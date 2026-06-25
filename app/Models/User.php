<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, HasUuid, HasRoles, Notifiable, SoftDeletes;

    protected $fillable = [
        'name', 'nip', 'email', 'password',
        'jabatan_struktural', 'jabatan_fungsional', 'golongan',
        'no_hp', 'foto_path', 'unit_kerja_id',
        'status', 'alasan_tolak',
        'approved_by', 'approved_at',
        'last_login_at', 'last_login_ip',
        'failed_login_count', 'locked_until',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at'   => 'datetime',
            'approved_at'         => 'datetime',
            'last_login_at'       => 'datetime',
            'locked_until'        => 'datetime',
            'password'            => 'hashed',
        ];
    }

    // ── Relasi ───────────────────────────────────────────────────────
    public function unitKerja()
    {
        return $this->belongsTo(UnitKerja::class);
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class)
                    ->orderByDesc('created_at');
    }

    public function unreadNotifications()
    {
        return $this->notifications()->where('is_read', false);
    }

    // ── Scope ────────────────────────────────────────────────────────
    public function scopePending($q)   { return $q->where('status', 'pending'); }
    public function scopeAktif($q)     { return $q->where('status', 'aktif'); }
    public function scopeNonaktif($q)  { return $q->where('status', 'nonaktif'); }

    // ── Helper ───────────────────────────────────────────────────────
    public function isSuperAdmin(): bool
    {
        return $this->hasRole('superadmin');
    }

    public function isAktif(): bool
    {
        return $this->status === 'aktif';
    }

    public function isLocked(): bool
    {
        return $this->locked_until && $this->locked_until->isFuture();
    }

    public function namaRole(): string
    {
        $role = $this->roles->first()?->name ?? '-';
        return match($role) {
            'superadmin'      => 'Super Admin',
            'admin_satker'    => 'Admin Satker',
            'arsiparis'       => 'Arsiparis',
            'operator_teknis' => 'Operator Teknis',
            'peminjam'        => 'Peminjam',
            'viewer'          => 'Viewer',
            default           => ucfirst(str_replace('_', ' ', $role)),
        };
    }

    public function inisial(): string
    {
        $parts = explode(' ', trim($this->name));
        return strtoupper(
            count($parts) >= 2
                ? $parts[0][0] . $parts[1][0]
                : ($parts[0][0] ?? 'U')
        );
    }

    public function recordLogin(string $ip): void
    {
        $this->update([
            'last_login_at'      => now(),
            'last_login_ip'      => $ip,
            'failed_login_count' => 0,
            'locked_until'       => null,
        ]);
    }

    public function recordFailedLogin(): void
    {
        $count = $this->failed_login_count + 1;
        $maxFail = (int) \App\Models\SystemConfig::get('auth', 'max_failed_login', 5);
        $lockMin = (int) \App\Models\SystemConfig::get('auth', 'lock_duration_minutes', 30);

        $this->update([
            'failed_login_count' => $count,
            'locked_until'       => $count >= $maxFail
                                    ? now()->addMinutes($lockMin)
                                    : null,
        ]);
    }
}
