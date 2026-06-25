<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;

class AssetCondition extends Model
{
    use HasUuid;

    protected $table = 'asset_conditions';

    protected $fillable = [
        'asset_id', 'tgl_inspeksi', 'jenis_inspeksi',
        'kondisi', 'rci_score', 'kondisi_komponen',
        'temuan', 'rekomendasi', 'urgensi_tindak_lanjut',
        'estimasi_biaya', 'inspektur_id', 'tim_inspeksi',
        'foto_paths', 'dok_lapangan_path',
    ];

    protected function casts(): array
    {
        return [
            'tgl_inspeksi'      => 'date',
            'kondisi_komponen'  => 'array',
            'foto_paths'        => 'array',
            'rci_score'         => 'decimal:2',
            'estimasi_biaya'    => 'decimal:2',
        ];
    }

    public function asset()      { return $this->belongsTo(Asset::class); }
    public function inspektur()  { return $this->belongsTo(User::class, 'inspektur_id'); }

    public function labelKondisi(): string
    {
        return match($this->kondisi) {
            'A' => 'Baik (>80%)',
            'B' => 'Sedang (60-80%)',
            'C' => 'Rusak Ringan (40-60%)',
            'D' => 'Rusak Berat (<40%)',
            default => '-',
        };
    }

    public function badgeKondisi(): string
    {
        return match($this->kondisi) {
            'A' => 'bg-green-100 text-green-800',
            'B' => 'bg-yellow-100 text-yellow-800',
            'C' => 'bg-orange-100 text-orange-800',
            'D' => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-600',
        };
    }

    // Setelah save, update kondisi_terakhir di tabel assets
    protected static function booted(): void
    {
        static::saved(function (AssetCondition $condition) {
            $condition->asset->update([
                'kondisi_terakhir'      => $condition->kondisi,
                'rci_score_terakhir'    => $condition->rci_score,
                'tgl_inspeksi_terakhir' => $condition->tgl_inspeksi,
            ]);
        });
    }
}
