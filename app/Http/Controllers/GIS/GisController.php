<?php

namespace App\Http\Controllers\GIS;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\OpRecord;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GisController extends Controller
{
    // ── Halaman peta aset ─────────────────────────────────────────────
    public function index()
    {
        $gisConfig = [
            'center' => [
                'lat' => (float) \App\Models\SystemConfig::get('gis', 'default_lat', -9.5),
                'lng' => (float) \App\Models\SystemConfig::get('gis', 'default_lng', 124.0),
            ],
            'zoom'       => (int) \App\Models\SystemConfig::get('gis', 'default_zoom', 9),
            'geojsonUrl' => route('gis.geojson.assets'),
        ];

        $assetTypes = \App\Models\AssetType::aktif()->orderBy('urutan')->get();
        $unitKerjas = \App\Models\UnitKerja::satker()->aktif()->orderBy('nama')->get();

        return view('gis.index', compact('gisConfig', 'assetTypes', 'unitKerjas'));
    }

    public function assetMap(Request $request)
    {
        $gisConfig = [
            'center'     => ['lat' => -9.5, 'lng' => 124.0],
            'zoom'       => 9,
            'geojsonUrl' => route('gis.geojson.assets', $request->only(['asset_type_id', 'kondisi', 'unit_kerja_id'])),
        ];

        $assetTypes = \App\Models\AssetType::aktif()->orderBy('urutan')->get();
        $unitKerjas = \App\Models\UnitKerja::satker()->aktif()->orderBy('nama')->get();

        return view('gis.assets', compact('gisConfig', 'assetTypes', 'unitKerjas'));
    }

    public function opMap()
    {
        $tahun = now()->year;
        $gisConfig = [
            'center'     => ['lat' => -9.5, 'lng' => 124.0],
            'zoom'       => 9,
            'geojsonUrl' => route('gis.geojson.op', ['tahun' => $tahun]),
        ];
        return view('gis.op', compact('gisConfig', 'tahun'));
    }

