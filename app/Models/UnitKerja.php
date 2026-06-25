<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UnitKerja extends Model
{
    use HasUuid, Auditable, SoftDeletes;

    protected $table = 'unit_kerja';

    protected $fillable = [
        'parent_id', 'tipe', 'nama', 'singkatan', 'kode',
        'kepala_nama', 'kepala_nip', 'kepala_jabatan',
        'telp', 'email', 'alamat', 'tupoksi',
        'is_aktif', 'urutan',
    ];

    protected function casts(): array
    {
        return ['is_aktif' => 'boolean'];
    }

    protected array $auditInclude = ['nama', 'tipe', 'kepala_nama', 'is_aktif'];

    public function auditLabel(): string { return $this->namaLengkap(); }

    // ── Relasi hierarki ───────────────────────────────────────────────
    public function parent()
    {
        return $this->belongsTo(UnitKerja::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(UnitKerja::class, 'parent_id')
                    ->orderBy('urutan')->orderBy('nama');
    }

    public function allChildren()
    {
        return $this->children()->with('allChildren');
    }

    // ── Relasi ke domain lain ─────────────────────────────────────────
    public function users()    { return $this->hasMany(User::class); }
    public function assets()   { return $this->hasMany(Asset::class); }
    public function projects() { return $this->hasMany(Project::class); }

    // ── Scope ─────────────────────────────────────────────────────────
    public function scopeAktif($q)   { return $q->where('is_aktif', true); }
    public function scopeRoot($q)    { return $q->whereNull('parent_id'); }
    public function scopeBalai($q)   { return $q->where('tipe', 'balai'); }
    public function scopeBagian($q)  { return $q->where('tipe', 'bagian'); }
    public function scopeBidang($q)  { return $q->where('tipe', 'bidang'); }
    public function scopeSatker($q)  { return $q->where('tipe', 'satker'); }
    public function scopePpk($q)     { return $q->where('tipe', 'ppk'); }

    // ── Helper ────────────────────────────────────────────────────────
    public function labelTipe(): string
    {
        return match($this->tipe) {
            'balai'  => 'Balai',
            'bagian' => 'Bagian',
            'bidang' => 'Bidang',
            'satker' => 'Satker',
            'ppk'    => 'PPK',
            default  => ucfirst($this->tipe),
        };
    }

    public function namaLengkap(): string
    {
        return match($this->tipe) {
            'bagian' => 'Bagian ' . $this->nama,
            'bidang' => 'Bidang ' . $this->nama,
            'satker' => 'Satker ' . $this->nama,
            'ppk'    => 'PPK ' . $this->nama,
            default  => $this->nama,
        };
    }

    public function breadcrumb(string $separator = ' › '): string
    {
        $parts  = [$this->singkatan ?? $this->namaLengkap()];
        $parent = $this->parent;
        while ($parent) {
            array_unshift($parts, $parent->singkatan ?? $parent->namaLengkap());
            $parent = $parent->parent;
        }
        return implode($separator, $parts);
    }

    public function badgeClass(): string
    {
        return match($this->tipe) {
            'balai'  => 'bg-purple-100 text-purple-800',
            'bagian' => 'bg-blue-100 text-blue-800',
            'bidang' => 'bg-teal-100 text-teal-800',
            'satker' => 'bg-amber-100 text-amber-800',
            'ppk'    => 'bg-red-100 text-red-800',
            default  => 'bg-gray-100 text-gray-700',
        };
    }

    public static function parentYangBoleh(string $tipeAnak): array
    {
        return match($tipeAnak) {
            'bagian' => ['balai'],
            'bidang' => ['balai'],
            'satker' => ['balai', 'bagian', 'bidang'],
            'ppk'    => ['satker'],
            default  => [],
        };
    }

    public function semuaIdAnak(): array
    {
        $ids = [];
        foreach ($this->children as $child) {
            $ids[] = $child->id;
            array_push($ids, ...$child->semuaIdAnak());
        }
        return $ids;
    }
}
