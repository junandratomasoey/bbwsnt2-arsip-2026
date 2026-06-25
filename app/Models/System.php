<?php namespace App\Models;
use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;

// ============================================================
class Notification extends Model
{
    use HasUuid;
    public $timestamps = false;

    protected $fillable = [
        'user_id','type','title','message','icon','level',
        'entity_type','entity_id','action_url',
        'is_read','read_at',
        'sent_email','sent_at','created_at',
    ];
    protected function casts(): array
    {
        return [
            'is_read'   => 'boolean',
            'sent_email'=> 'boolean',
            'read_at'   => 'datetime',
            'sent_at'   => 'datetime',
            'created_at'=> 'datetime',
        ];
    }
    public function user() { return $this->belongsTo(User::class); }

    public function markAsRead(): void
    {
        if (!$this->is_read) {
            $this->update(['is_read' => true, 'read_at' => now()]);
        }
    }

    public function badgeLevel(): string
    {
        return match($this->level) {
            'success' => 'bg-green-100 text-green-800',
            'warning' => 'bg-yellow-100 text-yellow-800',
            'error'   => 'bg-red-100 text-red-800',
            default   => 'bg-blue-100 text-blue-800',
        };
    }

    // Factory method untuk kemudahan membuat notifikasi
    public static function kirim(
        string|array $userIds,
        string $type,
        string $title,
        string $message,
        array $options = []
    ): void {
        $userIds = is_array($userIds) ? $userIds : [$userIds];
        $rows = array_map(fn($uid) => [
            'id'          => (string) \Illuminate\Support\Str::uuid(),
            'user_id'     => $uid,
            'type'        => $type,
            'title'       => $title,
            'message'     => $message,
            'icon'        => $options['icon'] ?? 'ti-bell',
            'level'       => $options['level'] ?? 'info',
            'entity_type' => $options['entity_type'] ?? null,
            'entity_id'   => $options['entity_id'] ?? null,
            'action_url'  => $options['action_url'] ?? null,
            'created_at'  => now(),
        ], $userIds);

        static::insert($rows);
    }
}

// ============================================================
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

// ============================================================
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

// ============================================================
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

// ============================================================
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

// ============================================================
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
