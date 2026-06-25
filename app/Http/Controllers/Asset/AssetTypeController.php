<?php
namespace App\Http\Controllers\Asset;
use App\Http\Controllers\Controller;
use App\Models\AssetType;
use Illuminate\Http\Request;

class AssetTypeController extends Controller
{
    public function index()
    {
        $types = AssetType::withCount('assets')->orderBy('urutan')->get();
        return view('superadmin.asset-types.index', compact('types'));
    }

    public function create()
    {
        return view('superadmin.asset-types.form');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode'      => 'required|string|max:20|unique:asset_types,kode',
            'nama'      => 'required|string|max:255',
            'kategori'  => 'required|string|max:100',
            'standar_op'=> 'nullable|string|max:255',
            'urutan'    => 'nullable|integer|min:0',
        ]);
        AssetType::create($validated + ['is_aktif' => true]);
        return redirect()->route('superadmin.asset-types.index')
            ->with('success', "Jenis aset <strong>{$validated['nama']}</strong> berhasil ditambahkan.");
    }

    public function edit(AssetType $assetType)
    {
        return view('superadmin.asset-types.form', compact('assetType'));
    }

    public function update(Request $request, AssetType $assetType)
    {
        $validated = $request->validate([
            'kode'      => "required|string|max:20|unique:asset_types,kode,{$assetType->id}",
            'nama'      => 'required|string|max:255',
            'kategori'  => 'required|string|max:100',
            'standar_op'=> 'nullable|string|max:255',
            'urutan'    => 'nullable|integer|min:0',
            'is_aktif'  => 'boolean',
        ]);
        $assetType->update($validated);
        return redirect()->route('superadmin.asset-types.index')
            ->with('success', "Jenis aset berhasil diperbarui.");
    }

    public function destroy(AssetType $assetType)
    {
        if ($assetType->assets()->count() > 0) {
            return back()->with('error', 'Jenis aset masih digunakan oleh ' . $assetType->assets()->count() . ' aset.');
        }
        $assetType->delete();
        return redirect()->route('superadmin.asset-types.index')->with('success', 'Jenis aset dihapus.');
    }
}
