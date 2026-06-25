<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Peminjaman extends Model
{
    use LogsActivity;

    protected $fillable = [
        'dokumen_id', 'user_id', 'approved_by', 'jenis',
        'tgl_pinjam', 'tgl_kembali', 'tgl_kembali_aktual',
        'status', 'keperluan', 'alasan_tolak', 'approved_at',
    ];

    protected function casts(): array
    {
        return [
            'tgl_pinjam'         => 'date',
            'tgl_kembali'        => 'date',
            'tgl_kembali_aktual' => 'date',
            'approved_at'        => 'datetime',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logFillable()->logOnlyDirty();
    }

    public function dokumen()    { return $this->belongsTo(Dokumen::class); }
    public function user()       { return $this->belongsTo(User::class); }
    public function approvedBy() { return $this->belongsTo(User::class, 'approved_by'); }

    public function isTerlambat(): bool
    {
        return $this->status === 'dipinjam'
            && $this->tgl_kembali
            && $this->tgl_kembali->isPast();
    }

    public function labelStatus(): string
    {
        return match($this->status) {
            'menunggu'     => 'Menunggu Persetujuan',
            'disetujui'    => 'Disetujui',
            'ditolak'      => 'Ditolak',
            'dipinjam'     => 'Sedang Dipinjam',
            'dikembalikan' => 'Dikembalikan',
            default        => '-',
        };
    }

    public function badgeClass(): string
    {
        return match($this->status) {
            'menunggu'     => 'bg-yellow-100 text-yellow-800',
            'disetujui'    => 'bg-blue-100 text-blue-800',
            'ditolak'      => 'bg-red-100 text-red-800',
            'dipinjam'     => 'bg-purple-100 text-purple-800',
            'dikembalikan' => 'bg-green-100 text-green-800',
            default        => 'bg-gray-100 text-gray-800',
        };
    }
}
