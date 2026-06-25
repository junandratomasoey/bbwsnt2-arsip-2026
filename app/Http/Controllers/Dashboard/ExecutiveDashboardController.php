<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\OpRecord;
use App\Models\Project;
use Illuminate\Support\Facades\DB;

class ExecutiveDashboardController extends Controller
{
    public function index()
    {
        $tahun = now()->year;

        $asetPerJenis = DB::select("
            SELECT at.nama, at.kategori,
                   COUNT(a.id) as jumlah,
                   COUNT(CASE WHEN a.kondisi_terakhir = 'A' THEN 1 END) as kondisi_a,
                   COUNT(CASE WHEN a.kondisi_terakhir = 'B' THEN 1 END) as kondisi_b,
                   COUNT(CASE WHEN a.kondisi_terakhir IN ('C','D') THEN 1 END) as kondisi_buruk
            FROM asset_types at
            LEFT JOIN assets a ON a.asset_type_id = at.id
                AND a.deleted_at IS NULL AND a.is_aktif = true
            WHERE at.is_aktif = true
            GROUP BY at.id, at.nama, at.kategori
            ORDER BY jumlah DESC
        ");

        $trenOp = DB::select("
            SELECT periode_bulan,
                   COUNT(*) as total,
                   ROUND(AVG(realisasi_pct)::numeric, 1) as avg_realisasi,
                   SUM(CASE WHEN status = 'selesai' THEN 1 ELSE 0 END) as selesai
            FROM op_records
            WHERE periode_tahun = ?
            GROUP BY periode_bulan
            ORDER BY periode_bulan
        ", [$tahun]);

        $realisasiProyek = DB::select("
            SELECT lifecycle_phase,
                   COUNT(*) as jumlah,
                   ROUND(AVG(realisasi_fisik_pct)::numeric, 1) as avg_fisik,
                   ROUND(AVG(realisasi_keuangan_pct)::numeric, 1) as avg_keuangan,
                   SUM(nilai_kontrak) as total_nilai
            FROM projects
            WHERE tahun_anggaran = ? AND deleted_at IS NULL
            GROUP BY lifecycle_phase
        ", [$tahun]);

        $kelengkapanDokPerSatker = DB::select("
            SELECT uk.singkatan, uk.nama,
                   COUNT(DISTINCT a.id) as total_aset,
                   COUNT(DISTINCT d.entity_id) as aset_dengan_dokumen,
                   ROUND(
                       COUNT(DISTINCT d.entity_id)::numeric /
                       NULLIF(COUNT(DISTINCT a.id), 0) * 100, 1
                   ) as pct_kelengkapan
            FROM unit_kerja uk
            LEFT JOIN assets a ON a.unit_kerja_id = uk.id AND a.deleted_at IS NULL
            LEFT JOIN documents d ON d.entity_type = 'App\\Models\\Asset'
                AND d.entity_id::uuid = a.id AND d.deleted_at IS NULL
            WHERE uk.tipe = 'satker' AND uk.is_aktif = true
            GROUP BY uk.id, uk.singkatan, uk.nama
            ORDER BY pct_kelengkapan DESC NULLS LAST
        ");

        $summary = [
            'total_nilai_kontrak' => Project::tahun($tahun)->sum('nilai_kontrak'),
            'avg_realisasi_fisik' => round((float) Project::tahun($tahun)->avg('realisasi_fisik_pct'), 1),
            'aset_kondisi_baik_pct' => Asset::aktif()->count() > 0
                ? round(Asset::where('kondisi_terakhir','A')->aktif()->count()
                    / Asset::aktif()->count() * 100, 1)
                : 0,
            'op_selesai_pct' => $this->pctOpSelesai($tahun),
        ];

        return view('dashboard.executive', compact(
            'asetPerJenis', 'trenOp', 'realisasiProyek',
            'kelengkapanDokPerSatker', 'summary', 'tahun'
        ));
    }

    private function pctOpSelesai(int $tahun): float
    {
        $total   = OpRecord::tahun($tahun)->count();
        $selesai = OpRecord::tahun($tahun)->selesai()->count();
        return $total > 0 ? round($selesai / $total * 100, 1) : 0;
    }
}
