@extends('layouts.app')
@section('title', $asset->nama)

@section('breadcrumb')
    <a href="{{ route('assets.index') }}" class="text-slate-500 hover:text-slate-700 text-sm">Aset</a>
    <i class="ti ti-chevron-right text-slate-300 text-xs"></i>
    <span class="text-slate-800 font-medium text-sm truncate">{{ $asset->nama }}</span>
@endsection

@section('content')
<div class="flex flex-col lg:flex-row gap-6">

    {{-- Kolom kiri: info utama --}}
    <div class="flex-1 space-y-5">

        {{-- Header aset --}}
        <div class="bg-white border border-slate-200 rounded-xl overflow-hidden">
            <div class="p-5 flex flex-col sm:flex-row sm:items-start gap-4">
                {{-- Foto --}}
                <div class="w-full sm:w-40 h-32 bg-slate-100 rounded-xl overflow-hidden flex-shrink-0">
                    @if($asset->foto_utama_path)
                    <img src="{{ Storage::url($asset->foto_utama_path) }}"
                         class="w-full h-full object-cover" alt="{{ $asset->nama }}">
                    @else
                    <div class="w-full h-full flex items-center justify-center">
                        <i class="ti ti-building-bridge text-4xl text-slate-300"></i>
                    </div>
                    @endif
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex flex-wrap items-start gap-2 mb-2">
                        <span class="font-mono text-xs text-slate-400 bg-slate-100 px-2 py-0.5 rounded">
                            {{ $asset->asset_code }}
                        </span>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium
                                     {{ $asset->badgeKondisi() }}">
                            Kondisi {{ $asset->kondisi_terakhir ?? 'Belum Dinilai' }}
                        </span>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium
                                     bg-sky-100 text-sky-700">
                            {{ $asset->labelLifecycle() }}
                        </span>
                    </div>
                    <h1 class="text-xl font-semibold text-slate-800">{{ $asset->nama }}</h1>
                    <p class="text-sm text-slate-500 mt-1">
                        {{ $asset->assetType?->nama }} ·
                        {{ $asset->unitKerja?->singkatan }} ·
                        {{ $asset->kabupaten }}, {{ $asset->kecamatan }}
                    </p>
                    @if($asset->deskripsi)
                    <p class="text-sm text-slate-600 mt-2">{{ $asset->deskripsi }}</p>
                    @endif
                </div>
            </div>

            {{-- Aksi --}}
            <div class="px-5 py-3 bg-slate-50 border-t border-slate-100 flex flex-wrap gap-2">
                @can('asset.edit')
                <a href="{{ route('assets.edit', $asset) }}"
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white border border-slate-200
                          text-sm text-slate-700 rounded-lg hover:bg-slate-50 transition-colors">
                    <i class="ti ti-edit text-slate-400"></i> Edit
                </a>
                @endcan
                @can('asset_condition.create')
                <a href="{{ route('assets.conditions.create', $asset) }}"
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white border border-slate-200
                          text-sm text-slate-700 rounded-lg hover:bg-slate-50 transition-colors">
                    <i class="ti ti-clipboard-check text-slate-400"></i> Input Kondisi
                </a>
                @endcan
                @can('document.create')
                <a href="{{ route('documents.create', ['entity_type' => 'App\Models\Asset', 'entity_id' => $asset->id]) }}"
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white border border-slate-200
                          text-sm text-slate-700 rounded-lg hover:bg-slate-50 transition-colors">
                    <i class="ti ti-file-plus text-slate-400"></i> Upload Dokumen
                </a>
                @endcan
                <a href="{{ route('assets.geometry.index', $asset) }}"
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white border border-slate-200
                          text-sm text-slate-700 rounded-lg hover:bg-slate-50 transition-colors">
                    <i class="ti ti-map-pin text-slate-400"></i> Geometri GIS
                </a>
            </div>
        </div>

        {{-- Checklist dokumen --}}
        @if(count($dokumenBelumAda) > 0)
        <div class="bg-amber-50 border border-amber-200 rounded-xl p-4">
            <div class="flex items-center gap-2 mb-3">
                <i class="ti ti-alert-triangle text-amber-500"></i>
                <p class="text-sm font-semibold text-amber-800">
                    {{ count($dokumenBelumAda) }} dokumen wajib belum ada
                </p>
                <div class="ml-auto">
                    <div class="text-xs text-amber-600">{{ $kelengkapanPct }}% lengkap</div>
                    <div class="w-24 h-1.5 bg-amber-200 rounded-full mt-1">
                        <div class="h-full bg-amber-500 rounded-full"
                             style="width: {{ $kelengkapanPct }}%"></div>
                    </div>
                </div>
            </div>
            <div class="flex flex-wrap gap-2">
                @foreach($dokumenBelumAda as $kode)
                <span class="text-xs px-2.5 py-1 bg-white border border-amber-200 text-amber-700 rounded-lg">
                    <i class="ti ti-x text-amber-400"></i> {{ $kode }}
                </span>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Data teknis --}}
        <div class="bg-white border border-slate-200 rounded-xl p-5">
            <h3 class="text-sm font-semibold text-slate-800 mb-4">Data Teknis</h3>
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                <div>
                    <p class="text-xs text-slate-400 mb-0.5">Tahun Bangun</p>
                    <p class="text-sm font-medium text-slate-700">{{ $asset->tahun_bangun ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-400 mb-0.5">Umur Aset</p>
                    <p class="text-sm font-medium text-slate-700">{{ $asset->umurTahun() }} tahun</p>
                </div>
                <div>
                    <p class="text-xs text-slate-400 mb-0.5">Sisa Umur Rencana</p>
                    <p class="text-sm font-medium {{ $asset->sisaUmurTahun() < 5 ? 'text-red-600' : 'text-slate-700' }}">
                        {{ $asset->sisaUmurTahun() }} tahun
                    </p>
                </div>
                <div>
                    <p class="text-xs text-slate-400 mb-0.5">RCI Score</p>
                    <p class="text-sm font-medium text-slate-700">
                        {{ $asset->rci_score_terakhir ? number_format($asset->rci_score_terakhir,1) . '/100' : '—' }}
                    </p>
                </div>
                <div>
                    <p class="text-xs text-slate-400 mb-0.5">Inspeksi Terakhir</p>
                    <p class="text-sm font-medium text-slate-700">
                        {{ $asset->tgl_inspeksi_terakhir?->format('d M Y') ?? '—' }}
                    </p>
                </div>
                <div>
                    <p class="text-xs text-slate-400 mb-0.5">DAS</p>
                    <p class="text-sm font-medium text-slate-700">{{ $asset->das ?? '—' }}</p>
                </div>
            </div>

            {{-- Atribut teknis spesifik --}}
            @if($asset->atribut_teknis)
            <div class="mt-4 pt-4 border-t border-slate-100">
                <p class="text-xs text-slate-400 mb-3">Atribut Spesifik</p>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                    @foreach($asset->atribut_teknis as $key => $val)
                    @if($val !== null && $val !== '')
                    <div>
                        <p class="text-xs text-slate-400 mb-0.5">{{ ucwords(str_replace('_',' ',$key)) }}</p>
                        <p class="text-sm font-medium text-slate-700">{{ $val }}</p>
                    </div>
                    @endif
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        {{-- OP tahun ini --}}
        <div class="bg-white border border-slate-200 rounded-xl overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-slate-800">OP Tahun {{ now()->year }}</h3>
                @can('op_record.create')
                <a href="{{ route('op.records.create', ['asset_id' => $asset->id]) }}"
                   class="text-xs text-sky-600 hover:underline flex items-center gap-1">
                    <i class="ti ti-plus"></i> Input OP
                </a>
                @endcan
            </div>
            @if($asset->opRecords->isEmpty())
            <p class="px-5 py-6 text-center text-sm text-slate-400">Belum ada data OP tahun ini</p>
            @else
            <div class="divide-y divide-slate-100">
                @foreach($asset->opRecords as $op)
                <div class="px-5 py-3 flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-700">{{ $op->labelBulan() }}</p>
                        <p class="text-xs text-slate-400 mt-0.5">{{ ucfirst($op->jenis_op) }}</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="text-right">
                            <p class="text-sm font-semibold text-slate-800">{{ $op->realisasi_pct }}%</p>
                            <div class="w-20 h-1.5 bg-slate-100 rounded-full mt-1">
                                <div class="h-full bg-sky-500 rounded-full"
                                     style="width: {{ min($op->realisasi_pct, 100) }}%"></div>
                            </div>
                        </div>
                        <span class="inline-flex px-2 py-0.5 rounded-md text-xs font-medium {{ $op->badgeStatus() }}">
                            {{ $op->labelStatus() }}
                        </span>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>

        {{-- Proyek terkait --}}
        @if($asset->projects->isNotEmpty())
        <div class="bg-white border border-slate-200 rounded-xl overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100">
                <h3 class="text-sm font-semibold text-slate-800">Proyek Aktif Terkait</h3>
            </div>
            <div class="divide-y divide-slate-100">
                @foreach($asset->projects as $project)
                <div class="px-5 py-3 flex items-center justify-between gap-4">
                    <div class="min-w-0">
                        <a href="{{ route('projects.show', $project) }}"
                           class="text-sm font-medium text-slate-800 hover:text-sky-600 truncate block">
                            {{ $project->nama }}
                        </a>
                        <p class="text-xs text-slate-400 mt-0.5">{{ $project->labelPhase() }} · {{ $project->tahun_anggaran }}</p>
                    </div>
                    <div class="flex items-center gap-2 flex-shrink-0">
                        <span class="text-sm font-semibold text-slate-700">{{ $project->realisasi_fisik_pct }}%</span>
                        <span class="text-xs px-2 py-0.5 rounded-md {{ $project->badgeHealth() }}">
                            {{ ucfirst($project->healthStatus()) }}
                        </span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

    </div>

    {{-- Kolom kanan: dokumen & kondisi --}}
    <div class="lg:w-80 space-y-5 flex-shrink-0">

        {{-- Kondisi terbaru --}}
        @if($asset->kondisiTerbaru)
        <div class="bg-white border border-slate-200 rounded-xl p-5">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-sm font-semibold text-slate-800">Kondisi Terakhir</h3>
                <a href="{{ route('assets.conditions.index', $asset) }}"
                   class="text-xs text-sky-600 hover:underline">Riwayat →</a>
            </div>
            @php $kond = $asset->kondisiTerbaru; @endphp
            <div class="flex items-center gap-3 mb-3">
                <div class="w-12 h-12 rounded-xl {{ $asset->badgeKondisi() }} flex items-center justify-center">
                    <span class="text-xl font-bold">{{ $kond->kondisi }}</span>
                </div>
                <div>
                    <p class="text-sm font-semibold text-slate-800">{{ $kond->labelKondisi() }}</p>
                    <p class="text-xs text-slate-400">{{ $kond->tgl_inspeksi?->format('d M Y') }}</p>
                </div>
            </div>
            @if($kond->rci_score)
            <div class="mb-3">
                <div class="flex justify-between text-xs text-slate-500 mb-1">
                    <span>RCI Score</span><span class="font-medium">{{ $kond->rci_score }}/100</span>
                </div>
                <div class="h-2 bg-slate-100 rounded-full">
                    <div class="h-full rounded-full transition-all"
                         style="width: {{ $kond->rci_score }}%;
                                background: {{ $kond->rci_score >= 80 ? '#22c55e' : ($kond->rci_score >= 60 ? '#eab308' : ($kond->rci_score >= 40 ? '#f97316' : '#ef4444')) }}">
                    </div>
                </div>
            </div>
            @endif
            @if($kond->temuan)
            <p class="text-xs text-slate-600 mt-2 line-clamp-3">{{ $kond->temuan }}</p>
            @endif
            @if($kond->inspektur)
            <p class="text-xs text-slate-400 mt-2">Inspektur: {{ $kond->inspektur->name }}</p>
            @endif
        </div>
        @endif

        {{-- Dokumen --}}
        <div class="bg-white border border-slate-200 rounded-xl overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-slate-800">
                    Dokumen ({{ $asset->documents->count() }})
                </h3>
                <a href="{{ route('documents.index', ['entity_id' => $asset->id]) }}"
                   class="text-xs text-sky-600 hover:underline">Semua →</a>
            </div>
            @if($asset->documents->isEmpty())
            <p class="px-5 py-6 text-center text-sm text-slate-400">Belum ada dokumen</p>
            @else
            <div class="divide-y divide-slate-100">
                @foreach($asset->documents->take(8) as $doc)
                <div class="px-5 py-2.5 flex items-center justify-between gap-3">
                    <div class="min-w-0">
                        <a href="{{ route('documents.show', $doc) }}"
                           class="text-xs font-medium text-slate-700 hover:text-sky-600 truncate block">
                            {{ $doc->judul }}
                        </a>
                        <p class="text-xs text-slate-400 mt-0.5">
                            {{ $doc->documentType?->nama }} · v{{ $doc->versiLabel() }}
                        </p>
                    </div>
                    <span class="flex-shrink-0 text-xs px-1.5 py-0.5 rounded {{ $doc->badgeStatus() }}">
                        {{ ucfirst($doc->status) }}
                    </span>
                </div>
                @endforeach
            </div>
            @endif
        </div>

        {{-- Komponen --}}
        @if($asset->components->isNotEmpty())
        <div class="bg-white border border-slate-200 rounded-xl p-5">
            <h3 class="text-sm font-semibold text-slate-800 mb-3">Komponen Utama</h3>
            <div class="space-y-2">
                @foreach($asset->components as $comp)
                <div class="flex items-center justify-between text-sm">
                    <div class="flex items-center gap-2">
                        @if($comp->is_kritis)
                        <i class="ti ti-star-filled text-amber-400 text-xs"></i>
                        @endif
                        <span class="text-slate-700">{{ $comp->nama_komponen }}</span>
                    </div>
                    @if($comp->kondisi)
                    <span class="text-xs px-1.5 py-0.5 rounded {{ $asset->badgeKondisi() }}">
                        {{ $comp->kondisi }}
                    </span>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
        @endif

    </div>
</div>
@endsection
