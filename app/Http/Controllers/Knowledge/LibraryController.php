<?php

namespace App\Http\Controllers\Knowledge;

use App\Http\Controllers\Controller;
use App\Models\LibraryItem;
use App\Models\LibraryLoan;
use App\Models\PhysicalLocation;
use App\Models\Notification;
use Illuminate\Http\Request;

class LibraryController extends Controller
{
    public function index(Request $request)
    {
        $query = \App\Models\LibraryItem::with('physicalLocation')
            ->when($request->search, fn($q) => $q->search($request->search))
            ->when($request->tipe,   fn($q) => $q->where('tipe', $request->tipe))
            ->aktif()->orderBy('judul')
            ->paginate(20)->withQueryString();

        $tipeList = ['buku','jurnal','standar','peraturan','laporan','prosiding','manual_teknis'];
        return view('knowledge.library.index', compact('query','tipeList'));
    }

    public function create()
    {
        $locations = \App\Models\PhysicalLocation::orderBy('gedung')->get();
        $tipeList  = ['buku','jurnal','standar','peraturan','laporan','prosiding','manual_teknis'];
        return view('knowledge.library.form', compact('locations','tipeList'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode_item'           => 'required|string|max:20|unique:library_items,kode_item',
            'judul'               => 'required|string|max:255',
            'tipe'                => 'required|in:buku,jurnal,standar,peraturan,laporan,prosiding,manual_teknis',
            'penulis'             => 'nullable|string|max:255',
            'penerbit'            => 'nullable|string|max:255',
            'tahun_terbit'        => 'nullable|integer|min:1900|max:' . now()->year,
            'isbn'                => 'nullable|string|max:20',
            'stok_fisik'          => 'required|integer|min:0',
            'physical_location_id'=> 'nullable|exists:physical_locations,id',
            'tags'                => 'nullable|string',
            'file_digital'        => 'nullable|file|mimes:pdf|max:51200',
        ]);

        $tags = !empty($validated['tags'])
            ? array_map('trim', explode(',', $validated['tags'])) : [];

        $filePath = null;
        if ($request->hasFile('file_digital')) {
            $filePath = $request->file('file_digital')->store('library/digital','local');
        }

        $item = \App\Models\LibraryItem::create([
            ...$validated,
            'tags'              => $tags,
            'ada_digital'       => $filePath !== null,
            'file_digital_path' => $filePath,
        ]);

        return redirect()->route('library.show', $item)
            ->with('success', "Item <strong>{$item->judul}</strong> berhasil ditambahkan.");
    }

    public function show(\App\Models\LibraryItem $item)
    {
        $item->load(['physicalLocation','loans' => fn($q) => $q->latest()->limit(5)]);
        return view('knowledge.library.show', compact('item'));
    }

    public function edit(\App\Models\LibraryItem $item)
    {
        $locations = \App\Models\PhysicalLocation::orderBy('gedung')->get();
        $tipeList  = ['buku','jurnal','standar','peraturan','laporan','prosiding','manual_teknis'];
        return view('knowledge.library.form', compact('item','locations','tipeList'));
    }

    public function update(Request $request, \App\Models\LibraryItem $item)
    {
        $validated = $request->validate([
            'judul'               => 'required|string|max:255',
            'penulis'             => 'nullable|string|max:255',
            'stok_fisik'          => 'required|integer|min:0',
            'physical_location_id'=> 'nullable|exists:physical_locations,id',
            'tags'                => 'nullable|string',
        ]);
        $tags = !empty($validated['tags'])
            ? array_map('trim', explode(',', $validated['tags'])) : [];
        $item->update([...$validated, 'tags' => $tags]);
        return redirect()->route('library.show', $item)->with('success', 'Item berhasil diperbarui.');
    }

    public function destroy(\App\Models\LibraryItem $item)
    {
        $item->delete();
        return redirect()->route('library.index')->with('success', 'Item berhasil dihapus.');
    }

    public function pinjam(Request $request, \App\Models\LibraryItem $item)
    {
        $request->validate([
            'tgl_pinjam_rencana'  => 'required|date|after_or_equal:today',
            'tgl_kembali_rencana' => 'required|date|after:tgl_pinjam_rencana',
            'keperluan'           => 'required|string|min:5',
        ]);
        if ($item->stokTersedia() <= 0) return back()->with('error', 'Stok tidak tersedia.');
        \App\Models\LibraryLoan::create([
            ...$request->only(['tgl_pinjam_rencana','tgl_kembali_rencana','keperluan']),
            'library_item_id' => $item->id,
            'borrower_id'     => auth()->id(),
            'status'          => 'requested',
        ]);
        return back()->with('success', 'Permohonan peminjaman buku berhasil diajukan.');
    }

    public function kembalikan(\App\Models\LibraryLoan $loan)
    {
        $loan->update(['status'=>'returned','tgl_dikembalikan'=>now()]);
        $loan->libraryItem->decrement('stok_dipinjam');
        return back()->with('success', 'Buku berhasil dicatat dikembalikan.');
    }

    public function loans(Request $request)
    {
        $loans = \App\Models\LibraryLoan::with(['libraryItem','borrower'])
            ->when(!auth()->user()->can('library_loan.approve'),
                fn($q) => $q->where('borrower_id', auth()->id()))
            ->latest()->paginate(20);
        return view('knowledge.library.loans', compact('loans'));
    }
}
