<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;

class AssetGeometry extends Model
{
    use HasUuid;

    protected $fillable = [
        'asset_id', 'geom_type', 'label', 'keterangan',
        'is_primary', 'properties',
    ];

    protected function casts(): array
    {
        return [
            'is_primary'  => 'boolean',
            'properties'  => 'array',
        ];
    }

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }

    // Ambil koordinat sebagai array [lat, lng] dari PostGIS geometry
    // Dipanggil setelah query dengan ST_AsGeoJSON atau ST_Y/ST_X
    public function getLatLngAttribute(): ?array
    {
        // Jika ada kolom lat/lng hasil SELECT ST_Y(geom), ST_X(geom)
        if (isset($this->attributes['lat']) && isset($this->attributes['lng'])) {
            return [
                'lat' => (float) $this->attributes['lat'],
                'lng' => (float) $this->attributes['lng'],
            ];
        }
        return null;
    }

    // Scope untuk query spasial — pakai DB::raw untuk PostGIS functions
    public function scopeWithLatLng($q)
    {
        return $q->selectRaw('*, ST_Y(geom::geometry) as lat, ST_X(geom::geometry) as lng');
    }

    public function scopePrimary($q)
    {
        return $q->where('is_primary', true);
    }

    public function scopeTitik($q)   { return $q->where('geom_type', 'point'); }
    public function scopeGaris($q)   { return $q->where('geom_type', 'line'); }
    public function scopePolygon($q) { return $q->where('geom_type', 'polygon'); }
}
