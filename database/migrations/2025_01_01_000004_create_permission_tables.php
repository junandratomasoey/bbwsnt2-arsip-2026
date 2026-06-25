<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * MIGRATION 004 — Spatie Permission Tables (UUID, hardcoded, no config dependency)
 *
 * Ditulis eksplisit tanpa bergantung config('permission.*') agar tidak ada
 * race condition antara config dan migration saat migrate:fresh.
 *
 * Nama kolom morph key: model_uuid  (sesuai config/permission.php kita)
 * Primary key: UUID
 */
return new class extends Migration
{
    // Hardcode semua nama — tidak bergantung config saat migration jalan
    private string $permissions        = 'permissions';
    private string $roles              = 'roles';
    private string $modelHasPermissions = 'model_has_permissions';
    private string $modelHasRoles      = 'model_has_roles';
    private string $roleHasPermissions = 'role_has_permissions';

    // Nama kolom morph key — HARUS sama dengan config/permission.php
    private string $morphKey           = 'model_uuid';

    public function up(): void
    {
        // ── permissions ───────────────────────────────────────────────
        Schema::create($this->permissions, function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('guard_name');
            $table->timestamps();
            $table->unique(['name', 'guard_name']);
        });

        // ── roles ─────────────────────────────────────────────────────
        Schema::create($this->roles, function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('guard_name');
            $table->timestamps();
            $table->unique(['name', 'guard_name']);
        });

        // ── model_has_permissions ─────────────────────────────────────
        Schema::create($this->modelHasPermissions, function (Blueprint $table) {
            $table->uuid('permission_id');
            $table->string('model_type');
            $table->uuid('model_uuid');          // ← nama eksplisit, bukan model_id

            $table->foreign('permission_id')
                  ->references('id')
                  ->on($this->permissions)
                  ->onDelete('cascade');

            // Primary key pakai model_uuid, bukan model_id
            $table->primary(
                ['permission_id', 'model_uuid', 'model_type'],
                'mhp_primary'
            );
        });

        // ── model_has_roles ───────────────────────────────────────────
        Schema::create($this->modelHasRoles, function (Blueprint $table) {
            $table->uuid('role_id');
            $table->string('model_type');
            $table->uuid('model_uuid');          // ← nama eksplisit

            $table->foreign('role_id')
                  ->references('id')
                  ->on($this->roles)
                  ->onDelete('cascade');

            $table->primary(
                ['role_id', 'model_uuid', 'model_type'],
                'mhr_primary'
            );
        });

        // ── role_has_permissions ──────────────────────────────────────
        Schema::create($this->roleHasPermissions, function (Blueprint $table) {
            $table->uuid('permission_id');
            $table->uuid('role_id');

            $table->foreign('permission_id')
                  ->references('id')
                  ->on($this->permissions)
                  ->onDelete('cascade');

            $table->foreign('role_id')
                  ->references('id')
                  ->on($this->roles)
                  ->onDelete('cascade');

            $table->primary(['permission_id', 'role_id'], 'rhp_primary');
        });

        // Flush Spatie cache setelah tabel terbuat
        try {
            app('cache')
                ->store(config('permission.cache.store') !== 'default'
                    ? config('permission.cache.store') : null)
                ->forget(config('permission.cache.key', 'spatie.permission.cache'));
        } catch (\Throwable $e) {
            // Cache mungkin belum tersedia saat fresh migrate — abaikan
        }
    }

    public function down(): void
    {
        Schema::dropIfExists($this->roleHasPermissions);
        Schema::dropIfExists($this->modelHasRoles);
        Schema::dropIfExists($this->modelHasPermissions);
        Schema::dropIfExists($this->roles);
        Schema::dropIfExists($this->permissions);
    }
};
