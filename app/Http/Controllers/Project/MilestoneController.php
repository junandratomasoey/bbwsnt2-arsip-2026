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

class MilestoneController extends Controller
{
    public function index(Project $project)
    {
        $project->load('milestones');
        return view('project.milestones', compact('project'));
    }

    public function store(Request $request, Project $project)
    {
        $validated = $request->validate([
            'nama'        => 'required|string|max:255',
            'tgl_rencana' => 'required|date',
            'bobot_pct'   => 'required|numeric|min:0|max:100',
            'urutan'      => 'nullable|integer|min:0',
            'deskripsi'   => 'nullable|string',
        ]);
        $project->milestones()->create($validated + ['status' => 'belum_mulai']);
        return back()->with('success', 'Milestone berhasil ditambahkan.');
    }

    public function update(Request $request, Project $project, ProjectMilestone $milestone)
    {
        $validated = $request->validate([
            'nama'        => 'required|string|max:255',
            'tgl_rencana' => 'required|date',
            'tgl_aktual'  => 'nullable|date',
            'bobot_pct'   => 'required|numeric|min:0|max:100',
            'status'      => 'required|in:belum_mulai,on_track,terlambat,selesai,dibatalkan',
            'catatan'     => 'nullable|string',
        ]);
        $milestone->update($validated);
        return back()->with('success', 'Milestone berhasil diperbarui.');
    }

    public function destroy(Project $project, ProjectMilestone $milestone)
    {
        $milestone->delete();
        return back()->with('success', 'Milestone berhasil dihapus.');
    }
}
