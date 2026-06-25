<?php

namespace App\Http\Controllers\Asset;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\AssetType;
use App\Models\UnitKerja;
use App\Models\Notification;
use Illuminate\Http\Request;

class AssetController extends Controller
{
    public function index(Request $request)
    {
        $query = Asset::with(['assetType', 'unitKerja'])
            ->aktif()
            ->when($request->search, fn($q) => $q->search($request->search))
            ->when($request->asset_type_id, fn($q) => $q->where('asset_type_id', $request->asset_type_id))
            ->when($request->unit_kerja_id, fn($q) => $q->where('unit_kerja_id', $request->unit_kerja_id))
            ->when($request->lifecycle_status, fn($q) => $q->where('lifecycle_status', $request->lifecycle_status))
            ->when($request->kondisi, fn($q) => $q->where('kondisi_terakhir', $request->kondisi))
            ->when($request->kabupaten, fn($q) => $q->where('kabupaten', 'ilike', "%{$request->kabupaten}%"))
            ->orderBy('asset_code')
            ->paginate(20)
            ->withQueryString();

        $assetTypes  = AssetType::aktif()->orderBy('urutan')->get();
        $unitKerjas  = UnitKerja::aktif()->satker()->orderBy('nama')->get();
        $kabupatenList = Asset::select('kabupaten')->distinct()->whereNotNull('kabupaten')->orderBy('kabupaten')->pluck('kabupaten');

        $stats = [
            'total'         => Asset::aktif()->count(),
            'kondisi_buruk' => Asset::kondisiBuruk()->aktif()->count(),
            'belum_dinilai' => Asset::aktif()->whereNull('kondisi_terakhir')->count(),
            'operating'     => Asset::operating()->aktif()->count(),
        ];

        return view('asset.index', compact('query', 'assetTypes', 'unitKerjas', 'kabupatenList', 'stats'));
    }

    public function create()
    {
        $assetTypes = AssetType::aktif()->orderBy('urutan')->get();
        $unitKerjas = UnitKerja::aktif()->whereIn('tipe', ['satker', 'ppk'])->orderBy('tipe')->orderBy('nama')->get();

        return view('asset.create', compact('assetTypes', 'unitKerjas'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateAsset($request);

        // Auto-generate kode aset jika tidak diisi
        if (empty($validated['asset_code'])) {
            $type = AssetType::find($validated['asset_type_id']);
            $validated['asset_code'] = Asset::generateKode($type?->kategori ?? 'umm');
        }

        $validated['created_by'] = auth()->id();
        $asset = Asset::create($validated);

        // Upload foto utama
        if ($request->hasFile('foto_utama')) {
            $path = $request->file('foto_utama')->store("assets/{$asset->id}/foto", 'public');
            $asset->update(['foto_utama_path' => $path]);
        }

        // Notifikasi ke admin satker
        $adminIds = \App\Models\User::role('admin_satker')
            ->where('unit_kerja_id', $asset->unit_kerja_id)
            ->pluck('id')->toArray();

        if (!empty($adminIds)) {
            Notification::kirim($adminIds, 'asset.created',
                'Aset Baru Ditambahkan',
                "Aset [{$asset->asset_code}] {$asset->nama} telah ditambahkan.",
                ['entity_type' => 'Asset', 'entity_id' => $asset->id,
                 'action_url'  => route('assets.show', $asset), 'icon' => 'ti-building-bridge']
            );
        }

        return redirect()->route('assets.show', $asset)
            ->with('success', "Aset <strong>{$asset->asset_code} — {$asset->nama}</strong> berhasil ditambahkan.");
    }

    public function show(Asset $asset)
    {
        $asset->load([
            'assetType', 'unitKerja', 'createdBy',
            'geometriUtama',
            'kondisiTerbaru.inspektur',
            'conditions' => fn($q) => $q->limit(5),
            'components',
            'projects' => fn($q) => $q->aktif()->limit(5),
            'opRecords' => fn($q) => $q->tahun(now()->year)->orderBy('periode_bulan'),
            'documents' => fn($q) => $q->approved()->limit(10),
        ]);

        $dokumenBelumAda = $asset->dokumenWajibBelumAda('after');
        $kelengkapanPct  = $asset->persentaseKelengkapanDokumen('after');

        return view('asset.show', compact('asset', 'dokumenBelumAda', 'kelengkapanPct'));
    }

    public function edit(Asset $asset)
    {
        $assetTypes = AssetType::aktif()->orderBy('urutan')->get();
        $unitKerjas = UnitKerja::aktif()->whereIn('tipe', ['satker', 'ppk'])->orderBy('nama')->get();

        return view('asset.create', compact('asset', 'assetTypes', 'unitKerjas'));
    }

    public function update(Request $request, Asset $asset)
    {
        $validated = $this->validateAsset($request, $asset->id);
        $validated['updated_by'] = auth()->id();

        if ($request->hasFile('foto_utama')) {
            $path = $request->file('foto_utama')->store("assets/{$asset->id}/foto", 'public');
            $validated['foto_utama_path'] = $path;
        }

        $asset->update($validated);

        return redirect()->route('assets.show', $asset)
            ->with('success', "Aset <strong>{$asset->nama}</strong> berhasil diperbarui.");
    }

    public function destroy(Asset $asset)
    {
        if ($asset->projects()->aktif()->exists()) {
            return back()->with('error', 'Tidak bisa menghapus: aset ini memiliki proyek aktif.');
        }

        $nama = $asset->nama;
        $asset->delete();

        return redirect()->route('assets.index')
            ->with('success', "Aset <strong>{$nama}</strong> berhasil dihapus.");
    }

    private function validateAsset(Request $request, ?string $exceptId = null): array
    {
        return $request->validate([
            'asset_code'         => "nullable|string|max:30|unique:assets,asset_code,{$exceptId}",
            'nama'               => 'required|string|max:255',
            'deskripsi'          => 'nullable|string',
            'asset_type_id'      => 'required|exists:asset_types,id',
            'unit_kerja_id'      => 'required|exists:unit_kerja,id',
            'provinsi'           => 'nullable|string|max:100',
            'kabupaten'          => 'nullable|string|max:100',
            'kecamatan'          => 'nullable|string|max:100',
            'desa'               => 'nullable|string|max:100',
            'das'                => 'nullable|string|max:100',
            'wilayah_sungai'     => 'nullable|string|max:100',
            'lifecycle_status'   => 'required|in:planning,construction,commissioning,operating,rehabilitating,decommissioned',
            'tahun_bangun'       => 'nullable|integer|min:1900|max:' . now()->year,
            'tahun_desain'       => 'nullable|integer|min:1900|max:' . now()->year,
            'umur_rencana_tahun' => 'nullable|integer|min:1|max:200',
            'atribut_teknis'     => 'nullable|array',
            'nilai_perolehan'    => 'nullable|numeric|min:0',
            'nilai_buku'         => 'nullable|numeric|min:0',
            'tahun_perolehan'    => 'nullable|integer|min:1900|max:' . now()->year,
            'foto_utama'         => 'nullable|image|max:5120',
        ]);
    }
}