    // ── GeoJSON: Semua aset dengan geometri ───────────────────────────
    public function geojsonAssets(Request $request)
    {
        $rows = DB::select("
            SELECT
                a.id,
                a.asset_code,
                a.nama,
                at.nama         AS jenis_aset,
                at.kategori,
                uk.singkatan    AS satker,
                a.kabupaten,
                a.kecamatan,
                a.lifecycle_status,
                a.kondisi_terakhir,
                a.rci_score_terakhir,
                a.tgl_inspeksi_terakhir,
                ST_AsGeoJSON(ag.geom)::json AS geometry,
                ag.geom_type
            FROM assets a
            JOIN  asset_types      at ON at.id = a.asset_type_id
            JOIN  unit_kerja       uk ON uk.id = a.unit_kerja_id
            JOIN  asset_geometries ag ON ag.asset_id = a.id AND ag.is_primary = true
            WHERE a.deleted_at IS NULL
            AND   a.is_aktif = true
            AND   ag.geom IS NOT NULL
            " . $this->buildWhereAsset($request) . "
            ORDER BY a.asset_code
            LIMIT 2000
        ");

        $features = array_map(fn($r) => [
            'type'       => 'Feature',
            'geometry'   => json_decode($r->geometry),
            'properties' => [
                'id'                   => $r->id,
                'asset_code'           => $r->asset_code,
                'nama'                 => $r->nama,
                'jenis_aset'           => $r->jenis_aset,
                'kategori'             => $r->kategori,
                'satker'               => $r->satker,
                'kabupaten'            => $r->kabupaten,
                'lifecycle_status'     => $r->lifecycle_status,
                'kondisi'              => $r->kondisi_terakhir ?? '-',
                'rci_score'            => $r->rci_score_terakhir,
                'tgl_inspeksi'         => $r->tgl_inspeksi_terakhir,
                'url'                  => route('assets.show', $r->id),
                // Warna marker berdasarkan kondisi
                'marker_color'         => $this->kondisiToColor($r->kondisi_terakhir),
                'marker_icon'          => $this->kategoriToIcon($r->kategori),
            ],
        ], $rows);

        return response()->json([
            'type'     => 'FeatureCollection',
            'features' => $features,
        ])->header('Cache-Control', 'public, max-age=300'); // Cache 5 menit
    }

    // ── GeoJSON: Sebaran OP ───────────────────────────────────────────
    public function geojsonOp(Request $request)
    {
        $tahun = $request->integer('tahun', now()->year);
        $bulan = $request->integer('bulan'); // 0 = semua bulan

        $rows = DB::select("
            SELECT
                a.id,
                a.asset_code,
                a.nama,
                at.kategori,
                uk.singkatan                AS satker,
                a.kabupaten,
                AVG(opr.realisasi_pct)      AS avg_realisasi,
                COUNT(opr.id)               AS total_record,
                SUM(CASE WHEN opr.status = 'selesai' THEN 1 ELSE 0 END) AS selesai,
                SUM(CASE WHEN opr.status = 'tidak_terlaksana' THEN 1 ELSE 0 END) AS tidak_terlaksana,
                ST_AsGeoJSON(ag.geom)::json AS geometry
            FROM assets a
            JOIN  asset_types      at  ON at.id  = a.asset_type_id
            JOIN  unit_kerja       uk  ON uk.id  = a.unit_kerja_id
            JOIN  asset_geometries ag  ON ag.asset_id = a.id AND ag.is_primary = true
            LEFT JOIN op_records   opr ON opr.asset_id = a.id
                AND opr.periode_tahun = ?
                " . ($bulan > 0 ? "AND opr.periode_bulan = {$bulan}" : '') . "
            WHERE a.deleted_at IS NULL AND a.is_aktif = true AND ag.geom IS NOT NULL
            GROUP BY a.id, a.asset_code, a.nama, at.kategori, uk.singkatan, a.kabupaten, ag.geom
            ORDER BY avg_realisasi DESC NULLS LAST
        ", [$tahun]);

        $features = array_map(fn($r) => [
            'type'     => 'Feature',
            'geometry' => json_decode($r->geometry),
            'properties' => [
                'id'              => $r->id,
                'asset_code'      => $r->asset_code,
                'nama'            => $r->nama,
                'satker'          => $r->satker,
                'kabupaten'       => $r->kabupaten,
                'avg_realisasi'   => round((float) $r->avg_realisasi, 1),
                'total_record'    => $r->total_record,
                'selesai'         => $r->selesai,
                'tidak_terlaksana'=> $r->tidak_terlaksana,
                'url'             => route('assets.show', $r->id),
                'marker_color'    => $this->opToColor((float) $r->avg_realisasi),
            ],
        ], $rows);

        return response()->json([
            'type'     => 'FeatureCollection',
            'features' => $features,
            'meta'     => [
                'tahun' => $tahun,
                'bulan' => $bulan ?: 'semua',
                'total' => count($features),
            ],
        ])->header('Cache-Control', 'public, max-age=60');
    }

    // ── GeoJSON: Proyek aktif ─────────────────────────────────────────
    public function geojsonProjects(Request $request)
    {
        $tahun = $request->integer('tahun', now()->year);

        $rows = DB::select("
            SELECT
                p.id, p.project_code, p.nama, p.lifecycle_phase,
                p.realisasi_fisik_pct, p.realisasi_keuangan_pct,
                p.tgl_selesai_rencana,
                uk.singkatan AS satker,
                at.nama AS jenis_aset,
                ST_AsGeoJSON(ag.geom)::json AS geometry
            FROM projects p
            JOIN  unit_kerja       uk  ON uk.id = p.unit_kerja_id
            LEFT JOIN assets       a   ON a.id  = p.asset_id
            LEFT JOIN asset_types  at  ON at.id = a.asset_type_id
            LEFT JOIN asset_geometries ag ON ag.asset_id = a.id AND ag.is_primary = true
            WHERE p.deleted_at IS NULL
            AND   p.tahun_anggaran = ?
            AND   p.lifecycle_phase NOT IN ('selesai','dibatalkan')
            AND   ag.geom IS NOT NULL
        ", [$tahun]);

        $features = array_map(fn($r) => [
            'type'     => 'Feature',
            'geometry' => json_decode($r->geometry),
            'properties' => [
                'id'              => $r->id,
                'project_code'    => $r->project_code,
                'nama'            => $r->nama,
                'phase'           => $r->lifecycle_phase,
                'realisasi_fisik' => $r->realisasi_fisik_pct,
                'satker'          => $r->satker,
                'url'             => route('projects.show', $r->id),
            ],
        ], $rows);

        return response()->json([
            'type'     => 'FeatureCollection',
            'features' => $features,
        ]);
    }

    // ── Helper private ────────────────────────────────────────────────
    private function buildWhereAsset(Request $request): string
    {
        $where = [];
        if ($request->asset_type_id) $where[] = "AND a.asset_type_id = '{$request->asset_type_id}'";
        if ($request->kondisi)       $where[] = "AND a.kondisi_terakhir = '{$request->kondisi}'";
        if ($request->unit_kerja_id) $where[] = "AND a.unit_kerja_id = '{$request->unit_kerja_id}'";
        return implode(' ', $where);
    }

    private function kondisiToColor(?string $kondisi): string
    {
        return match($kondisi) {
            'A' => '#22c55e', // green
            'B' => '#eab308', // yellow
            'C' => '#f97316', // orange
            'D' => '#ef4444', // red
            default => '#94a3b8', // gray (belum dinilai)
        };
    }

    private function opToColor(float $pct): string
    {
        if ($pct >= 90) return '#22c55e';
        if ($pct >= 70) return '#eab308';
        if ($pct >= 50) return '#f97316';
        return '#ef4444';
    }

    private function kategoriToIcon(string $kategori): string
    {
        return match($kategori) {
            'bendung'         => 'bendung',
            'embung'          => 'embung',
            'waduk'           => 'waduk',
            'saluran_irigasi' => 'saluran',
            'air_baku'        => 'air_baku',
            default           => 'lainnya',
        };
    }
}
