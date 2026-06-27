{{-- resources/views/project/show.blade.php --}}
@extends('layouts.app')
@section('title', $project->nama)

@section('breadcrumb')
    <a href="{{ route('projects.index') }}" class="text-slate-500 hover:text-slate-700 text-sm">Proyek</a>
    <i class="ti ti-chevron-right text-slate-300 text-xs"></i>
    <span class="text-slate-800 font-medium text-sm truncate">{{ $project->nama }}</span>
@endsection

@section('content')
<div class="flex flex-col lg:flex-row gap-6">

    {{-- Kolom utama --}}
    <div class="flex-1 space-y-5">

        {{-- Header --}}
        <div class="bg-white border border-slate-200 rounded-xl p-5">
            <div class="flex flex-wrap gap-2 mb-2">
                <span class="font-mono text-xs text-slate-400 bg-slate-100 px-2 py-0.5 rounded">{{ $project->project_code }}</span>
                <span class="text-xs px-2 py-0.5 rounded font-medium {{ $project->badgeHealth() }}">{{ ucfirst($project->healthStatus()) }}</span>
                <span class="text-xs px-2 py-0.5 rounded bg-sky-100 text-sky-700">{{ $project->labelPhase() }}</span>
            </div>
            <h1 class="text-xl font-semibold text-slate-800">{{ $project->nama }}</h1>
            <p class="text-sm text-slate-500 mt-1">{{ ucfirst(str_replace('_',' ',$project->jenis)) }} · {{ $project->unitKerja?->singkatan }} · TA {{ $project->tahun_anggaran }}</p>

            {{-- Progress bar --}}
            <div class="mt-4 space-y-2">
                <div class="flex justify-between text-xs text-slate-500">
                    <span>Realisasi Fisik</span>
                    <span class="font-semibold text-slate-700">{{ $project->realisasi_fisik_pct }}%</span>
                </div>
                <div class="h-3 bg-slate-100 rounded-full overflow-hidden">
                    <div class="h-full bg-sky-500 rounded-full transition-all"
                         style="width: {{ min($project->realisasi_fisik_pct, 100) }}%"></div>
                </div>
                @if($project->realisasi_keuangan_pct)
                <div class="flex justify-between text-xs text-slate-500">
                    <span>Realisasi Keuangan</span>
                    <span class="font-semibold text-slate-700">{{ $project->realisasi_keuangan_pct }}%</span>
                </div>
                <div class="h-2 bg-slate-100 rounded-full overflow-hidden">
                    <div class="h-full bg-emerald-500 rounded-full"
                         style="width: {{ min($project->realisasi_keuangan_pct, 100) }}%"></div>
                </div>
                @endif
            </div>

            <div class="flex flex-wrap gap-2 mt-4">
                @can('project.edit')
                <a href="{{ route('projects.edit', $project) }}"
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white border border-slate-200 text-sm text-slate-700 rounded-lg hover:bg-slate-50">
                    <i class="ti ti-edit text-slate-400"></i> Edit
                </a>
                @endcan
                @can('project_progress.create')
                <a href="{{ route('projects.progress.index', $project) }}"
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-sky-600 text-white text-sm rounded-lg hover:bg-sky-700">
                    <i class="ti ti-chart-line"></i> Input Progress
                </a>
                @endcan
            </div>
        </div>

        {{-- Info kontrak --}}
        <div class="bg-white border border-slate-200 rounded-xl p-5">
            <h3 class="text-sm font-semibold text-slate-700 mb-4 pb-2 border-b border-slate-100">Informasi Kontrak</h3>
            <div class="grid grid-cols-2 gap-4">
                <div><p class="text-xs text-slate-400 mb-0.5">No. Kontrak</p>
                    <p class="text-sm font-mono text-slate-700">{{ $project->no_kontrak ?? '—' }}</p>
                </div>
                <div><p class="text-xs text-slate-400 mb-0.5">Kontraktor</p>
                    <p class="text-sm text-slate-700">{{ $project->kontraktor ?? '—' }}</p>
                </div>
                <div><p class="text-xs text-slate-400 mb-0.5">Nilai Kontrak</p>
                    <p class="text-sm font-semibold text-slate-700">{{ $project->nilaiKontrakFormatted() }}</p>
                </div>
                <div><p class="text-xs text-slate-400 mb-0.5">Sumber Dana</p>
                    <p class="text-sm text-slate-700">{{ $project->sumber_dana ?? '—' }}</p>
                </div>
                <div><p class="text-xs text-slate-400 mb-0.5">Mulai Rencana</p>
                    <p class="text-sm text-slate-700">{{ $project->tgl_mulai_rencana?->format('d M Y') ?? '—' }}</p>
                </div>
                <div><p class="text-xs text-slate-400 mb-0.5">Selesai Rencana</p>
                    <p class="text-sm {{ $project->tgl_selesai_rencana?->isPast() && !in_array($project->lifecycle_phase,['selesai','dibatalkan']) ? 'text-red-600 font-semibold' : 'text-slate-700' }}">
                        {{ $project->tgl_selesai_rencana?->format('d M Y') ?? '—' }}
                    </p>
                </div>
                @if($project->asset)
                <div class="col-span-2"><p class="text-xs text-slate-400 mb-0.5">Aset Terkait</p>
                    <a href="{{ route('assets.show', $project->asset) }}" class="text-sm text-sky-600 hover:underline">
                        {{ $project->asset->nama }}
                    </a>
                </div>
                @endif
            </div>
        </div>

        {{-- Milestones --}}
        <div class="bg-white border border-slate-200 rounded-xl overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-slate-700">Milestone</h3>
            </div>
            @if($project->milestones->isEmpty())
            <p class="px-5 py-6 text-center text-sm text-slate-400">Belum ada milestone</p>
            @else
            <div class="divide-y divide-slate-100">
                @foreach($project->milestones as $ms)
                <div class="px-5 py-3 flex items-center justify-between gap-4">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-6 h-6 rounded-full flex items-center justify-center flex-shrink-0
                                    {{ $ms->isSelesai() ? 'bg-emerald-100' : ($ms->isTerlambat() ? 'bg-red-100' : 'bg-slate-100') }}">
                            <i class="ti {{ $ms->isSelesai() ? 'ti-check text-emerald-600' : ($ms->isTerlambat() ? 'ti-x text-red-500' : 'ti-clock text-slate-400') }} text-xs"></i>
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-slate-700 truncate">{{ $ms->nama }}</p>
                            <p class="text-xs text-slate-400">Target: {{ $ms->tgl_rencana?->format('d M Y') }}</p>
                        </div>
                    </div>
                    <span class="flex-shrink-0 text-xs px-2 py-0.5 rounded {{ $ms->badgeStatus() }}">
                        {{ ucfirst(str_replace('_',' ',$ms->status)) }}
                    </span>
                </div>
                @endforeach
            </div>
            @endif
        </div>

        {{-- Progress terbaru --}}
        @if($project->progresses->isNotEmpty())
        <div class="bg-white border border-slate-200 rounded-xl overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-slate-700">Progress Terbaru</h3>
                <a href="{{ route('projects.progress.index', $project) }}" class="text-xs text-sky-600 hover:underline">Semua →</a>
            </div>
            <div class="divide-y divide-slate-100">
                @foreach($project->progresses->take(5) as $prog)
                <div class="px-5 py-3 flex items-center justify-between gap-4">
                    <div>
                        <p class="text-sm text-slate-700">{{ $prog->tgl_laporan?->format('d M Y') }}</p>
                        <p class="text-xs text-slate-400">{{ ucfirst($prog->periode) }} · Laporan oleh {{ $prog->dilaporkanOleh?->name }}</p>
                    </div>
                    <div class="text-right flex-shrink-0">
                        <p class="text-sm font-semibold text-slate-800">{{ $prog->realisasi_fisik_pct }}%</p>
                        @php $dev = $prog->deviasi(); @endphp
                        <p class="text-xs {{ $dev >= 0 ? 'text-emerald-600' : 'text-red-600' }}">
                            {{ $dev >= 0 ? '+' : '' }}{{ $dev }}%
                        </p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    {{-- Sidebar --}}
    <div class="lg:w-72 space-y-5 flex-shrink-0">
        {{-- Dokumen --}}
        <div class="bg-white border border-slate-200 rounded-xl overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-slate-700">Dokumen ({{ $project->documents->count() }})</h3>
                @can('document.create')
                <a href="{{ route('documents.create', ['entity_type'=>'App\Models\Project','entity_id'=>$project->id]) }}"
                   class="text-xs text-sky-600 hover:underline"><i class="ti ti-plus"></i> Upload</a>
                @endcan
            </div>
            @if($project->documents->isEmpty())
            <p class="px-5 py-5 text-center text-xs text-slate-400">Belum ada dokumen</p>
            @else
            <div class="divide-y divide-slate-100">
                @foreach($project->documents->take(8) as $doc)
                <div class="px-5 py-2.5">
                    <a href="{{ route('documents.show', $doc) }}"
                       class="text-xs font-medium text-slate-700 hover:text-sky-600 truncate block">{{ $doc->judul }}</a>
                    <p class="text-xs text-slate-400">{{ $doc->documentType?->nama }}</p>
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
