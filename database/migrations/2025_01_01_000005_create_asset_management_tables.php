<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * MIGRATION 005 — Domain 2: Asset Management
 *
 * Tabel:
 *   asset_types      — Jenis/kategori aset (bendung, embung, dll)
 *   assets           — Aset infrastruktur (menggantikan tabel infrastruktur)
 *   asset_geometries — Geometri spasial per aset (PostGIS)
 *   asset_conditions — Riwayat kondisi & inspeksi aset (time-series)
 *   asset_components — Komponen teknis per aset
 *
 * Standar: ISO 55000 Asset Management (lifecycle, condition, valuation)
 */
return new class extends Migration
{
    public function up(): void
    {
        // ============================================================
        // ASSET TYPES — Master jenis aset infrastruktur air
        // ============================================================
        Schema::create('asset_types', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->string('kode', 20)->unique();
            $table->string('nama');
            $table->string('kategori'); // bendung, embung, waduk, saluran, dll

            // Checklist dokumen wajib per jenis aset (JSONB)
            // Format: {"before": ["kontrak","gambar_rencana"], "after": ["as_built","berita_acara"]}
            $table->jsonb('checklist_dokumen_wajib')->nullable();

            // Atribut teknis spesifik per jenis (JSONB — fleksibel untuk 20 tahun ke depan)
            // Bendung: {"lebar_mercu", "tinggi_bendung", "luas_daerah_irigasi"}
            // Embung: {"kapasitas_tampung", "luas_genangan"}
            $table->jsonb('atribut_teknis_template')->nullable();

            // Standar OP yang berlaku
            $table->string('standar_op')->nullable(); // SNI, Permen PUPR, dsb
            $table->unsignedSmallInteger('urutan')->default(0);
            $table->boolean('is_aktif')->default(true);

            $table->timestamps();
        });

        // ============================================================
        // ASSETS — Aset infrastruktur (core entity)
        // ============================================================
        Schema::create('assets', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // ── Identitas aset ────────────────────────────────────
            $table->string('asset_code', 30)->unique(); // Kode unik: BBW-BDG-001
            $table->string('nama');
            $table->text('deskripsi')->nullable();

            // ── Klasifikasi ───────────────────────────────────────
            $table->uuid('asset_type_id');
            $table->foreign('asset_type_id')->references('id')->on('asset_types');

            $table->uuid('unit_kerja_id'); // Satker/PPK pengelola
            $table->foreign('unit_kerja_id')->references('id')->on('unit_kerja');

            // ── Lokasi administratif ──────────────────────────────
            $table->string('provinsi')->nullable()->default('Nusa Tenggara Timur');
            $table->string('kabupaten')->nullable();
            $table->string('kecamatan')->nullable();
            $table->string('desa')->nullable();
            $table->string('das')->nullable();       // Daerah Aliran Sungai
            $table->string('wilayah_sungai')->nullable(); // WS Nusa Tenggara II

            // ── Lifecycle status (ISO 55000) ──────────────────────
            $table->enum('lifecycle_status', [
                'planning',         // Dalam perencanaan
                'construction',     // Sedang dibangun
                'commissioning',    // Uji coba operasi
                'operating',        // Operasional normal
                'rehabilitating',   // Sedang direhabilitasi
                'decommissioned',   // Dinonaktifkan/dihapus
            ])->default('operating');

            // ── Data teknis ───────────────────────────────────────
            $table->unsignedSmallInteger('tahun_bangun')->nullable();
            $table->unsignedSmallInteger('tahun_desain')->nullable();
            $table->unsignedSmallInteger('umur_rencana_tahun')->nullable();
            // Atribut teknis spesifik per jenis (flexible JSONB)
            $table->jsonb('atribut_teknis')->nullable();

            // ── Kondisi & penilaian ───────────────────────────────
            $table->enum('kondisi_terakhir', [
                'A', // Baik (>80%)
                'B', // Sedang (60-80%)
                'C', // Rusak ringan (40-60%)
                'D', // Rusak berat (<40%)
            ])->nullable();
            $table->decimal('rci_score_terakhir', 5, 2)->nullable(); // 0-100
            $table->date('tgl_inspeksi_terakhir')->nullable();

            // ── Valuasi aset (untuk pelaporan BMN) ───────────────
            $table->decimal('nilai_perolehan', 18, 2)->nullable();
            $table->decimal('nilai_buku', 18, 2)->nullable();
            $table->unsignedSmallInteger('tahun_perolehan')->nullable();

            // ── Metadata & foto ───────────────────────────────────
            $table->string('foto_utama_path')->nullable();
            $table->jsonb('metadata')->nullable(); // Data tambahan fleksibel

            // ── Pencarian ─────────────────────────────────────────
            // Generated column untuk full-text search
            // (diisi via trigger PostgreSQL di migration selanjutnya)
            $table->boolean('is_aktif')->default(true);

            // ── Audit ─────────────────────────────────────────────
            $table->uuid('created_by')->nullable();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->uuid('updated_by')->nullable();
            $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();
        });

        // Index assets
        DB::statement('CREATE INDEX idx_assets_type ON assets (asset_type_id)');
        DB::statement('CREATE INDEX idx_assets_uk ON assets (unit_kerja_id)');
        DB::statement('CREATE INDEX idx_assets_lifecycle ON assets (lifecycle_status)');
        DB::statement('CREATE INDEX idx_assets_kondisi ON assets (kondisi_terakhir)');
        DB::statement('CREATE INDEX idx_assets_code ON assets (asset_code)');
        DB::statement("
            CREATE INDEX idx_assets_nama_trgm ON assets
            USING GIN (nama gin_trgm_ops)
        ");

        // Full-text search vector — kolom biasa diupdate via trigger
        DB::statement('ALTER TABLE assets ADD COLUMN search_vector tsvector');
        DB::statement('CREATE INDEX idx_assets_fts ON assets USING GIN (search_vector)');

        // Trigger function untuk update search_vector saat insert/update
        DB::statement("
            CREATE OR REPLACE FUNCTION trg_assets_search_vector()
            RETURNS trigger AS \$\$
            BEGIN
                NEW.search_vector :=
                    setweight(to_tsvector('simple', coalesce(NEW.nama, '')), 'A') ||
                    setweight(to_tsvector('simple', coalesce(NEW.asset_code, '')), 'A') ||
                    setweight(to_tsvector('simple', coalesce(NEW.kabupaten, '')), 'B') ||
                    setweight(to_tsvector('simple', coalesce(NEW.kecamatan, '')), 'C') ||
                    setweight(to_tsvector('simple', coalesce(NEW.deskripsi, '')), 'D');
                RETURN NEW;
            END;
            \$\$ LANGUAGE plpgsql;
        ");
        DB::statement("
            CREATE TRIGGER trg_assets_search
            BEFORE INSERT OR UPDATE ON assets
            FOR EACH ROW EXECUTE FUNCTION trg_assets_search_vector();
        ");

        // ============================================================
        // ASSET GEOMETRIES — Spasial per aset (PostGIS)
        // ============================================================
        Schema::create('asset_geometries', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('asset_id');
            $table->foreign('asset_id')->references('id')->on('assets')->cascadeOnDelete();

            // Tipe geometry
            $table->enum('geom_type', [
                'point',    // Titik lokasi (semua aset)
                'line',     // Garis jaringan (saluran, sungai)
                'polygon',  // Area (waduk, embung, daerah irigasi)
            ]);

            $table->string('label')->nullable(); // "Titik bendung utama", "Area genangan"
            $table->text('keterangan')->nullable();
            $table->boolean('is_primary')->default(false); // Geometry utama untuk peta

            // Metadata tambahan (elevasi, panjang, luas — dari PostGIS)
            $table->jsonb('properties')->nullable();

            $table->timestamps();
        });

        // Tambah kolom geometry PostGIS (tidak bisa lewat Blueprint)
        // SRID 4326 = WGS84 (koordinat GPS standar)
        DB::statement('
            ALTER TABLE asset_geometries
            ADD COLUMN geom geometry(Geometry, 4326)
        ');

        // GIST index untuk spatial query (wajib untuk performa GIS)
        DB::statement('
            CREATE INDEX idx_asset_geom_spatial ON asset_geometries
            USING GIST (geom)
        ');
        DB::statement('CREATE INDEX idx_asset_geom_asset ON asset_geometries (asset_id)');
        DB::statement('CREATE INDEX idx_asset_geom_type ON asset_geometries (geom_type)');
        DB::statement('CREATE INDEX idx_asset_geom_primary ON asset_geometries (asset_id, is_primary)');

        // ============================================================
        // ASSET CONDITIONS — Riwayat kondisi & inspeksi (time-series)
        // ============================================================
        Schema::create('asset_conditions', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('asset_id');
            $table->foreign('asset_id')->references('id')->on('assets')->cascadeOnDelete();

            // ── Data inspeksi ─────────────────────────────────────
            $table->date('tgl_inspeksi');
            $table->enum('jenis_inspeksi', [
                'rutin',     // Inspeksi rutin bulanan/triwulan
                'tahunan',   // Inspeksi tahunan komprehensif
                'khusus',    // Setelah bencana, banjir, gempa
                'amdal',     // Untuk keperluan AMDAL
            ])->default('rutin');

            // ── Penilaian kondisi (RCI — Rapid Condition Index) ───
            $table->enum('kondisi', ['A', 'B', 'C', 'D']);
            $table->decimal('rci_score', 5, 2)->nullable(); // 0.00 - 100.00

            // Kondisi per komponen (JSONB — fleksibel per jenis aset)
            // {"tubuh_bendung": {"kondisi":"B", "skor":70, "catatan":"Retak minor"},
            //  "pintu_air":     {"kondisi":"A", "skor":90, "catatan":"Baik"},
            //  "saluran_primer":{"kondisi":"C", "skor":55, "catatan":"Sedimentasi tinggi"}}
            $table->jsonb('kondisi_komponen')->nullable();

            // ── Rekomendasi ───────────────────────────────────────
            $table->text('temuan')->nullable();
            $table->text('rekomendasi')->nullable();
            $table->enum('urgensi_tindak_lanjut', [
                'segera',   // < 1 bulan
                'mendesak', // 1-3 bulan
                'rutin',    // 3-12 bulan
                'jangka_panjang', // > 1 tahun
            ])->nullable();
            $table->decimal('estimasi_biaya', 18, 2)->nullable();

            // ── Inspektur ─────────────────────────────────────────
            $table->uuid('inspektur_id')->nullable();
            $table->foreign('inspektur_id')->references('id')->on('users')->nullOnDelete();
            $table->string('tim_inspeksi')->nullable(); // Nama-nama tim

            // ── Foto & dokumen lapangan ───────────────────────────
            $table->jsonb('foto_paths')->nullable();    // Array path foto
            $table->string('dok_lapangan_path')->nullable();

            $table->timestamps();
        });

        DB::statement('CREATE INDEX idx_ac_asset ON asset_conditions (asset_id)');
        DB::statement('CREATE INDEX idx_ac_tanggal ON asset_conditions (tgl_inspeksi DESC)');
        DB::statement('CREATE INDEX idx_ac_kondisi ON asset_conditions (kondisi)');
        DB::statement('CREATE INDEX idx_ac_asset_date ON asset_conditions (asset_id, tgl_inspeksi DESC)');

        // ============================================================
        // ASSET COMPONENTS — Komponen teknis per aset
        // ============================================================
        Schema::create('asset_components', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('asset_id');
            $table->foreign('asset_id')->references('id')->on('assets')->cascadeOnDelete();

            $table->string('nama_komponen');
            $table->string('kode_komponen', 20)->nullable();
            $table->string('material')->nullable();
            $table->unsignedSmallInteger('tahun_pasang')->nullable();
            $table->unsignedSmallInteger('umur_rencana')->nullable();
            $table->text('spesifikasi')->nullable();
            $table->enum('kondisi', ['A', 'B', 'C', 'D'])->nullable();
            $table->boolean('is_kritis')->default(false); // Komponen kritis

            $table->timestamps();
        });

        DB::statement('CREATE INDEX idx_acomp_asset ON asset_components (asset_id)');
    }

    public function down(): void
    {
        DB::statement('DROP TRIGGER IF EXISTS trg_assets_search ON assets');
        DB::statement('DROP FUNCTION IF EXISTS trg_assets_search_vector()');
        Schema::dropIfExists('asset_components');
        Schema::dropIfExists('asset_conditions');
        Schema::dropIfExists('asset_geometries');
        Schema::dropIfExists('assets');
        Schema::dropIfExists('asset_types');
    }
};
