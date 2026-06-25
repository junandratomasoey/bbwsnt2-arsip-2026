<?php

namespace App\Http\Controllers\Project;

use App\Http\Controllers\Controller;
use App\Models\OpRecord;
use App\Models\OpSchedule;
use App\Models\Asset;
use App\Models\UnitKerja;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OpScheduleController extends Controller
{
    public function index(Request $request)
    {
        $tahun = $request->integer('tahun', now()->year);
        $query = OpSchedule::with(['asset.assetType','unitKerja'])
            ->where('tahun', $tahun)
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->unit_kerja_id, fn($q) => $q->where('unit_kerja_id', $request->unit_kerja_id))
            ->orderBy('created_at','desc')
            ->paginate(20)->withQueryString();

        $unitKerjas = UnitKerja::satker()->aktif()->orderBy('nama')->get();
        $tahunList  = range(now()->year + 1, 2015);
        return view('op.schedules.index', compact('query','tahun','tahunList','unitKerjas'));
    }

    public function create()
    {
        $assets     = Asset::operating()->aktif()->with('assetType')->orderBy('nama')->get();
        $unitKerjas = UnitKerja::satker()->aktif()->orderBy('nama')->get();
        return view('op.schedules.form', compact('assets','unitKerjas'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'asset_id'             => 'required|exists:assets,id',
            'unit_kerja_id'        => 'required|exists:unit_kerja,id',
            'tahun'                => 'required|integer|min:2000|max:' . (now()->year + 2),
            'anggaran_op_rutin'    => 'nullable|numeric|min:0',
            'anggaran_op_berkala'  => 'nullable|numeric|min:0',
            'kode_dipa'            => 'nullable|string|max:50',
            'rencana_kegiatan'     => 'nullable|array',
        ]);

        // Cek duplikat
        $exists = OpSchedule::where('asset_id', $validated['asset_id'])
            ->where('tahun', $validated['tahun'])->exists();
        if ($exists) {
            return back()->withInput()->with('error', 'Jadwal OP untuk aset dan tahun ini sudah ada.');
        }

        $schedule = OpSchedule::create([
            ...$validated,
            'status'      => 'draft',
            'dibuat_oleh' => auth()->id(),
        ]);

        return redirect()->route('op.schedules.show', $schedule)
            ->with('success', 'Jadwal OP berhasil dibuat.');
    }

    public function show(OpSchedule $schedule)
    {
        $schedule->load(['asset.assetType','unitKerja','opRecords']);
        return view('op.schedules.show', compact('schedule'));
    }

    public function edit(OpSchedule $schedule)
    {
        $assets     = Asset::operating()->aktif()->with('assetType')->orderBy('nama')->get();
        $unitKerjas = UnitKerja::satker()->aktif()->orderBy('nama')->get();
        return view('op.schedules.form', compact('schedule','assets','unitKerjas'));
    }

    public function update(Request $request, OpSchedule $schedule)
    {
        $validated = $request->validate([
            'anggaran_op_rutin'   => 'nullable|numeric|min:0',
            'anggaran_op_berkala' => 'nullable|numeric|min:0',
            'rencana_kegiatan'    => 'nullable|array',
        ]);
        $schedule->update($validated);
        return redirect()->route('op.schedules.show', $schedule)
            ->with('success', 'Jadwal OP berhasil diperbarui.');
    }

    public function approve(OpSchedule $schedule)
    {
        $schedule->update(['status' => 'approved']);
        return back()->with('success', 'Jadwal OP berhasil disetujui.');
    }
}
