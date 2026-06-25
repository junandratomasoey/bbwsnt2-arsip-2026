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

class ProgressController extends Controller
{
    public function index(Project $project)
    {
        $progresses = $project->progresses()->paginate(20);
        return view('project.progress', compact('project', 'progresses'));
    }

    public function store(Request $request, Project $project)
    {
        $validated = $request->validate([
            'tgl_laporan'             => 'required|date',
            'periode'                 => 'required|in:harian,mingguan,bulanan',
            'realisasi_fisik_pct'     => 'required|numeric|min:0|max:100',
            'rencana_fisik_pct'       => 'nullable|numeric|min:0|max:100',
            'realisasi_keuangan_pct'  => 'nullable|numeric|min:0|max:100',
            'nilai_termin'            => 'nullable|numeric|min:0',
            'kendala'                 => 'nullable|string',
            'rencana_tindak_lanjut'   => 'nullable|string',
            'foto'                    => 'nullable|array',
            'foto.*'                  => 'image|max:5120',
        ]);

        $fotoPaths = [];
        if ($request->hasFile('foto')) {
            foreach ($request->file('foto') as $f) {
                $fotoPaths[] = $f->store("projects/{$project->id}/progress", 'public');
            }
        }

        $progress = $project->progresses()->create([
            ...$validated,
            'foto_paths'      => $fotoPaths ?: null,
            'dilaporkan_oleh' => auth()->id(),
        ]);

        // Update realisasi di tabel projects
        $project->update([
            'realisasi_fisik_pct'    => $validated['realisasi_fisik_pct'],
            'realisasi_keuangan_pct' => $validated['realisasi_keuangan_pct'] ?? $project->realisasi_keuangan_pct,
            'tgl_update_realisasi'   => $validated['tgl_laporan'],
        ]);

        return back()->with('success', 'Progress berhasil dicatat.');
    }

    public function update(Request $request, Project $project, ProjectProgress $progress)
    {
        $validated = $request->validate([
            'realisasi_fisik_pct'    => 'required|numeric|min:0|max:100',
            'rencana_fisik_pct'      => 'nullable|numeric|min:0|max:100',
            'kendala'                => 'nullable|string',
            'rencana_tindak_lanjut'  => 'nullable|string',
        ]);
        $progress->update($validated);
        return back()->with('success', 'Progress berhasil diperbarui.');
    }

    public function destroy(Project $project, ProjectProgress $progress)
    {
        $progress->delete();
        return back()->with('success', 'Progress berhasil dihapus.');
    }
}
