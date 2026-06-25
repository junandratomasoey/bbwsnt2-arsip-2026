<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;

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
