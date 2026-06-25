<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\Project;
use App\Models\OpRecord;
use App\Models\Document;
use App\Models\Loan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    public function index()
    {
        return view('report.index');
    }

    // ── Laporan Aset ─────────────────────────────────────────────────
    public function assets(Request $request)
    {
        $data = Asset::with(['assetType','unitKerja'])
            ->aktif()
            ->when($request->asset_type_id, fn($q) => $q->where('asset_type_id', $request->asset_type_id))
            ->when($request->kondisi,       fn($q) => $q->where('kondisi_terakhir', $request->kondisi))
            ->when($request->unit_kerja_id, fn($q) => $q->where('unit_kerja_id', $request->unit_kerja_id))
            ->orderBy('asset_code')->get();

        $summary = [
            'total'       => $data->count(),
            'kondisi_a'   => $data->where('kondisi_terakhir','A')->count(),
            'kondisi_b'   => $data->where('kondisi_terakhir','B')->count(),
            'kondisi_c'   => $data->where('kondisi_terakhir','C')->count(),
            'kondisi_d'   => $data->where('kondisi_terakhir','D')->count(),
            'belum_dinilai'=> $data->whereNull('kondisi_terakhir')->count(),
        ];

        $assetTypes  = \App\Models\AssetType::aktif()->get();
        $unitKerjas  = \App\Models\UnitKerja::satker()->aktif()->get();

        return view('report.assets', compact('data','summary','assetTypes','unitKerjas'));
    }

    // ── Laporan OP ───────────────────────────────────────────────────
    public function op(Request $request)
    {
        $tahun = $request->integer('tahun', now()->year);

        $data = DB::select("
            SELECT
                a.asset_code, a.nama AS nama_aset, at.nama AS jenis,
                uk.singkatan AS satker, a.kabupaten,
                COUNT(opr.id) AS total_op,
                SUM(CASE WHEN opr.status = 'selesai' THEN 1 ELSE 0 END) AS selesai,
                SUM(CASE WHEN opr.status = 'tidak_terlaksana' THEN 1 ELSE 0 END) AS tidak_terlaksana,
                ROUND(AVG(opr.realisasi_pct)::numeric, 1) AS avg_realisasi,
                SUM(opr.realisasi_anggaran) AS total_realisasi_anggaran
            FROM assets a
            JOIN asset_types at ON at.id = a.asset_type_id
            JOIN unit_kerja uk ON uk.id = a.unit_kerja_id
            LEFT JOIN op_records opr ON opr.asset_id = a.id AND opr.periode_tahun = ?
            WHERE a.deleted_at IS NULL AND a.is_aktif = true
            GROUP BY a.id, a.asset_code, a.nama, at.nama, uk.singkatan, a.kabupaten
            ORDER BY avg_realisasi DESC NULLS LAST
        ", [$tahun]);

        $tahunList = range(now()->year, 2015);
        return view('report.op', compact('data','tahun','tahunList'));
    }

    // ── Laporan Proyek ───────────────────────────────────────────────
    public function projects(Request $request)
    {
        $tahun = $request->integer('tahun', now()->year);
        $data  = Project::with(['asset','unitKerja'])
            ->tahun($tahun)
            ->when($request->lifecycle_phase, fn($q) => $q->where('lifecycle_phase', $request->lifecycle_phase))
            ->orderBy('lifecycle_phase')->orderBy('nama')
            ->get();

        $summary = [
            'total'              => $data->count(),
            'aktif'              => $data->whereNotIn('lifecycle_phase',['selesai','dibatalkan'])->count(),
            'selesai'            => $data->where('lifecycle_phase','selesai')->count(),
            'total_nilai_kontrak'=> $data->sum('nilai_kontrak'),
            'avg_realisasi_fisik'=> round($data->avg('realisasi_fisik_pct'), 1),
        ];

        $tahunList = range(now()->year, 2015);
        return view('report.projects', compact('data','summary','tahun','tahunList'));
    }

    // ── Laporan Dokumen ──────────────────────────────────────────────
    public function documents(Request $request)
    {
        $data = Document::with(['documentType','unitKerja','uploadedBy'])
            ->aksesibel(auth()->user())
            ->when($request->unit_kerja_id,    fn($q) => $q->where('unit_kerja_id', $request->unit_kerja_id))
            ->when($request->document_type_id, fn($q) => $q->where('document_type_id', $request->document_type_id))
            ->when($request->status,           fn($q) => $q->where('status', $request->status))
            ->when($request->filter === 'kadaluwarsa', fn($q) => $q->kadaluwarsa())
            ->orderBy('judul')->get();

        $unitKerjas = \App\Models\UnitKerja::satker()->aktif()->get();
        $docTypes   = \App\Models\DocumentType::aktif()->get();
        return view('report.documents', compact('data','unitKerjas','docTypes'));
    }

    // ── Laporan Peminjaman ───────────────────────────────────────────
    public function loans(Request $request)
    {
        $data = Loan::with(['document','borrower','approvedBy'])
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->jenis,  fn($q) => $q->where('jenis', $request->jenis))
            ->latest()->get();

        return view('report.loans', compact('data'));
    }

    // ── Export Excel ─────────────────────────────────────────────────
    public function exportAssets(Request $request)
    {
        $data = Asset::with(['assetType','unitKerja'])->aktif()->orderBy('asset_code')->get();

        return Excel::download(new class($data) implements \Maatwebsite\Excel\Concerns\FromCollection,
            \Maatwebsite\Excel\Concerns\WithHeadings,
            \Maatwebsite\Excel\Concerns\WithStyles
        {
            public function __construct(private $data) {}

            public function collection()
            {
                return $this->data->map(fn($a) => [
                    $a->asset_code,
                    $a->nama,
                    $a->assetType?->nama,
                    $a->unitKerja?->singkatan,
                    $a->kabupaten,
                    $a->kecamatan,
                    $a->tahun_bangun,
                    $a->lifecycle_status,
                    $a->kondisi_terakhir ?? 'Belum Dinilai',
                    $a->rci_score_terakhir,
                    $a->tgl_inspeksi_terakhir?->format('d/m/Y'),
                    number_format($a->nilai_perolehan ?? 0, 0, ',', '.'),
                ]);
            }

            public function headings(): array
            {
                return ['Kode Aset','Nama','Jenis','Satker','Kabupaten','Kecamatan',
                        'Thn Bangun','Lifecycle','Kondisi','RCI Score','Tgl Inspeksi Terakhir','Nilai Perolehan (Rp)'];
            }

            public function styles(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet)
            {
                return [1 => ['font' => ['bold' => true]]];
            }
        }, 'Rekap_Aset_BBWSNT2_' . now()->format('Ymd') . '.xlsx');
    }

    public function exportOp(Request $request)
    {
        $tahun = $request->integer('tahun', now()->year);
        $data  = DB::select("
            SELECT a.asset_code, a.nama, at.nama AS jenis, uk.singkatan AS satker,
                   COUNT(opr.id) AS total, SUM(CASE WHEN opr.status='selesai' THEN 1 ELSE 0 END) AS selesai,
                   ROUND(AVG(opr.realisasi_pct)::numeric,1) AS avg_realisasi
            FROM assets a
            JOIN asset_types at ON at.id = a.asset_type_id
            JOIN unit_kerja uk ON uk.id = a.unit_kerja_id
            LEFT JOIN op_records opr ON opr.asset_id = a.id AND opr.periode_tahun = ?
            WHERE a.deleted_at IS NULL AND a.is_aktif = true
            GROUP BY a.id, a.asset_code, a.nama, at.nama, uk.singkatan
            ORDER BY avg_realisasi DESC NULLS LAST
        ", [$tahun]);

        return Excel::download(new class($data, $tahun) implements \Maatwebsite\Excel\Concerns\FromArray,
            \Maatwebsite\Excel\Concerns\WithHeadings
        {
            public function __construct(private $data, private $tahun) {}
            public function array(): array { return array_map(fn($r) => (array)$r, $this->data); }
            public function headings(): array
            {
                return ['Kode','Nama Aset','Jenis','Satker','Total Record','Selesai','Avg Realisasi (%)'];
            }
        }, "Rekap_OP_{$tahun}_BBWSNT2.xlsx");
    }

    public function exportProjects(Request $request)
    {
        $tahun = $request->integer('tahun', now()->year);
        $data  = Project::with(['asset','unitKerja'])->tahun($tahun)->get();

        return Excel::download(new class($data) implements \Maatwebsite\Excel\Concerns\FromCollection,
            \Maatwebsite\Excel\Concerns\WithHeadings
        {
            public function __construct(private $data) {}
            public function collection()
            {
                return $this->data->map(fn($p) => [
                    $p->project_code, $p->nama, $p->jenis,
                    $p->unitKerja?->singkatan, $p->asset?->nama,
                    $p->lifecycle_phase, $p->no_kontrak, $p->kontraktor,
                    number_format($p->nilai_kontrak ?? 0, 0, ',', '.'),
                    $p->realisasi_fisik_pct . '%',
                    $p->realisasi_keuangan_pct . '%',
                    $p->tgl_selesai_rencana?->format('d/m/Y'),
                ]);
            }
            public function headings(): array
            {
                return ['Kode','Nama','Jenis','Satker','Aset','Phase','No Kontrak','Kontraktor',
                        'Nilai Kontrak','Real Fisik','Real Keuangan','Tgl Selesai Rencana'];
            }
        }, "Rekap_Proyek_{$tahun}_BBWSNT2.xlsx");
    }

    public function exportDocuments(Request $request)
    {
        $data = Document::with(['documentType','unitKerja'])
            ->aksesibel(auth()->user())->orderBy('judul')->get();

        return Excel::download(new class($data) implements \Maatwebsite\Excel\Concerns\FromCollection,
            \Maatwebsite\Excel\Concerns\WithHeadings
        {
            public function __construct(private $data) {}
            public function collection()
            {
                return $this->data->map(fn($d) => [
                    $d->doc_number, $d->judul,
                    $d->documentType?->nama, $d->unitKerja?->singkatan,
                    $d->klasifikasi, $d->status, $d->versiLabel(),
                    $d->tgl_dokumen?->format('d/m/Y'),
                    $d->tgl_kedaluwarsa?->format('d/m/Y'),
                    $d->ada_fisik ? 'Ya' : 'Tidak',
                    $d->ada_digital ? 'Ya' : 'Tidak',
                ]);
            }
            public function headings(): array
            {
                return ['No. Dokumen','Judul','Jenis','Satker','Klasifikasi','Status','Versi',
                        'Tgl Dokumen','Tgl Kadaluwarsa','Ada Fisik','Ada Digital'];
            }
        }, 'Rekap_Dokumen_BBWSNT2_' . now()->format('Ymd') . '.xlsx');
    }
}
