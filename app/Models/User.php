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

    protected $casts = [
        'id'                => 'string',
        'email_verified_at' => 'datetime',
        'approved_at'       => 'datetime',
        'last_login_at'     => 'datetime',
        'locked_until'      => 'datetime',
        'password'          => 'hashed',
    ];

    // ── Override relasi roles — gunakan UUID bukan integer ───────────
    public function roles(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->morphToMany(
            config('permission.models.role'),
            'model',
            config('permission.table_names.model_has_roles'),
            config('permission.column_names.model_morph_key'),
            config('permission.column_names.role_pivot_key')
        )->withPivot(config('permission.column_names.model_morph_key'));
    }

    // ── Override permissions langsung — bypass Spatie getPermissionsViaRoles ──
    public function getPermissionsViaRoles(): \Illuminate\Support\Collection
    {
        // Ambil role IDs sebagai string UUID dari database langsung
        $roleIds = $this->roles()->pluck(
            config('permission.table_names.roles') . '.id'
        )->map(fn($id) => (string) $id)->toArray();

        if (empty($roleIds)) {
            return collect();
        }

        // Query permissions via role_has_permissions dengan UUID string
        return \App\Models\Permission::query()
            ->join(
                config('permission.table_names.role_has_permissions'),
                config('permission.table_names.permissions') . '.id',
                '=',
                config('permission.table_names.role_has_permissions') . '.' .
                config('permission.column_names.permission_pivot_key')
            )
            ->whereIn(
                config('permission.table_names.role_has_permissions') . '.' .
                config('permission.column_names.role_pivot_key'),
                $roleIds  // ← UUID string, bukan integer
            )
            ->select(config('permission.table_names.permissions') . '.*')
            ->get();
    }

    // ── Override getAllPermissions ────────────────────────────────────
    public function getAllPermissions(): \Illuminate\Support\Collection
    {
        $permissions = $this->permissions;

        if ($this->roles && $this->roles->count()) {
            $permissions = $permissions->merge($this->getPermissionsViaRoles());
        }

        return $permissions->sort()->values();
    }

    // ── Relasi lain ───────────────────────────────────────────────────
    public function unitKerja()     { return $this->belongsTo(UnitKerja::class); }
    public function approvedBy()    { return $this->belongsTo(User::class, 'approved_by'); }
    public function notifications() { return $this->hasMany(Notification::class)->orderByDesc('created_at'); }
    public function unreadNotifications() { return $this->notifications()->where('is_read', false); }

    public function scopePending($q)  { return $q->where('status', 'pending'); }
    public function scopeAktif($q)    { return $q->where('status', 'aktif'); }
    public function scopeNonaktif($q) { return $q->where('status', 'nonaktif'); }

    public function isSuperAdmin(): bool { return $this->hasRole('superadmin'); }
    public function isAktif(): bool      { return $this->status === 'aktif'; }
    public function isLocked(): bool     { return $this->locked_until && $this->locked_until->isFuture(); }
    public function isPending(): bool    { return $this->status === 'pending'; }

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
            'pimpinan'        => 'Pimpinan',
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
}
