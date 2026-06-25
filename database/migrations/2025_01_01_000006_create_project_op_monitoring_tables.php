<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * MIGRATION 006 — Domain 3: Project & OP Monitoring
 *
 * Tabel:
 *   projects              — Pekerjaan/proyek (menggantikan tabel pekerjaan)
 *   project_milestones    — Milestone & tahapan proyek
 *   project_progresses    — Progress harian/mingguan (kurva S)
 *   op_records            — Rekaman OP per periode (menggantikan status_op)
 *   op_schedules          — Jadwal rencana OP per tahun
 */
return new class extends Migration
{
    public function up(): void
    {
        // ============================================================
        // PROJECTS — Pekerjaan/proyek infrastruktur
        // ============================================================
        Schema::create('projects', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // ── Identitas proyek ──────────────────────────────────
            $table->string('project_code', 30)->unique(); // BBW-PRJ-2025-001
            $table->string('nama');
            $table->text('deskripsi')->nullable();

            // ── Klasifikasi ───────────────────────────────────────
            $table->enum('jenis', [
                'pembangunan',
                'rehabilitasi',
                'peningkatan',
                'operasi_pemeliharaan',
                'studi_perencanaan',
                'pengawasan',
                'lainnya',
            ]);

            // ── Relasi ────────────────────────────────────────────
            $table->uuid('asset_id')->nullable(); // Aset yang dikerjakan
            $table->foreign('asset_id')->references('id')->on('assets')->nullOnDelete();

            $table->uuid('unit_kerja_id'); // Satker pelaksana
            $table->foreign('unit_kerja_id')->references('id')->on('unit_kerja');

            $table->uuid('ppk_id')->nullable(); // PPK pelaksana
            $table->foreign('ppk_id')->references('id')->on('unit_kerja')->nullOnDelete();

            // ── Lifecycle ─────────────────────────────────────────
            $table->enum('lifecycle_phase', [
                'perencanaan',
                'pengadaan',
                'pelaksanaan',
                'serah_terima_1',   // PHO — Provisional Hand Over
                'pemeliharaan',     // Masa pemeliharaan kontrak
                'serah_terima_2',   // FHO — Final Hand Over
                'selesai',
                'dibatalkan',
            ])->default('perencanaan');

            // ── Kontrak ───────────────────────────────────────────
            $table->string('no_kontrak')->nullable()->unique();
            $table->string('kontraktor')->nullable();
            $table->string('konsultan_pengawas')->nullable();
            $table->string('konsultan_perencana')->nullable();

            // ── Keuangan ──────────────────────────────────────────
            $table->unsignedSmallInteger('tahun_anggaran');
            $table->string('sumber_dana')->nullable(); // APBN, APBD, Hibah
            $table->string('kode_dipa')->nullable();
            $table->decimal('nilai_pagu', 18, 2)->nullable();
            $table->decimal('nilai_kontrak', 18, 2)->nullable();
            $table->decimal('nilai_addendum', 18, 2)->nullable()->default(0);

            // ── Jadwal ────────────────────────────────────────────
            $table->date('tgl_mulai_rencana')->nullable();
            $table->date('tgl_selesai_rencana')->nullable();
            $table->date('tgl_mulai_aktual')->nullable();
            $table->date('tgl_selesai_aktual')->nullable();
            $table->unsignedSmallInteger('durasi_kontrak_hari')->nullable();

            // ── Realisasi (diupdate berkala) ──────────────────────
            $table->decimal('realisasi_fisik_pct', 5, 2)->default(0);
            $table->decimal('realisasi_keuangan_pct', 5, 2)->default(0);
            $table->date('tgl_update_realisasi')->nullable();

            // ── Metadata ──────────────────────────────────────────
            $table->jsonb('metadata')->nullable();
            $table->boolean('is_multiyears')->default(false);

            // ── Audit ─────────────────────────────────────────────
            $table->uuid('created_by')->nullable();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();
        });

        DB::statement('CREATE INDEX idx_projects_asset ON projects (asset_id)');
        DB::statement('CREATE INDEX idx_projects_uk ON projects (unit_kerja_id)');
        DB::statement('CREATE INDEX idx_projects_phase ON projects (lifecycle_phase)');
        DB::statement('CREATE INDEX idx_projects_tahun ON projects (tahun_anggaran)');
        DB::statement("
            CREATE INDEX idx_projects_nama_trgm ON projects
            USING GIN (nama gin_trgm_ops)
        ");

        // ============================================================
        // PROJECT MILESTONES — Tahapan & milestone proyek
        // ============================================================
        Schema::create('project_milestones', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('project_id');
            $table->foreign('project_id')->references('id')->on('projects')->cascadeOnDelete();

            $table->string('nama');
            $table->text('deskripsi')->nullable();
            $table->date('tgl_rencana');
            $table->date('tgl_aktual')->nullable();
            $table->decimal('bobot_pct', 5, 2)->default(0); // Bobot untuk kurva S

            $table->enum('status', [
                'belum_mulai',
                'on_track',
                'terlambat',
                'selesai',
                'dibatalkan',
            ])->default('belum_mulai');

            $table->text('catatan')->nullable();
            $table->unsignedSmallInteger('urutan')->default(0);

            $table->timestamps();
        });

        DB::statement('CREATE INDEX idx_pm_project ON project_milestones (project_id)');
        DB::statement('CREATE INDEX idx_pm_tgl ON project_milestones (tgl_rencana)');

        // ============================================================
        // PROJECT PROGRESSES — Progress berkala (data kurva S)
        // ============================================================
        Schema::create('project_progresses', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('project_id');
            $table->foreign('project_id')->references('id')->on('projects')->cascadeOnDelete();

            $table->date('tgl_laporan'); // Tanggal cut-off laporan
            $table->enum('periode', ['harian', 'mingguan', 'bulanan'])->default('mingguan');

            // Realisasi fisik
            $table->decimal('realisasi_fisik_pct', 5, 2);
            $table->decimal('rencana_fisik_pct', 5, 2)->nullable(); // Dari baseline schedule
            $table->decimal('deviasi_fisik_pct', 5, 2)             // Positif = ahead, negatif = behind
                  ->storedAs('realisasi_fisik_pct - COALESCE(rencana_fisik_pct, 0)');

            // Realisasi keuangan
            $table->decimal('realisasi_keuangan_pct', 5, 2)->nullable();
            $table->decimal('nilai_termin', 18, 2)->nullable();

            // Kendala & isu
            $table->text('kendala')->nullable();
            $table->text('rencana_tindak_lanjut')->nullable();
            $table->jsonb('foto_paths')->nullable();

            $table->uuid('dilaporkan_oleh')->nullable();
            $table->foreign('dilaporkan_oleh')->references('id')->on('users')->nullOnDelete();

            $table->timestamps();

            // Unique: satu laporan per proyek per tanggal per periode
            $table->unique(['project_id', 'tgl_laporan', 'periode']);
        });

        DB::statement('CREATE INDEX idx_pp_project ON project_progresses (project_id)');
        DB::statement('CREATE INDEX idx_pp_tgl ON project_progresses (tgl_laporan DESC)');

        // ============================================================
        // OP SCHEDULES — Rencana OP tahunan per aset
        // ============================================================
        Schema::create('op_schedules', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('asset_id');
            $table->foreign('asset_id')->references('id')->on('assets')->cascadeOnDelete();

            $table->uuid('unit_kerja_id');
            $table->foreign('unit_kerja_id')->references('id')->on('unit_kerja');

            $table->unsignedSmallInteger('tahun');

            // Rencana anggaran OP
            $table->decimal('anggaran_op_rutin', 18, 2)->nullable();
            $table->decimal('anggaran_op_berkala', 18, 2)->nullable();
            $table->string('kode_dipa')->nullable();

            // Rencana kegiatan per bulan (JSONB)
            // {"1": ["Pembersihan saluran","Pelumasan pintu"],
            //  "2": ["Pengukuran debit"], ...}
            $table->jsonb('rencana_kegiatan')->nullable();

            $table->enum('status', ['draft', 'approved', 'berjalan', 'selesai'])->default('draft');

            $table->uuid('dibuat_oleh')->nullable();
            $table->foreign('dibuat_oleh')->references('id')->on('users')->nullOnDelete();

            $table->timestamps();

            $table->unique(['asset_id', 'tahun']);
        });

        DB::statement('CREATE INDEX idx_ops_asset ON op_schedules (asset_id)');
        DB::statement('CREATE INDEX idx_ops_tahun ON op_schedules (tahun)');

        // ============================================================
        // OP RECORDS — Rekaman pelaksanaan OP per periode
        // ============================================================
        Schema::create('op_records', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('asset_id');
            $table->foreign('asset_id')->references('id')->on('assets')->cascadeOnDelete();

            $table->uuid('unit_kerja_id');
            $table->foreign('unit_kerja_id')->references('id')->on('unit_kerja');

            $table->uuid('op_schedule_id')->nullable(); // Link ke rencana OP
            $table->foreign('op_schedule_id')->references('id')->on('op_schedules')->nullOnDelete();

            // ── Periode ───────────────────────────────────────────
            $table->unsignedSmallInteger('periode_tahun');
            $table->unsignedTinyInteger('periode_bulan'); // 1-12

            // ── Jenis & status ────────────────────────────────────
            $table->enum('jenis_op', ['rutin', 'berkala', 'darurat', 'rehabilitasi_minor']);
            $table->enum('status', ['belum', 'berjalan', 'selesai', 'tidak_terlaksana'])
                  ->default('belum');

            // ── Pelaksanaan ───────────────────────────────────────
            $table->date('tgl_pelaksanaan')->nullable();
            $table->decimal('realisasi_pct', 5, 2)->default(0);

            // Kegiatan yang dilakukan (JSONB array)
            // ["Pembersihan saluran 500m","Pelumasan 3 pintu air","Pengukuran debit"]
            $table->jsonb('kegiatan_dilakukan')->nullable();

            // ── Keuangan ──────────────────────────────────────────
            $table->decimal('anggaran', 18, 2)->nullable();
            $table->decimal('realisasi_anggaran', 18, 2)->nullable();

            // ── Data teknis ───────────────────────────────────────
            // Pengukuran debit, tinggi muka air, dll (JSONB — fleksibel)
            $table->jsonb('data_teknis')->nullable();

            // ── Kendala & dokumentasi ─────────────────────────────
            $table->text('kendala')->nullable();
            $table->text('catatan')->nullable();
            $table->jsonb('foto_paths')->nullable();

            // ── Petugas ───────────────────────────────────────────
            $table->uuid('petugas_id')->nullable();
            $table->foreign('petugas_id')->references('id')->on('users')->nullOnDelete();
            $table->string('tim_op')->nullable(); // Nama tim pelaksana

            $table->timestamps();

            // Unique: satu record per aset per bulan per jenis OP
            $table->unique(['asset_id', 'periode_tahun', 'periode_bulan', 'jenis_op'], 'op_records_unique');
        });

        DB::statement('CREATE INDEX idx_opr_asset ON op_records (asset_id)');
        DB::statement('CREATE INDEX idx_opr_periode ON op_records (periode_tahun, periode_bulan)');
        DB::statement('CREATE INDEX idx_opr_status ON op_records (status)');
        DB::statement('CREATE INDEX idx_opr_asset_tahun ON op_records (asset_id, periode_tahun DESC)');

        // ============================================================
        // Partitioning op_records per tahun (untuk performa jangka panjang)
        // Aktifkan jika data sudah >100.000 baris
        // ============================================================
        // DB::statement('
        //   CREATE TABLE op_records_2025 PARTITION OF op_records
        //   FOR VALUES FROM (2025) TO (2026)
        // ');
    }

    public function down(): void
    {
        Schema::dropIfExists('op_records');
        Schema::dropIfExists('op_schedules');
        Schema::dropIfExists('project_progresses');
        Schema::dropIfExists('project_milestones');
        Schema::dropIfExists('projects');
    }
};
