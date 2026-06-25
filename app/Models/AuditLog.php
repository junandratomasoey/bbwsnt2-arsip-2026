<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;

class AuditLog extends Model
{
    // Tidak pakai UUID — bigIncrements
    // Tidak pakai softDelete, updated_at
    public $incrementing = true;
    public $timestamps   = false;
    protected $primaryKey = 'id';

    protected $fillable = [
        'user_id','user_name','user_email','action',
        'entity_type','entity_id','entity_label',
        'old_values','new_values','properties',
        'ip_address','user_agent','url','method','session_id',
        'unit_kerja_id','created_at',
    ];

    protected function casts(): array
    {
        return [
            'old_values'  => 'array',
            'new_values'  => 'array',
            'properties'  => 'array',
            'created_at'  => 'datetime',
        ];
    }

    public function scopeUser($q, string $userId)   { return $q->where('user_id', $userId); }
    public function scopeAction($q, string $action) { return $q->where('action', $action); }
    public function scopeEntity($q, string $type, string $id)
    {
        return $q->where('entity_type', $type)->where('entity_id', $id);
    }
}
