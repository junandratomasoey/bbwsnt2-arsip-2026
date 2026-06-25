<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;

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
