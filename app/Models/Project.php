<?php

namespace App\Models;

use App\Models\Concerns\{HasUuid, Auditable};
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use HasUuid, Auditable, SoftDeletes;

    protected $fillable = [
        'project_code', 'nama', 'deskripsi', 'jenis',
        'asset_id', 'unit_kerja_id', 'ppk_id',
        'lifecycle_phase',
        'no_kontrak', 'kontraktor', 'konsultan_pengawas', 'konsultan_perencana',
        'tahun_anggaran', 'sumber_dana', 'kode_dipa',
        'nilai_pagu', 'nilai_kontrak', 'nilai_addendum',
        'tgl_mulai_rencana', 'tgl_selesai_rencana',
        'tgl_mulai_aktual', 'tgl_selesai_aktual',
        'durasi_kontrak_hari',
        'realisasi_fisik_pct', 'realisasi_keuangan_pct', 'tgl_update_realisasi',
        'metadata', 'is_multiyears', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'tgl_mulai_rencana'       => 'date',
            'tgl_selesai_rencana'     => 'date',
            'tgl_mulai_aktual'        => 'date',
            'tgl_selesai_aktual'      => 'date',
            'tgl_update_realisasi'    => 'date',
            'nilai_pagu'              => 'decimal:2',
            'nilai_kontrak'           => 'decimal:2',
            'nilai_addendum'          => 'decimal:2',
            'realisasi_fisik_pct'     => 'decimal:2',
            'realisasi_keuangan_pct'  => 'decimal:2',
            'is_multiyears'           => 'boolean',
            'metadata'                => 'array',
        ];
    }

    protected array $auditInclude = [
        'lifecycle_phase', 'realisasi_fisik_pct', 'realisasi_keuangan_pct',
    ];

    public function auditLabel(): string
    {
        return "[{$this->project_code}] {$this->nama}";
    }

    // ── Relasi ───────────────────────────────────────────────────────
    public function asset()      { return $this->belongsTo(Asset::class); }
    public function unitKerja()  { return $this->belongsTo(UnitKerja::class); }
    public function ppk()        { return $this->belongsTo(UnitKerja::class, 'ppk_id'); }
    public function createdBy()  { return $this->belongsTo(User::class, 'created_by'); }

    public function milestones()
    {
        return $this->hasMany(ProjectMilestone::class)->orderBy('urutan');
    }

    public function progresses()
    {
        return $this->hasMany(ProjectProgress::class)->orderByDesc('tgl_laporan');
    }

    public function progressTerbaru()
    {
        return $this->hasOne(ProjectProgress::class)->latestOfMany('tgl_laporan');
    }

    public function documents()
    {
        return $this->morphMany(Document::class, 'entity');
    }

    // ── Scope ────────────────────────────────────────────────────────
    public function scopeAktif($q)
    {
        return $q->whereNotIn('lifecycle_phase', ['selesai', 'dibatalkan']);
    }

    public function scopeTahun($q, int $tahun)
    {
        return $q->where('tahun_anggaran', $tahun);
    }

    public function scopeTerlambat($q)
    {
        return $q->aktif()
                 ->whereNotNull('tgl_selesai_rencana')
                 ->where('tgl_selesai_rencana', '<', now());
    }

    // ── Helper ───────────────────────────────────────────────────────
    public function labelPhase(): string
    {
        return match($this->lifecycle_phase) {
            'perencanaan'    => 'Perencanaan',
            'pengadaan'      => 'Pengadaan',
            'pelaksanaan'    => 'Pelaksanaan',
            'serah_terima_1' => 'PHO',
            'pemeliharaan'   => 'Pemeliharaan',
            'serah_terima_2' => 'FHO',
            'selesai'        => 'Selesai',
            'dibatalkan'     => 'Dibatalkan',
            default          => $this->lifecycle_phase,
        };
    }

    public function healthStatus(): string
    {
        if (in_array($this->lifecycle_phase, ['selesai', 'dibatalkan'])) return 'closed';
        if ($this->tgl_selesai_rencana?->isPast()) return 'overdue';
        if ($this->realisasi_fisik_pct < 50
            && $this->tgl_selesai_rencana?->diffInDays(now()) <= 30) return 'at_risk';
        return 'on_track';
    }

    public function badgeHealth(): string
    {
        return match($this->healthStatus()) {
            'on_track' => 'bg-green-100 text-green-800',
            'at_risk'  => 'bg-yellow-100 text-yellow-800',
            'overdue'  => 'bg-red-100 text-red-800',
            'closed'   => 'bg-gray-100 text-gray-600',
            default    => 'bg-gray-100 text-gray-600',
        };
    }

    public function deviasiFisik(): float
    {
        // Bandingkan realisasi fisik vs rencana pada tanggal hari ini
        // Berdasarkan progress terakhir
        $progress = $this->progressTerbaru;
        if (!$progress?->rencana_fisik_pct) return 0;
        return round($this->realisasi_fisik_pct - $progress->rencana_fisik_pct, 2);
    }

    public function nilaiKontrakFormatted(): string
    {
        if (!$this->nilai_kontrak) return '-';
        return 'Rp ' . number_format($this->nilai_kontrak, 0, ',', '.');
    }

    public static function generateKode(int $tahun): string
    {
        $prefix = "BBW-PRJ-{$tahun}-";
        $last   = static::where('project_code', 'like', $prefix . '%')
                        ->orderByDesc('project_code')
                        ->value('project_code');
        $num    = $last ? (int) substr($last, -3) + 1 : 1;
        return $prefix . str_pad($num, 3, '0', STR_PAD_LEFT);
    }
}
