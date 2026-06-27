<?php

namespace App\Http\Controllers\Project;

use App\Http\Controllers\Controller;
use App\Models\OpRecord;
use App\Models\OpSchedule;
use App\Models\Asset;
use App\Models\UnitKerja;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OpRecordController extends Controller
{
    public function index(Request $request)
    {
        $tahun = $request->integer('tahun', now()->year);
        $bulan = $request->integer('bulan');

        $query = OpRecord::with(['asset.assetType', 'unitKerja', 'petugas'])
            ->tahun($tahun)
            ->when($bulan,                  fn($q) => $q->bulan($bulan))
            ->when($request->status,        fn($q) => $q->where('status', $request->status))
            ->when($request->jenis_op,      fn($q) => $q->where('jenis_op', $request->jenis_op))
            ->when($request->unit_kerja_id, fn($q) => $q->where('unit_kerja_id', $request->unit_kerja_id))
            ->when($request->asset_id,      fn($q) => $q->where('asset_id', $request->asset_id))
            ->orderBy('periode_tahun', 'desc')->orderBy('periode_bulan', 'desc')
            ->paginate(25)->withQueryString();

        $ringkasan = OpRecord::tahun($tahun)
            ->selectRaw('status, COUNT(*) as jumlah, AVG(realisasi_pct) as avg_realisasi')
            ->groupBy('status')->pluck('jumlah', 'status');

        $unitKerjas = UnitKerja::satker()->aktif()->orderBy('nama')->get();
        $tahunList  = range(now()->year, 2015);

        return view('op.records.index', compact(
            'query', 'ringkasan', 'unitKerjas', 'tahun', 'bulan', 'tahunList'
        ));
    }

    public function create(Request $request)
    {
        $assets     = Asset::aktif()->with('assetType')->orderBy('nama')->get();
        $unitKerjas = UnitKerja::satker()->aktif()->orderBy('nama')->get();
        $assetId    = $request->asset_id;

        return view('op.records.create', compact('assets', 'unitKerjas', 'assetId'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'asset_id'           => 'required|exists:assets,id',
            'unit_kerja_id'      => 'required|exists:unit_kerja,id',
            'op_schedule_id'     => 'nullable|exists:op_schedules,id',
            'periode_tahun'      => 'required|integer|min:2000|max:' . (now()->year + 1),
            'periode_bulan'      => 'required|integer|min:1|max:12',
            'jenis_op'           => 'required|in:rutin,berkala,darurat,rehabilitasi_minor',
            'status'             => 'required|in:belum,berjalan,selesai,tidak_terlaksana',
            'tgl_pelaksanaan'    => 'nullable|date',
            'realisasi_pct'      => 'required|numeric|min:0|max:100',
            'kegiatan_text'      => 'nullable|string',  // ← textarea satu baris per kegiatan
            'anggaran'           => 'nullable|numeric|min:0',
            'realisasi_anggaran' => 'nullable|numeric|min:0',
            'data_teknis'        => 'nullable|array',
            'kendala'            => 'nullable|string',
            'catatan'            => 'nullable|string',
            'tim_op'             => 'nullable|string|max:255',
            'foto'               => 'nullable|array',
            'foto.*'             => 'image|max:5120',
        ]);

        // Parse kegiatan_text (textarea) menjadi array
        $kegiatan = [];
        if (!empty($validated['kegiatan_text'])) {
            $kegiatan = array_values(array_filter(
                array_map('trim', explode("\n", $validated['kegiatan_text']))
            ));
        }
        unset($validated['kegiatan_text']);

        // Upload foto
        $fotoPaths = [];
        if ($request->hasFile('foto')) {
            foreach ($request->file('foto') as $f) {
                $fotoPaths[] = $f->store("op/{$validated['asset_id']}", 'public');
            }
        }

        $record = OpRecord::create([
            ...$validated,
            'kegiatan_dilakukan' => !empty($kegiatan) ? $kegiatan : null,
            'foto_paths'         => !empty($fotoPaths) ? $fotoPaths : null,
            'petugas_id'         => auth()->id(),
        ]);

        return redirect()->route('op.records.show', $record)
            ->with('success', 'Rekaman OP berhasil disimpan.');
    }

    public function show(OpRecord $record)
    {
        $record->load(['asset.assetType', 'unitKerja', 'petugas', 'schedule']);
        return view('op.records.show', compact('record'));
    }

    public function edit(OpRecord $record)
    {
        $assets     = Asset::aktif()->orderBy('nama')->get();
        $unitKerjas = UnitKerja::satker()->aktif()->orderBy('nama')->get();
        return view('op.records.edit', compact('record', 'assets', 'unitKerjas'));
    }

    public function update(Request $request, OpRecord $record)
    {
        $validated = $request->validate([
            'status'             => 'required|in:belum,berjalan,selesai,tidak_terlaksana',
            'tgl_pelaksanaan'    => 'nullable|date',
            'realisasi_pct'      => 'required|numeric|min:0|max:100',
            'kegiatan_text'      => 'nullable|string',
            'anggaran'           => 'nullable|numeric|min:0',
            'realisasi_anggaran' => 'nullable|numeric|min:0',
            'data_teknis'        => 'nullable|array',
            'kendala'            => 'nullable|string',
            'catatan'            => 'nullable|string',
        ]);

        // Parse kegiatan
        $kegiatan = [];
        if (!empty($validated['kegiatan_text'])) {
            $kegiatan = array_values(array_filter(
                array_map('trim', explode("\n", $validated['kegiatan_text']))
            ));
        }
        unset($validated['kegiatan_text']);

        $record->update([
            ...$validated,
            'kegiatan_dilakukan' => !empty($kegiatan) ? $kegiatan : $record->kegiatan_dilakukan,
        ]);

        return redirect()->route('op.records.show', $record)
            ->with('success', 'Rekaman OP berhasil diperbarui.');
    }

    public function destroy(OpRecord $record)
    {
        $record->delete();
        return redirect()->route('op.records.index')
            ->with('success', 'Rekaman OP berhasil dihapus.');
    }
}
