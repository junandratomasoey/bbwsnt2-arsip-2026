<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\AuditLog;
use App\Models\Loan;
use App\Models\OpRecord;
use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user  = auth()->user();
        $tahun = now()->year;
        $bulan = now()->month;

        // ── Stats ─────────────────────────────────────────────────────
        try {
            $stats = DB::selectOne('SELECT * FROM mv_dashboard_stats');
        } catch (\Exception $e) {
            $stats = null;
        }

        $totalAset       = $stats?->total_aset       ?? Asset::aktif()->count();
        $totalDokumen    = $stats?->total_dokumen    ?? DB::table('documents')->whereNull('deleted_at')->count();
        $proyekAktif     = $stats?->proyek_aktif     ?? Project::aktif()->count();
        $peminjamanAktif = $stats?->peminjaman_aktif ?? Loan::aktif()->count();
        $userPending     = $stats?->user_pending     ?? User::pending()->count();
        $dokumenExpired  = $stats?->dokumen_kadaluwarsa ?? DB::table('documents')
            ->whereNull('deleted_at')->whereNotNull('tgl_kedaluwarsa')
            ->where('tgl_kedaluwarsa', '<', now())->count();
        $asetBuruk = $stats?->aset_kondisi_buruk ?? Asset::kondisiBuruk()->aktif()->count();

        // ── Aset kondisi buruk ────────────────────────────────────────
        $asetKondisiBuruk = Asset::with(['assetType', 'unitKerja'])
            ->kondisiBuruk()->aktif()->orderBy('kondisi_terakhir')->limit(5)->get();

        // ── Proyek terlambat ──────────────────────────────────────────
        $proyekTerlambat = Project::with(['asset', 'unitKerja'])
            ->terlambat()->orderBy('tgl_selesai_rencana')->limit(5)->get();

        // ── OP bulan ini ──────────────────────────────────────────────
        $opBulanIni = OpRecord::tahun($tahun)->bulan($bulan)
            ->selectRaw('status, COUNT(*) as jumlah')
            ->groupBy('status')
            ->pluck('jumlah', 'status')->toArray();

        // ── Peminjaman menunggu ───────────────────────────────────────
        $peminjamanMenunggu = [];
        if ($user->can('loan.approve')) {
            $peminjamanMenunggu = Loan::with(['document', 'borrower'])
                ->menunggu()->latest()->limit(5)->get();
        }

        // ── Dokumen hampir kadaluwarsa — pakai DB::table langsung ─────
        $dokumenMauExpired = DB::table('documents')
            ->join('document_types', 'documents.document_type_id', '=', 'document_types.id')
            ->whereNull('documents.deleted_at')
            ->whereNotNull('documents.tgl_kedaluwarsa')
            ->whereBetween('documents.tgl_kedaluwarsa', [now(), now()->addDays(30)])
            ->select('documents.id', 'documents.judul', 'documents.tgl_kedaluwarsa',
                     'document_types.nama as jenis_dokumen')
            ->limit(5)->get();

        // ── Notifikasi belum dibaca ───────────────────────────────────
        $notifikasiBelumDibaca = $user->unreadNotifications()->limit(5)->get();

        // ── User pending ──────────────────────────────────────────────
        $usersPending = [];
        if ($user->can('user.approve')) {
            $usersPending = User::pending()->with('unitKerja')->latest()->limit(4)->get();
        }

        // ── Log aktivitas terbaru ─────────────────────────────────────
        $aktivitasTerbaru = AuditLog::orderByDesc('created_at')->limit(8)->get();

        return view('dashboard.index', compact(
            'totalAset', 'totalDokumen', 'proyekAktif', 'peminjamanAktif',
            'userPending', 'dokumenExpired', 'asetBuruk',
            'asetKondisiBuruk', 'proyekTerlambat', 'opBulanIni',
            'peminjamanMenunggu', 'dokumenMauExpired',
            'notifikasiBelumDibaca', 'usersPending', 'aktivitasTerbaru',
            'tahun', 'bulan'
        ));
    }
}
