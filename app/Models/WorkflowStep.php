<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;

class WorkflowStep extends Model
{
    use HasUuid;
    protected $table = 'workflow_steps';
    protected $fillable = [
        'instance_id','step_name','step_label','action',
        'catatan','actor_id','actor_name','acted_at',
    ];
    protected function casts(): array
    {
        return ['acted_at' => 'datetime'];
    }
    public function instance() { return $this->belongsTo(WorkflowInstance::class); }
    public function actor()    { return $this->belongsTo(User::class, 'actor_id'); }
}
