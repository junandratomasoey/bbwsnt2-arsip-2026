<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class StatusOp extends Model
{
    use LogsActivity;

    protected $table = 'status_op';

    protected $fillable = [
        'pekerjaan_id', 'user_id', 'tahun', 'persentase',
        'status', 'tgl_op', 'catatan', 'foto_paths',
    ];

    protected function casts(): array
    {
        return [
            'tgl_op'     => 'date',
            'foto_paths' => 'array',
            'persentase' => 'decimal:2',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logFillable()->logOnlyDirty();
    }

    public function pekerjaan() { return $this->belongsTo(Pekerjaan::class); }
    public function user()      { return $this->belongsTo(User::class); }

    public function labelStatus(): string
    {
        return match($this->status) {
            'belum'    => 'Belum OP',
            'sebagian' => 'Sebagian',
            'penuh'    => 'OP Penuh (100%)',
            default    => '-',
        };
    }

    public function badgeClass(): string
    {
        return match($this->status) {
            'penuh'    => 'bg-green-100 text-green-800',
            'sebagian' => 'bg-yellow-100 text-yellow-800',
            default    => 'bg-red-100 text-red-800',
        };
    }
}
