<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;

class SystemConfig extends Model
{
    use HasUuid;
    protected $table = 'system_configs';
    protected $fillable = [
        'group','key','value','tipe','label',
        'deskripsi','is_public','is_encrypted','updated_by',
    ];
    protected function casts(): array
    {
        return [
            'is_public'    => 'boolean',
            'is_encrypted' => 'boolean',
        ];
    }

    // Helper statis untuk baca config
    public static function get(string $group, string $key, mixed $default = null): mixed
    {
        $config = static::where('group', $group)->where('key', $key)->first();
        if (!$config) return $default;

        return match($config->tipe) {
            'integer' => (int) $config->value,
            'boolean' => filter_var($config->value, FILTER_VALIDATE_BOOLEAN),
            'json'    => json_decode($config->value, true),
            default   => $config->value,
        };
    }

    public static function set(string $group, string $key, mixed $value): void
    {
        static::updateOrCreate(
            ['group' => $group, 'key' => $key],
            ['value' => is_array($value) ? json_encode($value) : (string) $value,
             'updated_by' => auth()->id()]
        );
    }
}
