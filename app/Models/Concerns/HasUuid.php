<?php

namespace App\Models\Concerns;

use Illuminate\Support\Str;

/**
 * Trait HasUuid
 *
 * Dipakai semua Model WIAKMS yang primary key-nya UUID.
 * Mengatur: non-incrementing, keyType string, auto-generate UUID saat create.
 *
 * Berbeda dengan HasUuids bawaan Laravel 11 yang pakai UUID v7 (ordered),
 * trait ini pakai UUID v4 via Str::uuid() — lebih kompatibel dengan
 * gen_random_uuid() di PostgreSQL.
 */
trait HasUuid
{
    public static function bootHasUuid(): void
    {
        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
        });
    }

    public function initializeHasUuid(): void
    {
        $this->incrementing = false;
        $this->keyType      = 'string';
    }
}
