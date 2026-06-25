<?php

namespace App\Http\Controllers\Document;

use App\Http\Controllers\Controller;
use App\Models\PhysicalLocation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class PhysicalLocationController extends Controller
{
    public function index(Request $request)
    {
        $query = PhysicalLocation::withCount('documents')
            ->when($request->search, fn($q) => $q->where(function($q) use($request) {
                $q->where('gedung','ilike',"%{$request->search}%")
                  ->orWhere('kode_lokasi','ilike',"%{$request->search}%");
            }))
            ->when($request->gedung, fn($q) => $q->where('gedung', $request->gedung))
            ->orderBy('gedung')->orderBy('lantai')->orderBy('lemari')
            ->paginate(20)->withQueryString();

        $gedungList = PhysicalLocation::distinct()->pluck('gedung')->filter()->sort()->values();
        return view('document.locations.index', compact('query','gedungList'));
    }

    public function create()
    {
        $gedungList = PhysicalLocation::distinct()->pluck('gedung')->filter()->sort()->values();
        return view('document.locations.form', compact('gedungList'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'gedung'         => 'required|string|max:100',
            'lantai'         => 'nullable|string|max:20',
            'ruang'          => 'nullable|string|max:50',
            'lemari'         => 'nullable|string|max:20',
            'rak'            => 'nullable|string|max:20',
            'laci'           => 'nullable|string|max:20',
            'kode_lokasi'    => 'required|string|max:30|unique:physical_locations,kode_lokasi',
            'kapasitas_item' => 'nullable|integer|min:1',
            'keterangan'     => 'nullable|string',
        ]);
        $loc = PhysicalLocation::create($validated);
        return redirect()->route('locations.show', $loc)
            ->with('success', "Lokasi <strong>{$loc->kode_lokasi}</strong> berhasil ditambahkan.");
    }

    public function show(PhysicalLocation $location)
    {
        $location->load(['documents' => fn($q) => $q->latest()->limit(20)]);
        return view('document.locations.show', compact('location'));
    }

    public function edit(PhysicalLocation $location)
    {
        $gedungList = PhysicalLocation::distinct()->pluck('gedung')->filter()->sort()->values();
        return view('document.locations.form', compact('location','gedungList'));
    }

    public function update(Request $request, PhysicalLocation $location)
    {
        $validated = $request->validate([
            'gedung'         => 'required|string|max:100',
            'lantai'         => 'nullable|string|max:20',
            'ruang'          => 'nullable|string|max:50',
            'lemari'         => 'nullable|string|max:20',
            'rak'            => 'nullable|string|max:20',
            'laci'           => 'nullable|string|max:20',
            'kode_lokasi'    => "required|string|max:30|unique:physical_locations,kode_lokasi,{$location->id}",
            'kapasitas_item' => 'nullable|integer|min:1',
            'keterangan'     => 'nullable|string',
        ]);
        $location->update($validated);
        return redirect()->route('locations.show', $location)
            ->with('success', 'Lokasi berhasil diperbarui.');
    }

    public function destroy(PhysicalLocation $location)
    {
        if ($location->documents()->count()) return back()->with('error', 'Masih ada dokumen di lokasi ini.');
        $kode = $location->kode_lokasi;
        $location->delete();
        return redirect()->route('locations.index')->with('success', "Lokasi {$kode} berhasil dihapus.");
    }

    public function generateQr(PhysicalLocation $location)
    {
        $path = "qr/locations/{$location->id}.svg";
        Storage::disk('public')->put($path, QrCode::format('svg')->size(200)
            ->generate(route('locations.show', $location)));
        $location->update(['qr_code_path' => $path]);
        return back()->with('success', 'QR code berhasil dibuat.');
    }
}
