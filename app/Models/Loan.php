<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;

class Loan extends Model
{
    use HasUuid;
    protected $table = 'loans';
    protected $fillable = [
        'document_id','borrower_id','jenis','status',
        'tgl_pinjam_rencana','tgl_kembali_rencana',
        'tgl_diambil','tgl_dikembalikan',
        'keperluan','catatan_peminjam','catatan_petugas','alasan_ditolak',
        'approved_by','approved_at',
        'download_count','max_download',
    ];
    protected function casts(): array
    {
        return [
            'tgl_pinjam_rencana'  => 'date',
            'tgl_kembali_rencana' => 'date',
            'tgl_diambil'         => 'datetime',
            'tgl_dikembalikan'    => 'datetime',
            'approved_at'         => 'datetime',
        ];
    }
    public function document()    { return $this->belongsTo(Document::class); }
    public function borrower()    { return $this->belongsTo(User::class, 'borrower_id'); }
    public function approvedBy()  { return $this->belongsTo(User::class, 'approved_by'); }

    public function scopeMenunggu($q)  { return $q->where('status', 'requested'); }
    public function scopeAktif($q)     { return $q->whereIn('status', ['requested','borrowed']); }

    public function isTerlambat(): bool
    {
        return $this->status === 'borrowed'
            && $this->tgl_kembali_rencana?->isPast();
    }

    public function labelStatus(): string
    {
        return match($this->status) {
            'requested' => 'Menunggu Persetujuan',
            'approved'  => 'Disetujui',
            'borrowed'  => 'Dipinjam',
            'returned'  => 'Dikembalikan',
            'rejected'  => 'Ditolak',
            'overdue'   => 'Terlambat',
            default     => $this->status,
        };
    }

    public function badgeStatus(): string
    {
        return match($this->status) {
            'requested' => 'bg-yellow-100 text-yellow-800',
            'approved'  => 'bg-blue-100 text-blue-800',
            'borrowed'  => 'bg-purple-100 text-purple-800',
            'returned'  => 'bg-green-100 text-green-800',
            'rejected'  => 'bg-red-100 text-red-800',
            'overdue'   => 'bg-orange-100 text-orange-800',
            default     => 'bg-gray-100 text-gray-600',
        };
    }
}
