<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * MIGRATION 008 — Domain 5: Knowledge Base & Digital Library
 *
 * Tabel:
 *   knowledge_categories  — Kategori artikel knowledge
 *   knowledge_articles    — Wiki, SOP, lesson learned, FAQ, best practice
 *   knowledge_relations   — Relasi antar artikel (linked knowledge graph)
 *   library_items         — Buku, jurnal, standar, peraturan
 *   library_loans         — Peminjaman item perpustakaan
 *
 * Fitur:
 *   - Full-text search (tsvector generated column + pg_trgm)
 *   - PostgreSQL native array untuk tags
 *   - JSONB untuk metadata fleksibel
 *   - Knowledge graph sederhana via tabel relasi
 */
return new class extends Migration
{
    public function up(): void
    {
        // ============================================================
        // KNOWLEDGE CATEGORIES — Kategori artikel
        // ============================================================
        Schema::create('knowledge_categories', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('parent_id')->nullable();
            // FK self-referencing parent_id — ditambah via Schema::table setelah create

            $table->string('nama');
            $table->string('slug')->unique();
            $table->string('ikon')->nullable();       // Tabler icon name
            $table->string('warna', 20)->nullable();  // Tailwind color class
            $table->text('deskripsi')->nullable();
            $table->unsignedSmallInteger('urutan')->default(0);
            $table->boolean('is_aktif')->default(true);

            $table->timestamps();
        });

        // FK self-referencing knowledge_categories — SETELAH tabel selesai
        Schema::table('knowledge_categories', function (Blueprint $table) {
            $table->foreign('parent_id')
                  ->references('id')->on('knowledge_categories')
                  ->nullOnDelete();
        });

        // ============================================================
        // KNOWLEDGE ARTICLES — Konten knowledge base
        // ============================================================
        Schema::create('knowledge_articles', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // ── Identitas ─────────────────────────────────────────
            $table->string('judul');
            $table->string('slug')->unique();

            // ── Klasifikasi ───────────────────────────────────────
            $table->enum('tipe', [
                'sop',           // Standar Operasional Prosedur
                'wiki',          // Artikel pengetahuan umum
                'lesson_learned',// Pelajaran dari pengalaman/proyek
                'faq',           // Pertanyaan yang sering ditanya
                'best_practice', // Praktik terbaik
                'regulasi',      // Ringkasan regulasi/peraturan
                'panduan',       // Panduan teknis
            ]);

            $table->uuid('category_id')->nullable();
            $table->foreign('category_id')->references('id')->on('knowledge_categories')->nullOnDelete();

            // ── Konten ────────────────────────────────────────────
            $table->longText('konten');     // Markdown
            $table->text('ringkasan')->nullable(); // Summary 1-2 paragraf

            // ── Relasi ke entitas lain ────────────────────────────
            $table->string('entity_type')->nullable(); // Terkait asset/project
            $table->uuid('entity_id')->nullable();

            // ── Status ────────────────────────────────────────────
            $table->enum('status', ['draft', 'review', 'published', 'archived'])->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();

            // ── Versi ─────────────────────────────────────────────
            $table->unsignedSmallInteger('versi')->default(1);
            $table->uuid('parent_article_id')->nullable();
            // FK self-referencing parent_article_id — ditambah via Schema::table setelah create

            // ── Metadata ──────────────────────────────────────────
            // PostgreSQL native array
            $table->unsignedInteger('views_count')->default(0);
            $table->unsignedInteger('helpful_count')->default(0);
            $table->boolean('is_featured')->default(false);
            $table->jsonb('metadata')->nullable();

            // ── Penulis ───────────────────────────────────────────
            $table->uuid('author_id');
            $table->foreign('author_id')->references('id')->on('users');
            $table->uuid('reviewer_id')->nullable();
            $table->foreign('reviewer_id')->references('id')->on('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();
        });

        // Tambah kolom array (tidak bisa via Blueprint)
        DB::statement("ALTER TABLE knowledge_articles ADD COLUMN tags text[] DEFAULT '{}'");

        // FK self-referencing parent_article_id — SETELAH tabel selesai
        Schema::table('knowledge_articles', function (Blueprint $table) {
            $table->foreign('parent_article_id')
                  ->references('id')->on('knowledge_articles')
                  ->nullOnDelete();
        });

        // Index
        DB::statement('CREATE INDEX idx_ka_category ON knowledge_articles (category_id)');
        DB::statement('CREATE INDEX idx_ka_tipe ON knowledge_articles (tipe)');
        DB::statement('CREATE INDEX idx_ka_status ON knowledge_articles (status)');
        DB::statement('CREATE INDEX idx_ka_entity ON knowledge_articles (entity_type, entity_id)');
        DB::statement('CREATE INDEX idx_ka_tags ON knowledge_articles USING GIN (tags)');
        DB::statement("
            CREATE INDEX idx_ka_judul_trgm ON knowledge_articles
            USING GIN (judul gin_trgm_ops)
        ");

        // Full-text search
        DB::statement('ALTER TABLE knowledge_articles ADD COLUMN search_vector tsvector');
        DB::statement('CREATE INDEX idx_ka_fts ON knowledge_articles USING GIN (search_vector)');

        DB::statement("
            CREATE OR REPLACE FUNCTION trg_knowledge_articles_search_vector()
            RETURNS trigger AS \$\$
            BEGIN
                NEW.search_vector :=
                    setweight(to_tsvector('simple', coalesce(NEW.judul, '')), 'A') ||
                    setweight(to_tsvector('simple', coalesce(NEW.ringkasan, '')), 'B') ||
                    setweight(to_tsvector('simple', coalesce(array_to_string(NEW.tags, ' '), '')), 'B') ||
                    setweight(to_tsvector('simple', coalesce(substring(NEW.konten, 1, 10000), '')), 'C');
                RETURN NEW;
            END;
            \$\$ LANGUAGE plpgsql;
        ");
        DB::statement("
            CREATE TRIGGER trg_knowledge_articles_search
            BEFORE INSERT OR UPDATE ON knowledge_articles
            FOR EACH ROW EXECUTE FUNCTION trg_knowledge_articles_search_vector();
        ");

        // ============================================================
        // KNOWLEDGE RELATIONS — Graph relasi antar artikel
        // ============================================================
        Schema::create('knowledge_relations', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('article_id');
            $table->foreign('article_id')->references('id')->on('knowledge_articles')->cascadeOnDelete();

            $table->uuid('related_article_id');
            $table->foreign('related_article_id')->references('id')->on('knowledge_articles')->cascadeOnDelete();

            $table->enum('tipe_relasi', [
                'lihat_juga',    // See also
                'prasyarat',     // Prerequisite
                'lanjutan',      // Follow-up
                'referensi',     // Reference
            ])->default('lihat_juga');

            $table->timestamps();

            $table->unique(['article_id', 'related_article_id', 'tipe_relasi'], 'kr_unique');
        });

        // ============================================================
        // LIBRARY ITEMS — Koleksi perpustakaan digital/fisik
        // ============================================================
        Schema::create('library_items', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // ── Identitas ─────────────────────────────────────────
            $table->string('kode_item', 20)->unique(); // LIB-BK-001
            $table->string('judul');
            $table->string('judul_singkat')->nullable();

            // ── Klasifikasi ───────────────────────────────────────
            $table->enum('tipe', [
                'buku',
                'jurnal',
                'standar',        // SNI, ISO, dll
                'peraturan',      // Permen, Perpres, UU
                'laporan',
                'prosiding',
                'manual_teknis',
            ]);

            // ── Bibliografi ───────────────────────────────────────
            $table->string('penulis')->nullable();
            $table->string('penerbit')->nullable();
            $table->unsignedSmallInteger('tahun_terbit')->nullable();
            $table->string('edisi')->nullable();
            $table->string('isbn', 20)->nullable();
            $table->string('no_seri')->nullable();  // Untuk jurnal/standar
            $table->string('bahasa', 20)->default('Indonesia');

            // ── Topik & klasifikasi DDC ───────────────────────────
            $table->string('ddc')->nullable(); // Dewey Decimal Classification
            $table->string('subjek')->nullable();

            // ── Ketersediaan ──────────────────────────────────────
            $table->unsignedSmallInteger('stok_fisik')->default(0);
            $table->unsignedSmallInteger('stok_dipinjam')->default(0);
            $table->boolean('ada_digital')->default(false);
            $table->string('file_digital_path')->nullable();

            // ── Lokasi fisik ──────────────────────────────────────
            $table->uuid('physical_location_id')->nullable();
            $table->foreign('physical_location_id')
                  ->references('id')->on('physical_locations')->nullOnDelete();

            $table->jsonb('metadata')->nullable();
            $table->boolean('is_aktif')->default(true);

            $table->timestamps();
            $table->softDeletes();
        });

        // Tambah kolom array untuk tags
        DB::statement("ALTER TABLE library_items ADD COLUMN tags text[] DEFAULT '{}'");

        DB::statement('CREATE INDEX idx_li_tipe ON library_items (tipe)');
        DB::statement('CREATE INDEX idx_li_tags ON library_items USING GIN (tags)');
        DB::statement("
            CREATE INDEX idx_li_judul_trgm ON library_items
            USING GIN (judul gin_trgm_ops)
        ");

        // Full-text search untuk perpustakaan
        DB::statement('ALTER TABLE library_items ADD COLUMN search_vector tsvector');
        DB::statement('CREATE INDEX idx_li_fts ON library_items USING GIN (search_vector)');

        DB::statement("
            CREATE OR REPLACE FUNCTION trg_library_items_search_vector()
            RETURNS trigger AS \$\$
            BEGIN
                NEW.search_vector :=
                    setweight(to_tsvector('simple', coalesce(NEW.judul, '')), 'A') ||
                    setweight(to_tsvector('simple', coalesce(NEW.penulis, '')), 'B') ||
                    setweight(to_tsvector('simple', coalesce(NEW.subjek, '')), 'B') ||
                    setweight(to_tsvector('simple', coalesce(array_to_string(NEW.tags, ' '), '')), 'C');
                RETURN NEW;
            END;
            \$\$ LANGUAGE plpgsql;
        ");
        DB::statement("
            CREATE TRIGGER trg_library_items_search
            BEFORE INSERT OR UPDATE ON library_items
            FOR EACH ROW EXECUTE FUNCTION trg_library_items_search_vector();
        ");

        // ============================================================
        // LIBRARY LOANS — Peminjaman item perpustakaan
        // ============================================================
        Schema::create('library_loans', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('library_item_id');
            $table->foreign('library_item_id')->references('id')->on('library_items');

            $table->uuid('borrower_id');
            $table->foreign('borrower_id')->references('id')->on('users');

            $table->enum('status', [
                'requested', 'approved', 'borrowed', 'returned', 'rejected', 'overdue',
            ])->default('requested');

            $table->date('tgl_pinjam_rencana');
            $table->date('tgl_kembali_rencana');
            $table->timestamp('tgl_diambil')->nullable();
            $table->timestamp('tgl_dikembalikan')->nullable();

            $table->text('keperluan');
            $table->text('catatan')->nullable();

            $table->uuid('approved_by')->nullable();
            $table->foreign('approved_by')->references('id')->on('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();

            $table->timestamps();
        });

        DB::statement('CREATE INDEX idx_ll_item ON library_loans (library_item_id)');
        DB::statement('CREATE INDEX idx_ll_borrower ON library_loans (borrower_id)');
        DB::statement('CREATE INDEX idx_ll_status ON library_loans (status)');
    }

    public function down(): void
    {
        DB::statement('DROP TRIGGER IF EXISTS trg_library_items_search ON library_items');
        DB::statement('DROP FUNCTION IF EXISTS trg_library_items_search_vector()');
        DB::statement('DROP TRIGGER IF EXISTS trg_knowledge_articles_search ON knowledge_articles');
        DB::statement('DROP FUNCTION IF EXISTS trg_knowledge_articles_search_vector()');
        Schema::dropIfExists('library_loans');
        Schema::dropIfExists('library_items');
        Schema::dropIfExists('knowledge_relations');
        Schema::dropIfExists('knowledge_articles');
        Schema::dropIfExists('knowledge_categories');
    }
};
