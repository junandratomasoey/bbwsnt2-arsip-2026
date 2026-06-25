<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * MIGRATION 001 — Enable PostgreSQL extensions
 *
 * Harus dijalankan PERTAMA sebelum migration lainnya.
 * Requires PostgreSQL superuser atau rw permission ke extension.
 *
 * Install di server (satu kali):
 *   sudo apt install postgresql-16-postgis-3
 *   sudo -u postgres psql -d wiakms -c "CREATE EXTENSION postgis;"
 */
return new class extends Migration
{
    public function up(): void
    {
        // PostGIS — geometry types, spatial functions, GIST index
        DB::statement('CREATE EXTENSION IF NOT EXISTS postgis');

        // pg_trgm — trigram similarity untuk full-text search bahasa Indonesia
        DB::statement('CREATE EXTENSION IF NOT EXISTS pg_trgm');

        // uuid-ossp — UUID v4 generation di level DB (fallback)
        DB::statement('CREATE EXTENSION IF NOT EXISTS "uuid-ossp"');

        // unaccent — normalisasi teks untuk search (hapus aksen)
        DB::statement('CREATE EXTENSION IF NOT EXISTS unaccent');
    }

    public function down(): void
    {
        DB::statement('DROP EXTENSION IF EXISTS unaccent');
        DB::statement('DROP EXTENSION IF EXISTS "uuid-ossp"');
        DB::statement('DROP EXTENSION IF EXISTS pg_trgm');
        // Jangan drop postgis jika ada data geometry
    }
};
