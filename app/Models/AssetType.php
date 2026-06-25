<?php namespace App\Models;
use App\Models\Concerns\{HasUuid, Auditable};
use Illuminate\Database\Eloquent\Model;

class AssetType extends Model
{
    use HasUuid;
    protected $fillable = [
        'kode', 'nama', 'kategori',
        'checklist_dokumen_wajib', 'atribut_teknis_template',
        'standar_op', 'urutan', 'is_aktif',
    ];
    protected function casts(): array
    {
        return [
            'checklist_dokumen_wajib'   => 'array',
            'atribut_teknis_template'   => 'array',
            'is_aktif'                  => 'boolean',
        ];
    }
    public function assets() { return $this->hasMany(Asset::class); }
    public function scopeAktif($q) { return $q->where('is_aktif', true); }
}
