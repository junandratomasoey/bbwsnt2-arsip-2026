@extends('layouts.app')
@section('title', 'Proyek')

@section('breadcrumb')
    <i class="ti ti-timeline text-slate-400"></i>
    <span class="text-slate-800 font-medium text-sm">Proyek</span>
@endsection

@section('content')
<x-page-header title="Proyek" desc="Monitoring pekerjaan infrastruktur sumber daya air" icon="ti-timeline">
    @can('project.create')
    <a href="{{ route('projects.create') }}"
       class="inline-flex items-center gap-2 px-4 py-2 bg-sky-600 text-white text-sm font-medium rounded-xl hover:bg-sky-700">
        <i class="ti ti-plus"></i> Tambah Proyek
    </a>
    @endcan
</x-page-header>

{{-- Stats --}}
<div class="grid grid-cols-3 gap-3 mb-5">
    <x-stat-card label="Proyek Aktif"    value="{{ $stats['aktif'] }}"     icon="ti-timeline"        color="sky" />
    <x-stat-card label="Terlambat"       value="{{ $stats['terlambat'] }}" icon="ti-clock-exclamation" color="red" />
    <x-stat-card label="Selesai {{ now()->year }}" value="{{ $stats['selesai'] }}" icon="ti-circle-check" color="green" />
</div>

{{-- Filter --}}
<div class="bg-white border border-slate-200 rounded-xl p-4 mb-4">
    <form method="GET" class="flex flex-wrap gap-2">
        <input type="text" name="search" value="{{ request('search') }}"
               placeholder="Cari nama, kode, kontrak..."
               class="flex-1 min-w-48 border border-slate-200 rounded-lg px-3 py-2 text-sm
                      focus:outline-none focus:ring-2 focus:ring-sky-500">
        <select name="tahun" class="border border-slate-200 rounded-lg px-3 py-2 text-sm
                                    focus:outline-none focus:ring-2 focus:ring-sky-500">
            <option value="">Semua tahun</option>
            @foreach($tahunList as $t)
            <option value="{{ $t }}" @selected(request('tahun') == $t)>{{ $t }}</option>
            @endforeach
        </select>
        <select name="lifecycle_phase" class="border border-slate-200 rounded-lg px-3 py-2 text-sm
                                              focus:outline-none focus:ring-2 focus:ring-sky-500">
            <option value="">Semua fase</option>
            @foreach(['perencanaan'=>'Perencanaan','pelaksanaan'=>'Pelaksanaan','selesai'=>'Selesai','dibatalkan'=>'Dibatalkan'] as $v=>$l)
            <option value="{{ $v }}" @selected(request('lifecycle_phase') === $v)>{{ $l }}</option>
            @endforeach
        </select>
        <select name="unit_kerja_id" class="border border-slate-200 rounded-lg px-3 py-2 text-sm
                                            focus:outline-none focus:ring-2 focus:ring-sky-500">
            <option value="">Semua satker</option>
            @foreach($unitKerjas as $uk)
            <option value="{{ $uk->id }}" @selected(request('unit_kerja_id') === $uk->id)>{{ $uk->singkatan }}</option>
            @endforeach
        </select>
        @if(request('filter') !== 'terlambat')
        <a href="{{ route('projects.index', ['filter'=>'terlambat']) }}"
           class="px-3 py-2 text-sm border border-red-200 text-red-600 rounded-lg hover:bg-red-50">
            <i class="ti ti-clock-exclamation"></i> Terlambat
        </a>
        @endif
        <button type="submit" class="px-4 py-2 bg-slate-800 text-white text-sm rounded-lg hover:bg-slate-700">
            <i class="ti ti-search"></i>
        </button>
        @if(request()->hasAny(['search','tahun','lifecycle_phase','unit_kerja_id','filter']))
        <a href="{{ route('projects.index') }}" class="px-4 py-2 text-sm text-slate-500 border border-slate-200 rounded-lg hover:bg-slate-50">Reset</a>
        @endif
    </form>
</div>

{{-- Tabel --}}
<div class="bg-white border border-slate-200 rounded-xl overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 border-b border-slate-200 text-xs font-medium text-slate-500 uppercase tracking-wide">
            <tr>
                <th class="px-5 py-3 text-left">Proyek</th>
                <th class="px-5 py-3 text-left hidden md:table-cell">Satker</th>
                <th class="px-5 py-3 text-left hidden lg:table-cell">Kontrak</th>
                <th class="px-5 py-3 text-center">Fisik</th>
                <th class="px-5 py-3 text-center">Status</th>
                <th class="px-5 py-3 text-center">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse($query as $project)
            <tr class="hover:bg-slate-50 transition-colors">
                <td class="px-5 py-3">
                    <a href="{{ route('projects.show', $project) }}"
                       class="font-medium text-slate-800 hover:text-sky-600 block">{{ $project->nama }}</a>
                    <p class="text-xs text-slate-400 mt-0.5 font-mono">{{ $project->project_code }}</p>
                </td>
                <td class="px-5 py-3 hidden md:table-cell text-xs text-slate-600">
                    {{ $project->unitKerja?->singkatan }}<br>
                    <span class="text-slate-400">{{ $project->tahun_anggaran }}</span>
                </td>
                <td class="px-5 py-3 hidden lg:table-cell">
                    <p class="text-xs text-slate-600">{{ $project->nilaiKontrakFormatted() }}</p>
                    @if($project->tgl_selesai_rencana)
                    <p class="text-xs text-slate-400">Selesai: {{ $project->tgl_selesai_rencana->format('d M Y') }}</p>
                    @endif
                </td>
                <td class="px-5 py-3 text-center">
                    <div class="flex flex-col items-center">
                        <span class="text-sm font-semibold text-slate-800">{{ $project->realisasi_fisik_pct }}%</span>
                        <div class="w-16 h-1.5 bg-slate-100 rounded-full mt-1">
                            <div class="h-full bg-sky-500 rounded-full"
                                 style="width: {{ min($project->realisasi_fisik_pct, 100) }}%"></div>
                        </div>
                    </div>
                </td>
                <td class="px-5 py-3 text-center">
                    <span class="inline-flex px-2 py-0.5 rounded-md text-xs font-medium {{ $project->badgeHealth() }}">
                        {{ ucfirst($project->healthStatus()) }}
                    </span>
                </td>
                <td class="px-5 py-3">
                    <div class="flex items-center justify-center gap-1">
                        <a href="{{ route('projects.show', $project) }}"
                           class="p-1.5 text-slate-400 hover:text-sky-600 hover:bg-sky-50 rounded-lg">
                            <i class="ti ti-eye text-sm"></i>
                        </a>
                        @can('project.edit')
                        <a href="{{ route('projects.edit', $project) }}"
                           class="p-1.5 text-slate-400 hover:text-amber-600 hover:bg-amber-50 rounded-lg">
                            <i class="ti ti-edit text-sm"></i>
                        </a>
                        @endcan
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="6" class="px-5 py-10 text-center text-slate-400">Tidak ada proyek ditemukan.</td></tr>
            @endforelse
        </tbody>
    </table>
    @if($query->hasPages())
    <div class="px-5 py-4 border-t border-slate-100">{{ $query->links() }}</div>
    @endif
</div>
@endsection
