<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;

class AssetComponent extends Model
{
    use HasUuid;

    protected $table = 'asset_components';

    protected $fillable = [
        'asset_id', 'nama_komponen', 'kode_komponen',
        'material', 'tahun_pasang', 'umur_rencana',
        'spesifikasi', 'kondisi', 'is_kritis',
    ];

    protected function casts(): array
    {
        return ['is_kritis' => 'boolean'];
    }

    public function asset() { return $this->belongsTo(Asset::class); }

    public function scopeKritis($q) { return $q->where('is_kritis', true); }
}
