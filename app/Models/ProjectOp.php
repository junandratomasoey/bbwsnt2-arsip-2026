<?php namespace App\Models;
use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;

// ============================================================
class ProjectMilestone extends Model
{
    use HasUuid;
    protected $fillable = [
        'project_id','nama','deskripsi','tgl_rencana','tgl_aktual',
        'bobot_pct','status','catatan','urutan',
    ];
    protected function casts(): array
    {
        return [
            'tgl_rencana' => 'date',
            'tgl_aktual'  => 'date',
            'bobot_pct'   => 'decimal:2',
        ];
    }
    public function project() { return $this->belongsTo(Project::class); }

    public function isSelesai(): bool { return $this->status === 'selesai'; }
    public function isTerlambat(): bool
    {
        return $this->status !== 'selesai' && $this->tgl_rencana?->isPast();
    }
    public function badgeStatus(): string
    {
        return match($this->status) {
            'selesai'     => 'bg-green-100 text-green-800',
            'on_track'    => 'bg-blue-100 text-blue-800',
            'terlambat'   => 'bg-red-100 text-red-800',
            'belum_mulai' => 'bg-gray-100 text-gray-600',
            default       => 'bg-gray-100 text-gray-600',
        };
    }
}

// ============================================================
class ProjectProgress extends Model
{
    use HasUuid;
    protected $fillable = [
        'project_id','tgl_laporan','periode',
        'realisasi_fisik_pct','rencana_fisik_pct',
        'realisasi_keuangan_pct','nilai_termin',
        'kendala','rencana_tindak_lanjut','foto_paths',
        'dilaporkan_oleh',
    ];
    protected function casts(): array
    {
        return [
            'tgl_laporan'             => 'date',
            'realisasi_fisik_pct'     => 'decimal:2',
            'rencana_fisik_pct'       => 'decimal:2',
            'realisasi_keuangan_pct'  => 'decimal:2',
            'nilai_termin'            => 'decimal:2',
            'foto_paths'              => 'array',
        ];
    }
    public function project()       { return $this->belongsTo(Project::class); }
    public function dilaporkanOleh(){ return $this->belongsTo(User::class, 'dilaporkan_oleh'); }

    // Deviasi: positif = ahead, negatif = behind schedule
    public function deviasi(): float
    {
        return round($this->realisasi_fisik_pct - ($this->rencana_fisik_pct ?? 0), 2);
    }

    public function statusDeviasi(): string
    {
        $d = $this->deviasi();
        if ($d > 0)  return 'ahead';
        if ($d < -5) return 'critical';
        if ($d < 0)  return 'behind';
        return 'on_track';
    }
}

// ============================================================
class OpSchedule extends Model
{
    use HasUuid;
    protected $table = 'op_schedules';
    protected $fillable = [
        'asset_id','unit_kerja_id','tahun',
        'anggaran_op_rutin','anggaran_op_berkala','kode_dipa',
        'rencana_kegiatan','status','dibuat_oleh',
    ];
    protected function casts(): array
    {
        return [
            'anggaran_op_rutin'    => 'decimal:2',
            'anggaran_op_berkala'  => 'decimal:2',
            'rencana_kegiatan'     => 'array',
        ];
    }
    public function asset()      { return $this->belongsTo(Asset::class); }
    public function unitKerja()  { return $this->belongsTo(UnitKerja::class); }
    public function dibuatOleh() { return $this->belongsTo(User::class, 'dibuat_oleh'); }
    public function opRecords()  { return $this->hasMany(OpRecord::class); }
}

// ============================================================
class OpRecord extends Model
{
    use HasUuid;
    protected $table = 'op_records';
    protected $fillable = [
        'asset_id','unit_kerja_id','op_schedule_id',
        'periode_tahun','periode_bulan','jenis_op','status',
        'tgl_pelaksanaan','realisasi_pct',
        'kegiatan_dilakukan','anggaran','realisasi_anggaran',
        'data_teknis','kendala','catatan','foto_paths',
        'petugas_id','tim_op',
    ];
    protected function casts(): array
    {
        return [
            'tgl_pelaksanaan'    => 'date',
            'realisasi_pct'      => 'decimal:2',
            'anggaran'           => 'decimal:2',
            'realisasi_anggaran' => 'decimal:2',
            'kegiatan_dilakukan' => 'array',
            'data_teknis'        => 'array',
            'foto_paths'         => 'array',
        ];
    }
    public function asset()      { return $this->belongsTo(Asset::class); }
    public function unitKerja()  { return $this->belongsTo(UnitKerja::class); }
    public function schedule()   { return $this->belongsTo(OpSchedule::class, 'op_schedule_id'); }
    public function petugas()    { return $this->belongsTo(User::class, 'petugas_id'); }

    public function scopeTahun($q, int $y)  { return $q->where('periode_tahun', $y); }
    public function scopeBulan($q, int $m)  { return $q->where('periode_bulan', $m); }
    public function scopeSelesai($q)        { return $q->where('status', 'selesai'); }

    public function labelBulan(): string
    {
        return \Carbon\Carbon::create(null, $this->periode_bulan)->translatedFormat('F');
    }

    public function labelStatus(): string
    {
        return match($this->status) {
            'belum'             => 'Belum',
            'berjalan'          => 'Berjalan',
            'selesai'           => 'Selesai',
            'tidak_terlaksana'  => 'Tidak Terlaksana',
            default             => $this->status,
        };
    }

    public function badgeStatus(): string
    {
        return match($this->status) {
            'selesai'           => 'bg-green-100 text-green-800',
            'berjalan'          => 'bg-blue-100 text-blue-800',
            'belum'             => 'bg-gray-100 text-gray-600',
            'tidak_terlaksana'  => 'bg-red-100 text-red-800',
            default             => 'bg-gray-100 text-gray-600',
        };
    }
}
