<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;

class WorkflowInstance extends Model
{
    use HasUuid;
    protected $table = 'workflow_instances';
    protected $fillable = [
        'definition_id','entity_type','entity_id','entity_label',
        'current_step','status','payload','catatan',
        'due_at','completed_at','initiated_by',
    ];
    protected function casts(): array
    {
        return [
            'payload'      => 'array',
            'due_at'       => 'datetime',
            'completed_at' => 'datetime',
        ];
    }
    public function definition()   { return $this->belongsTo(WorkflowDefinition::class); }
    public function initiatedBy()  { return $this->belongsTo(User::class, 'initiated_by'); }
    public function steps()        { return $this->hasMany(WorkflowStep::class, 'instance_id')->orderBy('acted_at'); }
    public function latestStep()   { return $this->hasOne(WorkflowStep::class, 'instance_id')->latestOfMany('acted_at'); }

    public function isActive(): bool    { return $this->status === 'active'; }
    public function isOverdue(): bool   { return $this->due_at?->isPast() && $this->isActive(); }
}
