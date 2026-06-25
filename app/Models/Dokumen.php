<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Dokumen extends Model
{
    use SoftDeletes, LogsActivity;

    protected $fillable = [
        'judul', 'no_dokumen', 'pekerjaan_id', 'user_id', 'lokasi_arsip_id',
        'jenis_dokumen', 'fase', 'file_path', 'file_original_name', 'file_mime',
        'file_size', 'versi', 'versi_sebelumnya', 'is_rahasia', 'is_fisik',
        'tgl_dokumen', 'tgl_kedaluwarsa', 'qr_code_path', 'keterangan',
    ];

    protected function casts(): array
    {
        return [
            'tgl_dokumen'     => 'date',
            'tgl_kedaluwarsa' => 'date',
            'is_rahasia'      => 'boolean',
            'is_fisik'        => 'boolean',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['judul', 'jenis_dokumen', 'fase', 'versi', 'is_rahasia'])
            ->logOnlyDirty();
    }

    public function pekerjaan()        { return $this->belongsTo(Pekerjaan::class); }
    public function user()             { return $this->belongsTo(User::class); }
    public function lokasiArsip()      { return $this->belongsTo(LokasiArsip::class); }
    public function versiSebelumnya()  { return $this->belongsTo(Dokumen::class, 'versi_sebelumnya'); }
    public function riwayatVersi()     { return $this->hasMany(Dokumen::class, 'versi_sebelumnya'); }
    public function peminjaman()       { return $this->hasMany(Peminjaman::class); }

    public function isKadaluwarsa(): bool
    {
        return $this->tgl_kedaluwarsa && $this->tgl_kedaluwarsa->isPast();
    }

    public function fileSizeLabel(): string
    {
        if (!$this->file_size) return '-';
        $kb = $this->file_size / 1024;
        if ($kb < 1024) return round($kb, 1) . ' KB';
        return round($kb / 1024, 1) . ' MB';
    }

    public function labelJenis(): string
    {
        $map = [
            'kontrak'           => 'Kontrak',
            'addendum'          => 'Addendum',
            'gambar_rencana'    => 'Gambar Rencana',
            'gambar_asbuilt'    => 'Gambar As-Built',
            'spesifikasi_teknis'=> 'Spesifikasi Teknis',
            'rab'               => 'RAB',
            'laporan_harian'    => 'Laporan Harian',
            'laporan_mingguan'  => 'Laporan Mingguan',
            'laporan_bulanan'   => 'Laporan Bulanan',
            'laporan_akhir'     => 'Laporan Akhir',
            'berita_acara'      => 'Berita Acara',
            'foto_dokumentasi'  => 'Foto Dokumentasi',
            'sertifikat'        => 'Sertifikat',
            'izin'              => 'Izin',
            'amdal'             => 'AMDAL',
            'op_manual'         => 'Manual OP',
            'lainnya'           => 'Lainnya',
        ];
        return $map[$this->jenis_dokumen] ?? $this->jenis_dokumen;
    }

    // Scope: hanya dokumen tidak rahasia untuk non-superadmin
    public function scopeTersedia($query, User $user)
    {
        if ($user->isSuperAdmin() || $user->can('dokumen.view_rahasia')) {
            return $query;
        }
        return $query->where('is_rahasia', false);
    }
}
