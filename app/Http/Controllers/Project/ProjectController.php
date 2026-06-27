<?php

namespace App\Http\Controllers\Project;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectMilestone;
use App\Models\ProjectProgress;
use App\Models\Asset;
use App\Models\UnitKerja;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        $query = Project::with(['asset.assetType', 'unitKerja'])
            ->when($request->search, fn($q) => $q->where(function($q) use($request) {
                $q->where('nama','ilike',"%{$request->search}%")
                  ->orWhere('project_code','ilike',"%{$request->search}%")
                  ->orWhere('no_kontrak','ilike',"%{$request->search}%");
            }))
            ->when($request->lifecycle_phase, fn($q) => $q->where('lifecycle_phase', $request->lifecycle_phase))
            ->when($request->tahun,           fn($q) => $q->tahun((int)$request->tahun))
            ->when($request->unit_kerja_id,   fn($q) => $q->where('unit_kerja_id', $request->unit_kerja_id))
            ->when($request->filter === 'terlambat', fn($q) => $q->terlambat())
            ->when($request->filter === 'aktif',     fn($q) => $q->aktif())
            ->latest()
            ->paginate(20)->withQueryString();

        $unitKerjas  = UnitKerja::satker()->aktif()->orderBy('nama')->get();
        $tahunList   = range(now()->year, 2015);
        $phaseList   = ['perencanaan','pengadaan','pelaksanaan','serah_terima_1','pemeliharaan','serah_terima_2','selesai','dibatalkan'];

        $stats = [
            'aktif'     => Project::aktif()->count(),
            'terlambat' => Project::terlambat()->count(),
            'selesai'   => Project::where('lifecycle_phase','selesai')->tahun(now()->year)->count(),
        ];

        return view('project.index', compact('query','unitKerjas','tahunList','phaseList','stats'));
    }

    public function create()
    {
        $assets     = Asset::aktif()->with('assetType')->orderBy('nama')->get();
        $unitKerjas = UnitKerja::aktif()->whereIn('tipe',['satker','ppk'])->orderBy('nama')->get();
        $jenisOptions = ['pembangunan','rehabilitasi','peningkatan','operasi_pemeliharaan','studi_perencanaan','pengawasan','lainnya'];
        return view('project.form', compact('assets','unitKerjas','jenisOptions'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateProject($request);
        $validated['created_by'] = auth()->id();

        if (empty($validated['project_code'])) {
            $validated['project_code'] = Project::generateKode($validated['tahun_anggaran']);
        }

        $project = Project::create($validated);
        return redirect()->route('projects.show', $project)
            ->with('success', "Proyek <strong>{$project->nama}</strong> berhasil ditambahkan.");
    }

    public function show(Project $project)
    {
        $project->load([
            'asset.assetType','unitKerja','ppk','createdBy',
            'milestones','progresses' => fn($q) => $q->limit(10),
            'documents' => fn($q) => $q->approved()->limit(10),
        ]);
        return view('project.show', compact('project'));
    }

    public function edit(Project $project)
    {
        $assets     = Asset::aktif()->with('assetType')->orderBy('nama')->get();
        $unitKerjas = UnitKerja::aktif()->whereIn('tipe',['satker','ppk'])->orderBy('nama')->get();
        $jenisOptions = ['pembangunan','rehabilitasi','peningkatan','operasi_pemeliharaan','studi_perencanaan','pengawasan','lainnya'];
        return view('project.form', compact('project','assets','unitKerjas','jenisOptions'));
    }

    public function update(Request $request, Project $project)
    {
        $project->update($this->validateProject($request, $project->id));
        return redirect()->route('projects.show', $project)
            ->with('success', "Proyek <strong>{$project->nama}</strong> berhasil diperbarui.");
    }

    public function destroy(Project $project)
    {
        $nama = $project->nama;
        $project->delete();
        return redirect()->route('projects.index')
            ->with('success', "Proyek <strong>{$nama}</strong> berhasil dihapus.");
    }

    private function validateProject(Request $request, ?string $exceptId = null): array
    {
        return $request->validate([
            'project_code'       => [\Illuminate\Validation\Rule::unique('projects', 'project_code')->when($exceptId, fn($r) => $r->ignore($exceptId))],
            'nama'               => 'required|string|max:255',
            'deskripsi'          => 'nullable|string',
            'jenis'              => 'required|in:pembangunan,rehabilitasi,peningkatan,operasi_pemeliharaan,studi_perencanaan,pengawasan,lainnya',
            'asset_id'           => 'nullable|exists:assets,id',
            'unit_kerja_id'      => 'required|exists:unit_kerja,id',
            'ppk_id'             => 'nullable|exists:unit_kerja,id',
            'lifecycle_phase'    => 'required|in:perencanaan,pengadaan,pelaksanaan,serah_terima_1,pemeliharaan,serah_terima_2,selesai,dibatalkan',
            'no_kontrak'         => ['nullable', 'string', 'max:100', \Illuminate\Validation\Rule::unique('projects', 'no_kontrak')->when($exceptId, fn($r) => $r->ignore($exceptId))],
            'kontraktor'         => 'nullable|string|max:255',
            'konsultan_pengawas' => 'nullable|string|max:255',
            'tahun_anggaran'     => 'required|integer|min:2000|max:' . (now()->year + 1),
            'sumber_dana'        => 'nullable|string|max:100',
            'nilai_pagu'         => 'nullable|numeric|min:0',
            'nilai_kontrak'      => 'nullable|numeric|min:0',
            'tgl_mulai_rencana'  => 'nullable|date',
            'tgl_selesai_rencana'=> 'nullable|date|after_or_equal:tgl_mulai_rencana',
            'realisasi_fisik_pct'    => 'nullable|numeric|min:0|max:100',
            'realisasi_keuangan_pct' => 'nullable|numeric|min:0|max:100',
            'is_multiyears'      => 'boolean',
        ]);
    }
}
