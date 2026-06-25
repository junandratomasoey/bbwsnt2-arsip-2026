<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->string('name');
            $table->string('nip', 18)->nullable()->unique();
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');

            $table->string('jabatan_struktural')->nullable();
            $table->string('jabatan_fungsional')->nullable();
            $table->string('golongan', 10)->nullable();
            $table->string('no_hp', 20)->nullable();
            $table->string('foto_path')->nullable();

            // FK ke unit_kerja ditambah setelah tabel unit_kerja ada
            $table->uuid('unit_kerja_id')->nullable();

            $table->enum('status', ['pending', 'aktif', 'nonaktif', 'ditolak'])
                  ->default('pending');
            $table->text('alasan_tolak')->nullable();

            // FK self-referencing approved_by — ditambah setelah tabel selesai
            $table->uuid('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();

            $table->rememberToken();
            $table->timestamp('last_login_at')->nullable();
            $table->string('last_login_ip', 45)->nullable();
            $table->unsignedSmallInteger('failed_login_count')->default(0);
            $table->timestamp('locked_until')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });

        // FK unit_kerja (tabel sudah ada dari migration sebelumnya)
        Schema::table('users', function (Blueprint $table) {
            $table->foreign('unit_kerja_id')
                  ->references('id')->on('unit_kerja')
                  ->nullOnDelete();
        });

        // FK self-referencing approved_by — SETELAH tabel users selesai
        Schema::table('users', function (Blueprint $table) {
            $table->foreign('approved_by')
                  ->references('id')->on('users')
                  ->nullOnDelete();
        });

        DB::statement('CREATE INDEX idx_users_status   ON users (status)');
        DB::statement('CREATE INDEX idx_users_unit_kerja ON users (unit_kerja_id)');
        DB::statement("
            CREATE INDEX idx_users_nama_trgm ON users
            USING GIN (name gin_trgm_ops)
        ");

        // Tabel pendukung Breeze
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->uuid('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};
