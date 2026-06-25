<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;

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
