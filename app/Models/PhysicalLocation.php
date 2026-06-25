<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;

class PhysicalLocation extends Model
{
    use HasUuid;
    protected $table = 'physical_locations';
    protected $fillable = [
        'gedung','lantai','ruang','lemari','rak','laci',
        'kode_lokasi','qr_code_path',
        'kapasitas_item','terisi_item','keterangan','is_aktif',
    ];
    protected function casts(): array
    {
        return ['is_aktif' => 'boolean'];
    }
    public function documents() { return $this->hasMany(Document::class); }
    public function libraryItems() { return $this->hasMany(LibraryItem::class); }

    public function labelLengkap(): string
    {
        return implode(' › ', array_filter([
            $this->gedung,
            $this->lantai  ? 'Lt. ' . $this->lantai   : null,
            $this->ruang   ? 'Ruang ' . $this->ruang  : null,
            $this->lemari  ? 'Lmr. ' . $this->lemari  : null,
            $this->rak     ? 'Rak ' . $this->rak       : null,
            $this->laci    ? 'Laci ' . $this->laci     : null,
        ]));
    }

    public function sisaKapasitas(): ?int
    {
        if (!$this->kapasitas_item) return null;
        return $this->kapasitas_item - $this->terisi_item;
    }
}
