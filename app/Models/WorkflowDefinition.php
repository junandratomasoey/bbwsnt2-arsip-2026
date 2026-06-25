<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;

class WorkflowDefinition extends Model
{
    use HasUuid;
    protected $table = 'workflow_definitions';
    protected $fillable = [
        'kode','nama','deskripsi','entity_type',
        'steps_definition','sla_hours','is_aktif','created_by',
    ];
    protected function casts(): array
    {
        return [
            'steps_definition' => 'array',
            'sla_hours'        => 'array',
            'is_aktif'         => 'boolean',
        ];
    }
    public function instances() { return $this->hasMany(WorkflowInstance::class, 'definition_id'); }
}
