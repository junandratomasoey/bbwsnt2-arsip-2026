<?php
namespace App\Http\Controllers\Asset;
use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\AssetCondition;
use Illuminate\Http\Request;

class AssetConditionController extends Controller
{
    public function index(Asset $asset)
    {
        $conditions = $asset->conditions()->with('inspektur')->paginate(15);
        return view('asset.conditions.index', compact('asset', 'conditions'));
    }

    public function create(Asset $asset)
    {
        return view('asset.conditions.form', compact('asset'));
    }

    public function store(Request $request, Asset $asset)
    {
        $validated = $request->validate([
            'tgl_inspeksi'         => 'required|date',
            'jenis_inspeksi'       => 'required|in:rutin,tahunan,khusus,amdal',
            'kondisi'              => 'required|in:A,B,C,D',
            'rci_score'            => 'nullable|numeric|min:0|max:100',
            'temuan'               => 'nullable|string',
            'rekomendasi'          => 'nullable|string',
            'urgensi_tindak_lanjut'=> 'nullable|in:segera,mendesak,rutin,jangka_panjang',
            'estimasi_biaya'       => 'nullable|numeric|min:0',
            'tim_inspeksi'         => 'nullable|string|max:255',
        ]);

        $fotoPaths = [];
        if ($request->hasFile('foto')) {
            foreach ($request->file('foto') as $f) {
                $fotoPaths[] = $f->store("conditions/{$asset->id}", 'public');
            }
        }

        $asset->conditions()->create([
            ...$validated,
            'foto_paths'   => $fotoPaths ?: null,
            'inspektur_id' => auth()->id(),
        ]);

        return redirect()->route('assets.show', $asset)
            ->with('success', 'Data kondisi aset berhasil disimpan.');
    }

    public function edit(Asset $asset, AssetCondition $condition)
    {
        return view('asset.conditions.form', compact('asset', 'condition'));
    }

    public function update(Request $request, Asset $asset, AssetCondition $condition)
    {
        $validated = $request->validate([
            'kondisi'    => 'required|in:A,B,C,D',
            'rci_score'  => 'nullable|numeric|min:0|max:100',
            'temuan'     => 'nullable|string',
            'rekomendasi'=> 'nullable|string',
        ]);
        $condition->update($validated);
        return redirect()->route('assets.show', $asset)
            ->with('success', 'Data kondisi berhasil diperbarui.');
    }

    public function destroy(Asset $asset, AssetCondition $condition)
    {
        $condition->delete();
        return back()->with('success', 'Data kondisi dihapus.');
    }
}
