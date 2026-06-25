<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('unit_kerja', function (Blueprint $table) {
            // Cara yang benar untuk UUID PK di PostgreSQL dengan Laravel
            $table->uuid('id')->primary();

            // parent_id tanpa FK dulu — FK ditambah setelah tabel selesai
            $table->uuid('parent_id')->nullable();

            $table->enum('tipe', ['balai', 'bagian', 'bidang', 'satker', 'ppk']);
            $table->string('nama');
            $table->string('singkatan', 50)->nullable();
            $table->string('kode', 30)->unique();

            $table->string('kepala_nama')->nullable();
            $table->string('kepala_nip', 18)->nullable();
            $table->string('kepala_jabatan')->nullable();

            $table->string('telp', 20)->nullable();
            $table->string('email')->nullable();
            $table->text('alamat')->nullable();
            $table->text('tupoksi')->nullable();
            $table->boolean('is_aktif')->default(true);
            $table->unsignedSmallInteger('urutan')->default(0);

            $table->timestamps();
            $table->softDeletes();
        });

        // FK self-referencing ditambah SETELAH tabel selesai dibuat
        // Ini wajib untuk self-referencing FK di PostgreSQL
        Schema::table('unit_kerja', function (Blueprint $table) {
            $table->foreign('parent_id')
                  ->references('id')
                  ->on('unit_kerja')
                  ->nullOnDelete();
        });

        // Index
        DB::statement('CREATE INDEX idx_uk_parent ON unit_kerja (parent_id)');
        DB::statement('CREATE INDEX idx_uk_tipe   ON unit_kerja (tipe)');
        DB::statement('CREATE INDEX idx_uk_kode   ON unit_kerja (kode)');
        DB::statement("
            CREATE INDEX idx_uk_nama_trgm ON unit_kerja
            USING GIN (nama gin_trgm_ops)
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('unit_kerja');
    }
};
