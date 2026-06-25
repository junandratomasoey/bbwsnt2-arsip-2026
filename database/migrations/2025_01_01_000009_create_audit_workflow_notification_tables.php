<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * MIGRATION 009 — Domain 6: Audit, Workflow & Notifications
 *
 * Tabel:
 *   audit_logs           — Immutable audit trail (append-only)
 *   workflow_definitions — Definisi alur kerja yang bisa dikonfigurasi
 *   workflow_instances   — Instance workflow per dokumen/entitas
 *   workflow_steps       — Langkah dalam setiap instance workflow
 *   notifications        — Notifikasi in-app per pengguna
 *   system_configs       — Konfigurasi sistem (key-value store)
 *
 * Prinsip audit_logs:
 *   - NO updated_at — hanya append
 *   - NO softdelete — tidak boleh dihapus
 *   - Row-level security via PostgreSQL RLS (opsional, aktifkan di production)
 *   - Bigserial PK — lebih efisien dari UUID untuk write-heavy table
 */
return new class extends Migration
{
    public function up(): void
    {
        // ============================================================
        // AUDIT LOGS — Immutable audit trail
        // ============================================================
        Schema::create('audit_logs', function (Blueprint $table) {
            // Bigserial lebih efisien untuk tabel yang hanya di-insert
            $table->bigIncrements('id');

            // ── Siapa ─────────────────────────────────────────────
            $table->uuid('user_id')->nullable();
            // Tidak ada FK constraint — user mungkin sudah dihapus,
            // audit log harus tetap ada
            $table->string('user_name')->nullable();   // Snapshot nama saat aksi
            $table->string('user_email')->nullable();  // Snapshot email saat aksi

            // ── Apa ───────────────────────────────────────────────
            $table->enum('action', [
                'login', 'logout', 'login_failed',
                'create', 'read', 'update', 'delete', 'restore',
                'download', 'upload', 'print',
                'approve', 'reject',
                'borrow', 'return',
                'export',
            ]);

            // ── Pada entitas apa ──────────────────────────────────
            $table->string('entity_type')->nullable();  // Model name
            $table->string('entity_id')->nullable();    // UUID as string
            $table->string('entity_label')->nullable(); // Human readable label

            // ── Perubahan data ────────────────────────────────────
            $table->jsonb('old_values')->nullable();  // Nilai sebelum
            $table->jsonb('new_values')->nullable();  // Nilai sesudah
            $table->jsonb('properties')->nullable();  // Metadata tambahan

            // ── Konteks teknis ────────────────────────────────────
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('url')->nullable();
            $table->string('method', 10)->nullable(); // GET, POST, PUT, dll
            $table->string('session_id')->nullable();

            // ── Unit kerja saat aksi ──────────────────────────────
            $table->uuid('unit_kerja_id')->nullable();

            // ── Timestamp — hanya created_at, tidak ada updated_at ─
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));

            // Tidak ada: updated_at, deleted_at, softDeletes
        });

        // Index — optimasi untuk query audit yang umum
        DB::statement('CREATE INDEX idx_al_user ON audit_logs (user_id)');
        DB::statement('CREATE INDEX idx_al_entity ON audit_logs (entity_type, entity_id)');
        DB::statement('CREATE INDEX idx_al_action ON audit_logs (action)');
        DB::statement('CREATE INDEX idx_al_created ON audit_logs (created_at DESC)');
        DB::statement('CREATE INDEX idx_al_uk ON audit_logs (unit_kerja_id)');

        // Partisi per tahun — aktifkan setelah data > 1 juta baris
        // DB::statement('ALTER TABLE audit_logs PARTITION BY RANGE (created_at)');

        // Cegah UPDATE dan DELETE pada audit_logs (immutable)
        DB::statement("
            CREATE RULE audit_logs_no_update AS ON UPDATE TO audit_logs
            DO INSTEAD NOTHING
        ");
        DB::statement("
            CREATE RULE audit_logs_no_delete AS ON DELETE TO audit_logs
            DO INSTEAD NOTHING
        ");

        // ============================================================
        // WORKFLOW DEFINITIONS — Template alur kerja
        // ============================================================
        Schema::create('workflow_definitions', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->string('kode', 30)->unique();
            $table->string('nama');
            $table->text('deskripsi')->nullable();

            // Entitas yang menggunakan workflow ini
            $table->string('entity_type'); // dokumen, peminjaman, kondisi_aset

            // Definisi langkah (JSONB — fleksibel, bisa dikonfigurasi dari UI)
            // [
            //   {"step": "draft",    "label": "Draft",     "approvers": [],         "next": "review"},
            //   {"step": "review",   "label": "Review",    "approvers": ["arsiparis"],"next": "approved"},
            //   {"step": "approved", "label": "Disetujui", "approvers": ["admin_satker"], "next": null}
            // ]
            $table->jsonb('steps_definition');

            // SLA per langkah (jam)
            $table->jsonb('sla_hours')->nullable();
            // {"review": 48, "approved": 24}

            $table->boolean('is_aktif')->default(true);

            $table->uuid('created_by')->nullable();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();

            $table->timestamps();
        });

        // ============================================================
        // WORKFLOW INSTANCES — Instance workflow per entitas
        // ============================================================
        Schema::create('workflow_instances', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // ── Definisi ──────────────────────────────────────────
            $table->uuid('definition_id')->nullable();
            $table->foreign('definition_id')
                  ->references('id')->on('workflow_definitions')->nullOnDelete();

            // ── Entitas ───────────────────────────────────────────
            $table->string('entity_type');
            $table->uuid('entity_id');
            $table->string('entity_label')->nullable();

            // ── Status ────────────────────────────────────────────
            $table->string('current_step');
            $table->enum('status', [
                'active',    // Sedang berjalan
                'completed', // Selesai (disetujui)
                'rejected',  // Ditolak
                'cancelled', // Dibatalkan
                'expired',   // Melewati SLA
            ])->default('active');

            // ── Konteks ───────────────────────────────────────────
            $table->jsonb('payload')->nullable();  // Data tambahan
            $table->text('catatan')->nullable();

            // ── Waktu ─────────────────────────────────────────────
            $table->timestamp('due_at')->nullable();      // Batas waktu
            $table->timestamp('completed_at')->nullable();

            $table->uuid('initiated_by')->nullable();
            $table->foreign('initiated_by')->references('id')->on('users')->nullOnDelete();

            $table->timestamps();
        });

        DB::statement('CREATE INDEX idx_wi_entity ON workflow_instances (entity_type, entity_id)');
        DB::statement('CREATE INDEX idx_wi_status ON workflow_instances (status)');
        DB::statement('CREATE INDEX idx_wi_step ON workflow_instances (current_step)');

        // ============================================================
        // WORKFLOW STEPS — Log langkah dalam setiap instance
        // ============================================================
        Schema::create('workflow_steps', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('instance_id');
            $table->foreign('instance_id')->references('id')->on('workflow_instances')->cascadeOnDelete();

            $table->string('step_name');           // Nama langkah
            $table->string('step_label')->nullable();

            $table->enum('action', [
                'submitted',  // Disubmit ke langkah ini
                'approved',   // Disetujui
                'rejected',   // Ditolak
                'returned',   // Dikembalikan ke langkah sebelumnya
                'delegated',  // Didelegasikan
                'expired',    // Expired karena SLA
            ]);

            $table->text('catatan')->nullable();

            $table->uuid('actor_id')->nullable();  // Yang melakukan aksi
            $table->foreign('actor_id')->references('id')->on('users')->nullOnDelete();
            $table->string('actor_name')->nullable(); // Snapshot nama

            $table->timestamp('acted_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamps();
        });

        DB::statement('CREATE INDEX idx_ws_instance ON workflow_steps (instance_id)');
        DB::statement('CREATE INDEX idx_ws_acted ON workflow_steps (acted_at DESC)');

        // ============================================================
        // NOTIFICATIONS — Notifikasi in-app
        // ============================================================
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // ── Penerima ──────────────────────────────────────────
            $table->uuid('user_id');
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();

            // ── Konten ────────────────────────────────────────────
            $table->string('type');           // NotifClass name
            $table->string('title');
            $table->text('message');
            $table->string('icon')->nullable(); // Tabler icon name
            $table->enum('level', ['info', 'success', 'warning', 'error'])->default('info');

            // ── Link ke entitas ───────────────────────────────────
            $table->string('entity_type')->nullable();
            $table->uuid('entity_id')->nullable();
            $table->string('action_url')->nullable(); // URL untuk action button

            // ── Status baca ───────────────────────────────────────
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();

            // ── Pengiriman ────────────────────────────────────────
            $table->boolean('sent_email')->default(false);
            $table->timestamp('sent_at')->nullable();

            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
        });

        DB::statement('CREATE INDEX idx_notif_user ON notifications (user_id, is_read)');
        DB::statement('CREATE INDEX idx_notif_user_created ON notifications (user_id, created_at DESC)');
        DB::statement('CREATE INDEX idx_notif_entity ON notifications (entity_type, entity_id)');

        // ============================================================
        // SYSTEM CONFIGS — Key-value store untuk konfigurasi sistem
        // ============================================================
        Schema::create('system_configs', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->string('group', 50);     // auth, storage, op, notification, dll
            $table->string('key', 100);
            $table->text('value')->nullable();
            $table->string('tipe', 20)->default('string'); // string, integer, boolean, json
            $table->string('label');
            $table->text('deskripsi')->nullable();
            $table->boolean('is_public')->default(false);  // Bisa diakses tanpa auth
            $table->boolean('is_encrypted')->default(false);

            $table->uuid('updated_by')->nullable();
            $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();

            $table->timestamps();

            $table->unique(['group', 'key']);
        });

        // Insert konfigurasi default
        DB::table('system_configs')->insert([
            // Auth
            ['id' => (string) Str::uuid(), 'group' => 'auth', 'key' => 'session_lifetime_hours', 'value' => '8', 'tipe' => 'integer', 'label' => 'Batas waktu sesi (jam)', 'is_public' => false, 'is_encrypted' => false, 'created_at' => now(), 'updated_at' => now()],
            ['id' => (string) Str::uuid(), 'group' => 'auth', 'key' => 'max_failed_login', 'value' => '5', 'tipe' => 'integer', 'label' => 'Maks login gagal sebelum dikunci', 'is_public' => false, 'is_encrypted' => false, 'created_at' => now(), 'updated_at' => now()],
            ['id' => (string) Str::uuid(), 'group' => 'auth', 'key' => 'lock_duration_minutes', 'value' => '30', 'tipe' => 'integer', 'label' => 'Durasi kunci akun (menit)', 'is_public' => false, 'is_encrypted' => false, 'created_at' => now(), 'updated_at' => now()],

            // Storage
            ['id' => (string) Str::uuid(), 'group' => 'storage', 'key' => 'max_upload_mb', 'value' => '50', 'tipe' => 'integer', 'label' => 'Maks ukuran upload (MB)', 'is_public' => true, 'is_encrypted' => false, 'created_at' => now(), 'updated_at' => now()],
            ['id' => (string) Str::uuid(), 'group' => 'storage', 'key' => 'allowed_extensions', 'value' => 'pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png,zip,rar', 'tipe' => 'string', 'label' => 'Ekstensi file yang diizinkan', 'is_public' => true, 'is_encrypted' => false, 'created_at' => now(), 'updated_at' => now()],

            // Loan
            ['id' => (string) Str::uuid(), 'group' => 'loan', 'key' => 'max_duration_days', 'value' => '14', 'tipe' => 'integer', 'label' => 'Maks durasi peminjaman (hari)', 'is_public' => true, 'is_encrypted' => false, 'created_at' => now(), 'updated_at' => now()],
            ['id' => (string) Str::uuid(), 'group' => 'loan', 'key' => 'reminder_days_before', 'value' => '3', 'tipe' => 'integer', 'label' => 'Notifikasi H-X sebelum batas kembali', 'is_public' => false, 'is_encrypted' => false, 'created_at' => now(), 'updated_at' => now()],

            // Notification
            ['id' => (string) Str::uuid(), 'group' => 'notification', 'key' => 'alert_doc_expiry_days', 'value' => '30', 'tipe' => 'integer', 'label' => 'Alert dokumen kedaluwarsa H-X', 'is_public' => false, 'is_encrypted' => false, 'created_at' => now(), 'updated_at' => now()],
            ['id' => (string) Str::uuid(), 'group' => 'notification', 'key' => 'alert_op_overdue_months', 'value' => '3', 'tipe' => 'integer', 'label' => 'Alert aset tidak di-OP lebih dari X bulan', 'is_public' => false, 'is_encrypted' => false, 'created_at' => now(), 'updated_at' => now()],

            // GIS
            ['id' => (string) Str::uuid(), 'group' => 'gis', 'key' => 'default_lat', 'value' => '-9.5', 'tipe' => 'string', 'label' => 'Latitude default peta (NTT)', 'is_public' => true, 'is_encrypted' => false, 'created_at' => now(), 'updated_at' => now()],
            ['id' => (string) Str::uuid(), 'group' => 'gis', 'key' => 'default_lng', 'value' => '124.0', 'tipe' => 'string', 'label' => 'Longitude default peta (NTT)', 'is_public' => true, 'is_encrypted' => false, 'created_at' => now(), 'updated_at' => now()],
            ['id' => (string) Str::uuid(), 'group' => 'gis', 'key' => 'default_zoom', 'value' => '9', 'tipe' => 'integer', 'label' => 'Zoom default peta', 'is_public' => true, 'is_encrypted' => false, 'created_at' => now(), 'updated_at' => now()],

            // Organisasi
            ['id' => (string) Str::uuid(), 'group' => 'org', 'key' => 'nama_balai', 'value' => 'Balai Besar Wilayah Sungai Nusa Tenggara II', 'tipe' => 'string', 'label' => 'Nama balai', 'is_public' => true, 'is_encrypted' => false, 'created_at' => now(), 'updated_at' => now()],
            ['id' => (string) Str::uuid(), 'group' => 'org', 'key' => 'singkatan_balai', 'value' => 'BBWS NT II', 'tipe' => 'string', 'label' => 'Singkatan nama balai', 'is_public' => true, 'is_encrypted' => false, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('system_configs');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('workflow_steps');
        Schema::dropIfExists('workflow_instances');
        Schema::dropIfExists('workflow_definitions');

        // Drop rules sebelum drop table
        DB::statement('DROP RULE IF EXISTS audit_logs_no_update ON audit_logs');
        DB::statement('DROP RULE IF EXISTS audit_logs_no_delete ON audit_logs');
        Schema::dropIfExists('audit_logs');
    }
};
