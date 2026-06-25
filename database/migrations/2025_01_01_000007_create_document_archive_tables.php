<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * MIGRATION 007 — Domain 4: Document & Archive Management
 *
 * Tabel:
 *   document_types    — Master jenis dokumen
 *   physical_locations — Lokasi fisik arsip (gedung > lantai > ruang > lemari > rak > laci)
 *   documents         — Dokumen/arsip (polymorphic — bisa attach ke asset/project/op)
 *   document_files    — File aktual per dokumen (support multiple file per dokumen)
 *   loans             — Peminjaman dokumen (fisik & digital)
 *
 * Fitur:
 *   - Polymorphic entity (attach ke asset, project, op_record, knowledge)
 *   - Versi semantik (major.minor)
 *   - Full-text search via tsvector generated column
 *   - Klasifikasi kerahasiaan (biasa, terbatas, rahasia)
 *   - Retensi arsip sesuai regulasi ANRI
 *   - QR code untuk identifikasi fisik
 */
return new class extends Migration
{
    public function up(): void
    {
        // ============================================================
        // DOCUMENT TYPES — Master jenis dokumen
        // ============================================================
        Schema::create('document_types', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->string('kode', 20)->unique();
            $table->string('nama');
            $table->string('kategori'); // teknis, administrasi, hukum, keuangan, foto
            $table->text('deskripsi')->nullable();

            // Aturan retensi default (tahun)
            $table->unsignedSmallInteger('retensi_aktif_tahun')->default(5);
            $table->unsignedSmallInteger('retensi_inaktif_tahun')->default(10);
            $table->enum('nasib_akhir', ['musnah', 'permanen', 'sampling'])->default('musnah');

            // Apakah wajib ada sebelum fase tertentu
            $table->jsonb('wajib_pada_fase')->nullable();
            // {"before_construction": true, "after_construction": true, "op": false}

            $table->boolean('is_aktif')->default(true);
            $table->unsignedSmallInteger('urutan')->default(0);

            $table->timestamps();
        });

        // ============================================================
        // PHYSICAL LOCATIONS — Lokasi fisik arsip
        // ============================================================
        Schema::create('physical_locations', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // Hierarki lokasi
            $table->string('gedung');
            $table->string('lantai')->nullable();
            $table->string('ruang')->nullable();
            $table->string('lemari')->nullable();
            $table->string('rak')->nullable();
            $table->string('laci')->nullable();

            // Identifikasi unik
            $table->string('kode_lokasi', 30)->unique();
            // Format: BBWS-GD1-LT2-R3-L4-R5
            // Otomatis generate dari gedung+lantai+lemari+rak

            // QR code untuk scan fisik
            $table->string('qr_code_path')->nullable();

            // Kapasitas
            $table->unsignedSmallInteger('kapasitas_item')->nullable();
            $table->unsignedSmallInteger('terisi_item')->default(0);

            $table->text('keterangan')->nullable();
            $table->boolean('is_aktif')->default(true);

            $table->timestamps();
        });

        DB::statement("
            CREATE INDEX idx_ploc_kode ON physical_locations (kode_lokasi)
        ");

        // ============================================================
        // DOCUMENTS — Dokumen/arsip (entity utama)
        // ============================================================
        Schema::create('documents', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // ── Nomor & identitas ─────────────────────────────────
            $table->string('doc_number', 50)->nullable();
            // Format: BBW/TK/2025/001
            $table->string('judul');
            $table->text('deskripsi')->nullable();

            // ── Polymorphic entity ────────────────────────────────
            // Dokumen bisa melekat pada: asset, project, op_record, knowledge_article
            $table->string('entity_type')->nullable(); // "App\Models\Asset"
            $table->uuid('entity_id')->nullable();     // UUID entity
            $table->string('entity_fase')->nullable(); // before/during/after/op/umum

            // ── Klasifikasi ───────────────────────────────────────
            $table->uuid('document_type_id');
            $table->foreign('document_type_id')->references('id')->on('document_types');

            $table->uuid('unit_kerja_id')->nullable();
            $table->foreign('unit_kerja_id')->references('id')->on('unit_kerja')->nullOnDelete();

            // ── Kerahasiaan (sesuai Perka ANRI) ──────────────────
            $table->enum('klasifikasi', [
                'biasa',    // Tidak ada pembatasan akses
                'terbatas', // Terbatas pada unit kerja terkait
                'rahasia',  // Hanya pejabat berwenang
            ])->default('biasa');

            // ── Versi semantik ────────────────────────────────────
            $table->unsignedSmallInteger('versi_mayor')->default(1);
            $table->unsignedSmallInteger('versi_minor')->default(0);

            // Link ke versi sebelumnya
            $table->uuid('parent_doc_id')->nullable();
            // FK self-referencing parent_doc_id — ditambah via Schema::table setelah create

            // ── Status workflow ───────────────────────────────────
            $table->enum('status', [
                'draft',      // Sedang dibuat
                'review',     // Menunggu review
                'approved',   // Disetujui
                'superseded', // Digantikan versi baru
                'archived',   // Diarsipkan
            ])->default('draft');

            // ── Tanggal ───────────────────────────────────────────
            $table->date('tgl_dokumen')->nullable();
            $table->date('tgl_diterima')->nullable();
            $table->date('tgl_kedaluwarsa')->nullable();

            // ── Retensi arsip (ANRI) ──────────────────────────────
            $table->unsignedSmallInteger('retensi_aktif_tahun')->nullable();
            $table->unsignedSmallInteger('retensi_inaktif_tahun')->nullable();
            $table->enum('nasib_akhir', ['musnah', 'permanen', 'sampling'])->nullable();
            $table->date('tgl_musnah_rencana')->nullable();

            // ── Lokasi fisik ──────────────────────────────────────
            $table->uuid('physical_location_id')->nullable();
            $table->foreign('physical_location_id')
                  ->references('id')->on('physical_locations')->nullOnDelete();
            $table->boolean('ada_fisik')->default(false);
            $table->boolean('ada_digital')->default(false);

            // ── QR Code untuk dokumen fisik ───────────────────────
            $table->string('qr_code')->nullable()->unique(); // Kode unik untuk QR
            $table->string('qr_code_path')->nullable();      // Path file QR image

            // ── Tags & metadata ───────────────────────────────────
            // Kolom tags (text[]) ditambah via ALTER TABLE setelah Schema::create selesai
            $table->jsonb('metadata')->nullable();

            // ── Statistik ─────────────────────────────────────────
            $table->unsignedInteger('download_count')->default(0);
            $table->unsignedInteger('view_count')->default(0);

            // ── Audit ─────────────────────────────────────────────
            $table->uuid('uploaded_by');
            $table->foreign('uploaded_by')->references('id')->on('users');

            $table->uuid('approved_by')->nullable();
            $table->foreign('approved_by')->references('id')->on('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });

        // FK self-referencing parent_doc_id — SETELAH tabel documents selesai dibuat
        Schema::table('documents', function (Blueprint $table) {
            $table->foreign('parent_doc_id')
                  ->references('id')->on('documents')
                  ->nullOnDelete();
        });

        // Kolom tags (PostgreSQL native array) — harus setelah tabel selesai dibuat
        DB::statement("ALTER TABLE documents ADD COLUMN tags text[] DEFAULT '{}'");

        // Index untuk documents
        DB::statement('CREATE INDEX idx_doc_entity ON documents (entity_type, entity_id)');
        DB::statement('CREATE INDEX idx_doc_type ON documents (document_type_id)');
        DB::statement('CREATE INDEX idx_doc_status ON documents (status)');
        DB::statement('CREATE INDEX idx_doc_uk ON documents (unit_kerja_id)');
        DB::statement('CREATE INDEX idx_doc_klasifikasi ON documents (klasifikasi)');
        DB::statement('CREATE INDEX idx_doc_tgl ON documents (tgl_dokumen DESC)');
        DB::statement('CREATE INDEX idx_doc_tags ON documents USING GIN (tags)');
        DB::statement("
            CREATE INDEX idx_doc_judul_trgm ON documents
            USING GIN (judul gin_trgm_ops)
        ");

        // Full-text search vector — kolom biasa diupdate via trigger
        DB::statement('ALTER TABLE documents ADD COLUMN search_vector tsvector');
        DB::statement('CREATE INDEX idx_doc_fts ON documents USING GIN (search_vector)');

        DB::statement("
            CREATE OR REPLACE FUNCTION trg_documents_search_vector()
            RETURNS trigger AS \$\$
            BEGIN
                NEW.search_vector :=
                    setweight(to_tsvector('simple', coalesce(NEW.judul, '')), 'A') ||
                    setweight(to_tsvector('simple', coalesce(NEW.doc_number, '')), 'A') ||
                    setweight(to_tsvector('simple', coalesce(NEW.deskripsi, '')), 'B') ||
                    setweight(to_tsvector('simple', coalesce(array_to_string(NEW.tags, ' '), '')), 'C');
                RETURN NEW;
            END;
            \$\$ LANGUAGE plpgsql;
        ");
        DB::statement("
            CREATE TRIGGER trg_documents_search
            BEFORE INSERT OR UPDATE ON documents
            FOR EACH ROW EXECUTE FUNCTION trg_documents_search_vector();
        ");

        // ============================================================
        // DOCUMENT FILES — File aktual per dokumen
        // ============================================================
        Schema::create('document_files', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('document_id');
            $table->foreign('document_id')->references('id')->on('documents')->cascadeOnDelete();

            // ── File info ─────────────────────────────────────────
            $table->string('file_path');        // Path di storage
            $table->string('file_name');        // Nama file asli
            $table->string('file_disk')->default('local'); // local, s3, dll
            $table->string('mime_type');
            $table->unsignedBigInteger('file_size'); // Bytes
            $table->string('file_hash', 64)->nullable(); // SHA-256 untuk integrity check

            // ── Metadata ──────────────────────────────────────────
            $table->boolean('is_primary')->default(false); // File utama
            $table->string('label')->nullable(); // "Gambar halaman 1", "Lampiran A"
            $table->unsignedSmallInteger('page_count')->nullable(); // Untuk PDF

            // ── Upload info ───────────────────────────────────────
            $table->uuid('uploaded_by');
            $table->foreign('uploaded_by')->references('id')->on('users');

            $table->timestamps();
        });

        DB::statement('CREATE INDEX idx_dfile_doc ON document_files (document_id)');
        DB::statement('CREATE INDEX idx_dfile_primary ON document_files (document_id, is_primary)');

        // ============================================================
        // LOANS — Peminjaman dokumen
        // ============================================================
        Schema::create('loans', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // ── Relasi ────────────────────────────────────────────
            $table->uuid('document_id');
            $table->foreign('document_id')->references('id')->on('documents');

            $table->uuid('borrower_id');
            $table->foreign('borrower_id')->references('id')->on('users');

            // ── Jenis peminjaman ──────────────────────────────────
            $table->enum('jenis', ['fisik', 'digital']);

            // ── Status workflow ───────────────────────────────────
            // requested → approved → borrowed → returned
            //           → rejected
            $table->enum('status', [
                'requested',  // Baru diajukan
                'approved',   // Disetujui, belum diambil
                'borrowed',   // Sedang dipinjam
                'returned',   // Sudah dikembalikan
                'rejected',   // Ditolak
                'overdue',    // Terlambat dikembalikan
            ])->default('requested');

            // ── Jadwal ────────────────────────────────────────────
            $table->date('tgl_pinjam_rencana');
            $table->date('tgl_kembali_rencana');
            $table->timestamp('tgl_diambil')->nullable();
            $table->timestamp('tgl_dikembalikan')->nullable();

            // ── Keperluan & catatan ───────────────────────────────
            $table->text('keperluan');          // Wajib diisi
            $table->text('catatan_peminjam')->nullable();
            $table->text('catatan_petugas')->nullable();
            $table->text('alasan_ditolak')->nullable();

            // ── Approval ──────────────────────────────────────────
            $table->uuid('approved_by')->nullable();
            $table->foreign('approved_by')->references('id')->on('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();

            // ── Download tracking (untuk jenis digital) ───────────
            $table->unsignedSmallInteger('download_count')->default(0);
            $table->unsignedSmallInteger('max_download')->default(3); // Batas download

            $table->timestamps();
        });

        DB::statement('CREATE INDEX idx_loans_doc ON loans (document_id)');
        DB::statement('CREATE INDEX idx_loans_borrower ON loans (borrower_id)');
        DB::statement('CREATE INDEX idx_loans_status ON loans (status)');
        DB::statement('CREATE INDEX idx_loans_tgl ON loans (tgl_pinjam_rencana, tgl_kembali_rencana)');
    }

    public function down(): void
    {
        DB::statement('DROP TRIGGER IF EXISTS trg_documents_search ON documents');
        DB::statement('DROP FUNCTION IF EXISTS trg_documents_search_vector()');
        Schema::dropIfExists('loans');
        Schema::dropIfExists('document_files');
        Schema::dropIfExists('documents');
        Schema::dropIfExists('physical_locations');
        Schema::dropIfExists('document_types');
    }
};
