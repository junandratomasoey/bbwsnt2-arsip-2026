<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Pekerjaan extends Model
{
    use SoftDeletes, LogsActivity;

    protected $fillable = [
        'nama', 'no_kontrak', 'unit_kerja_id', 'infrastruktur_id', 'jenis',
        'nilai_kontrak', 'kontraktor', 'konsultan', 'tgl_mulai', 'tgl_selesai',
        'status_fase', 'tahun_anggaran', 'keterangan', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'tgl_mulai'     => 'date',
            'tgl_selesai'   => 'date',
            'nilai_kontrak' => 'decimal:2',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logFillable()->logOnlyDirty();
    }

    public function unitKerja()   { return $this->belongsTo(UnitKerja::class); }
    public function infrastruktur() { return $this->belongsTo(Infrastruktur::class); }
    public function createdBy()   { return $this->belongsTo(User::class, 'created_by'); }
    public function dokumen()     { return $this->hasMany(Dokumen::class); }
    public function statusOps()   { return $this->hasMany(StatusOp::class); }

    // Dokumen yang belum ada (berdasarkan checklist jenis pekerjaan)
    public function dokumenBelumAda(): array
    {
        $wajib = $this->checklistDokumenWajib();
        $sudahAda = $this->dokumen->pluck('jenis_dokumen')->toArray();
        return array_diff($wajib, $sudahAda);
    }

    public function checklistDokumenWajib(): array
    {
        return match($this->jenis) {
            'pembangunan', 'rehabilitasi', 'peningkatan' => [
                'kontrak', 'gambar_rencana', 'spesifikasi_teknis', 'rab',
                'berita_acara', 'gambar_asbuilt', 'laporan_akhir',
            ],
            'operasi_pemeliharaan' => [
                'op_manual', 'laporan_bulanan', 'berita_acara',
            ],
            'studi' => ['laporan_akhir', 'laporan_bulanan'],
            default => ['laporan_akhir'],
        };
    }

    public function persentaseKelengkapanDokumen(): float
    {
        $wajib = count($this->checklistDokumenWajib());
        if ($wajib === 0) return 100;
        $sudahAda = $this->dokumen->pluck('jenis_dokumen')->unique()->count();
        return round(min($sudahAda / $wajib * 100, 100), 1);
    }

    public function statusOpTerakhir()
    {
        return $this->statusOps()->orderByDesc('tahun')->first();
    }

    public function labelFase(): string
    {
        return match($this->status_fase) {
            'perencanaan'  => 'Perencanaan',
            'pelaksanaan'  => 'Pelaksanaan',
            'serah_terima' => 'Serah Terima',
            'op'           => 'Operasi & Pemeliharaan',
            'selesai'      => 'Selesai',
            default        => '-',
        };
    }
}
