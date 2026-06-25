<?php
namespace App\Http\Controllers\Asset;
use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\AssetGeometry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AssetGeometryController extends Controller
{
    public function index(Asset $asset)
    {
        $geometries = $asset->geometries()->selectRaw('*, ST_AsGeoJSON(geom) as geojson')->get();
        return view('asset.geometry.index', compact('asset', 'geometries'));
    }

    public function store(Request $request, Asset $asset)
    {
        $request->validate([
            'geom_type' => 'required|in:point,line,polygon',
            'label'     => 'nullable|string|max:100',
            'lat'       => 'required_if:geom_type,point|nullable|numeric|between:-90,90',
            'lng'       => 'required_if:geom_type,point|nullable|numeric|between:-180,180',
            'geojson'   => 'required_without_all:lat,lng|nullable|json',
        ]);

        // Jika point — konversi lat/lng ke WKT
        if ($request->geom_type === 'point' && $request->lat && $request->lng) {
            $wkt = "SRID=4326;POINT({$request->lng} {$request->lat})";
        } elseif ($request->geojson) {
            $wkt = "SRID=4326;" . DB::selectOne("SELECT ST_AsText(ST_GeomFromGeoJSON(?)::geometry) as wkt",
                [$request->geojson])->wkt;
        } else {
            return back()->with('error', 'Data geometri tidak valid.');
        }

        // Jika is_primary, reset yang lama
        if ($request->boolean('is_primary')) {
            $asset->geometries()->update(['is_primary' => false]);
        }

        DB::statement("
            INSERT INTO asset_geometries (id, asset_id, geom_type, label, keterangan, is_primary, geom, created_at, updated_at)
            VALUES (gen_random_uuid(), ?, ?, ?, ?, ?, ST_GeomFromEWKT(?), NOW(), NOW())
        ", [
            $asset->id, $request->geom_type, $request->label,
            $request->keterangan, $request->boolean('is_primary'), $wkt
        ]);

        return back()->with('success', 'Geometri berhasil disimpan.');
    }

    public function update(Request $request, Asset $asset, AssetGeometry $geometry)
    {
        if ($request->boolean('is_primary')) {
            $asset->geometries()->update(['is_primary' => false]);
        }
        $geometry->update([
            'label'      => $request->label,
            'keterangan' => $request->keterangan,
            'is_primary' => $request->boolean('is_primary'),
        ]);
        return back()->with('success', 'Geometri diperbarui.');
    }

    public function destroy(Asset $asset, AssetGeometry $geometry)
    {
        $geometry->delete();
        return back()->with('success', 'Geometri dihapus.');
    }
}
