<?php

namespace App\Models;

use Illuminate\Support\Str;
use Spatie\Permission\Models\Role as SpatieRole;
use Illuminate\Database\Eloquent\Concerns\HasUuids; // Pastikan pakai trait ini jika di Laravel 9+

class Role extends SpatieRole
{
    use HasUuids; // Mengatur otomatis agar UUID di-generate dengan benar

    
    public    $incrementing = false;
    protected $keyType      = 'string';

    protected $casts = [
        'id' => 'string',
    ];

    // ── Override permissions relation agar UUID bukan integer ────────
    public function permissions(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(
            config('permission.models.permission'),
            config('permission.table_names.role_has_permissions'),
            config('permission.column_names.role_pivot_key'),        // role_id
            config('permission.column_names.permission_pivot_key')   // permission_id
        );
    }

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
        });
    }
}
