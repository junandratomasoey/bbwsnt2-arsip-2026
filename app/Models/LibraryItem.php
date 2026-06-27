<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use App\Models\Concerns\HasPostgresArrays;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;

class LibraryItem extends Model
{
    use HasUuid, SoftDeletes, HasPostgresArrays;
    protected $table = 'library_items';
    protected $fillable = [
        'kode_item','judul','judul_singkat','tipe',
        'penulis','penerbit','tahun_terbit','edisi',
        'isbn','no_seri','bahasa','ddc','subjek',
        'stok_fisik','stok_dipinjam','ada_digital','file_digital_path',
        'physical_location_id','tags','metadata','is_aktif',
    ];
    protected array $pgArrayColumns = ['tags'];

    protected function casts(): array
    {
        return [
            'ada_digital' => 'boolean',
            'is_aktif'    => 'boolean',
            'tags'        => 'array',
            'metadata'    => 'array',
        ];
    }
    public function physicalLocation() { return $this->belongsTo(PhysicalLocation::class); }
    public function loans()            { return $this->hasMany(LibraryLoan::class); }
    public function scopeAktif($q)     { return $q->where('is_aktif', true); }
    public function scopeTersedia($q)  { return $q->whereRaw('stok_fisik > stok_dipinjam'); }
    public function stokTersedia(): int { return max(0, $this->stok_fisik - $this->stok_dipinjam); }

    public function scopeSearch($q, string $keyword)
    {
        return $q->whereRaw(
            "search_vector @@ plainto_tsquery('simple', ?)", [$keyword]
        )->orderByRaw(
            "ts_rank(search_vector, plainto_tsquery('simple', ?)) DESC", [$keyword]
        );
    }
}
