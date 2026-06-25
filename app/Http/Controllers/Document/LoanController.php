<?php

namespace App\Http\Controllers\Document;

use App\Http\Controllers\Controller;
use App\Models\Loan;
use App\Models\Document;
use App\Models\Notification;
use Illuminate\Http\Request;

class LoanController extends Controller
{
    public function index(Request $request)
    {
        $user  = auth()->user();
        $query = Loan::with(['document.documentType','borrower','approvedBy'])
            ->when(!$user->isSuperAdmin() && !$user->can('loan.approve'),
                fn($q) => $q->where('borrower_id', $user->id))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->jenis,  fn($q) => $q->where('jenis', $request->jenis))
            ->when($request->search, fn($q) => $q->whereHas('document',
                fn($dq) => $dq->where('judul','ilike',"%{$request->search}%")))
            ->latest()->paginate(20)->withQueryString();

        $stats = [
            'menunggu'  => Loan::menunggu()->count(),
            'dipinjam'  => Loan::where('status','borrowed')->count(),
            'terlambat' => Loan::where('status','borrowed')
                ->where('tgl_kembali_rencana','<',now())->count(),
        ];

        return view('document.loans.index', compact('query','stats'));
    }

    public function create(Request $request)
    {
        $dokumen = null;
        if ($request->document_id) {
            $dokumen = Document::findOrFail($request->document_id);
        }
        $documents = Document::aksesibel(auth()->user())->approved()->orderBy('judul')->get();
        return view('document.loans.create', compact('documents','dokumen'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'document_id'         => 'required|exists:documents,id',
            'jenis'               => 'required|in:fisik,digital',
            'tgl_pinjam_rencana'  => 'required|date|after_or_equal:today',
            'tgl_kembali_rencana' => 'required|date|after:tgl_pinjam_rencana',
            'keperluan'           => 'required|string|min:10|max:500',
        ]);

        // Cek dokumen fisik tidak sedang dipinjam
        if ($validated['jenis'] === 'fisik') {
            $sedangDipinjam = Loan::where('document_id', $validated['document_id'])
                ->where('jenis','fisik')
                ->whereIn('status',['approved','borrowed'])->exists();
            if ($sedangDipinjam) {
                return back()->withErrors(['document_id' => 'Dokumen fisik ini sedang dipinjam.']);
            }
        }

        $loan = Loan::create([
            ...$validated,
            'borrower_id' => auth()->id(),
            'status'      => 'requested',
        ]);

        // Notif ke admin/arsiparis
        $adminIds = \App\Models\User::role(['admin_satker','arsiparis'])->pluck('id')->toArray();
        if (!empty($adminIds)) {
            Notification::kirim($adminIds, 'loan.requested',
                'Permohonan Peminjaman',
                auth()->user()->name . " mengajukan peminjaman dokumen: {$loan->document->judul}",
                ['icon'=>'ti-book-download','level'=>'info','action_url'=>route('loans.show',$loan)]
            );
        }

        return redirect()->route('loans.show', $loan)
            ->with('success', 'Permohonan peminjaman berhasil diajukan.');
    }

    public function show(Loan $loan)
    {
        if (!auth()->user()->can('loan.approve') && $loan->borrower_id !== auth()->id()) {
            abort(403);
        }
        $loan->load(['document.physicalLocation','borrower','approvedBy']);
        return view('document.loans.show', compact('loan'));
    }

    public function approve(Loan $loan)
    {
        abort_unless(in_array($loan->status, ['requested']), 422, 'Status tidak valid.');
        $loan->update([
            'status'      => 'borrowed',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
            'tgl_diambil' => now(),
        ]);
        Notification::kirim($loan->borrower_id, 'loan.approved',
            'Peminjaman Disetujui',
            "Peminjaman dokumen '{$loan->document->judul}' Anda telah disetujui.",
            ['icon'=>'ti-check','level'=>'success','action_url'=>route('loans.show',$loan)]
        );
        return back()->with('success', 'Peminjaman disetujui.');
    }

    public function tolak(Request $request, Loan $loan)
    {
        $request->validate(['alasan_ditolak' => 'required|string|min:5']);
        $loan->update([
            'status'         => 'rejected',
            'alasan_ditolak' => $request->alasan_ditolak,
            'approved_by'    => auth()->id(),
            'approved_at'    => now(),
        ]);
        return back()->with('success', 'Peminjaman ditolak.');
    }

    public function kembalikan(Loan $loan)
    {
        abort_unless($loan->status === 'borrowed', 422, 'Dokumen belum dipinjam.');
        $loan->update(['status' => 'returned', 'tgl_dikembalikan' => now()]);
        return back()->with('success', 'Dokumen berhasil dicatat dikembalikan.');
    }

    public function destroy(Loan $loan)
    {
        abort_unless(in_array($loan->status, ['requested','rejected']), 403, 'Tidak bisa dihapus.');
        $loan->delete();
        return redirect()->route('loans.index')->with('success', 'Permohonan dihapus.');
    }
}
