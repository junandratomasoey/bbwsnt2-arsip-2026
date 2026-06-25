<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;

class LibraryLoan extends Model
{
    use HasUuid;
    protected $table = 'library_loans';
    protected $fillable = [
        'library_item_id','borrower_id','status',
        'tgl_pinjam_rencana','tgl_kembali_rencana',
        'tgl_diambil','tgl_dikembalikan',
        'keperluan','catatan','approved_by','approved_at',
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
    public function libraryItem() { return $this->belongsTo(LibraryItem::class); }
    public function borrower()    { return $this->belongsTo(User::class, 'borrower_id'); }
    public function approvedBy()  { return $this->belongsTo(User::class, 'approved_by'); }
    public function isTerlambat(): bool
    {
        return $this->status === 'borrowed' && $this->tgl_kembali_rencana?->isPast();
    }
}
