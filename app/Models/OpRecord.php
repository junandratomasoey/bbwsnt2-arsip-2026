<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;

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
