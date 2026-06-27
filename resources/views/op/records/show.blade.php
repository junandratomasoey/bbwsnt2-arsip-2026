{{-- resources/views/op/records/show.blade.php --}}
@extends('layouts.app')
@section('title', 'Detail Rekaman OP')

@section('breadcrumb')
    <a href="{{ route('op.records.index') }}" class="text-slate-500 hover:text-slate-700 text-sm">Rekaman OP</a>
    <i class="ti ti-chevron-right text-slate-300 text-xs"></i>
    <span class="text-slate-800 font-medium text-sm">Detail</span>
@endsection

@section('content')
<x-page-header :title="'OP — ' . $record->asset?->nama . ' (' . $record->labelBulan() . ' ' . $record->periode_tahun . ')'"
    icon="ti-settings-2">
    @can('op_record.edit')
    <a href="{{ route('op.records.edit', $record) }}"
       class="inline-flex items-center gap-1.5 px-4 py-2 bg-white border border-slate-200 text-sm text-slate-700 rounded-xl hover:bg-slate-50">
        <i class="ti ti-edit text-slate-400"></i> Edit
    </a>
    @endcan
</x-page-header>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-5">
        <div class="bg-white border border-slate-200 rounded-xl p-5">
            <h3 class="text-sm font-semibold text-slate-700 mb-4 pb-2 border-b border-slate-100">Detail Rekaman</h3>
            <div class="grid grid-cols-2 gap-4">
                <div><p class="text-xs text-slate-400 mb-0.5">Aset</p>
                    <a href="{{ route('assets.show', $record->asset) }}" class="text-sm font-medium text-sky-600 hover:underline">
                        {{ $record->asset?->nama }}
                    </a>
                </div>
                <div><p class="text-xs text-slate-400 mb-0.5">Satker</p>
                    <p class="text-sm text-slate-700">{{ $record->unitKerja?->singkatan }}</p>
                </div>
                <div><p class="text-xs text-slate-400 mb-0.5">Periode</p>
                    <p class="text-sm font-semibold text-slate-700">{{ $record->labelBulan() }} {{ $record->periode_tahun }}</p>
                </div>
                <div><p class="text-xs text-slate-400 mb-0.5">Jenis OP</p>
                    <p class="text-sm text-slate-700">{{ ucfirst(str_replace('_',' ',$record->jenis_op)) }}</p>
                </div>
                <div><p class="text-xs text-slate-400 mb-0.5">Tgl Pelaksanaan</p>
                    <p class="text-sm text-slate-700">{{ $record->tgl_pelaksanaan?->format('d M Y') ?? '—' }}</p>
                </div>
                <div><p class="text-xs text-slate-400 mb-0.5">Status</p>
                    <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium {{ $record->badgeStatus() }}">
                        {{ $record->labelStatus() }}
                    </span>
                </div>
                <div><p class="text-xs text-slate-400 mb-0.5">Realisasi Fisik</p>
                    <p class="text-2xl font-bold text-slate-800">{{ $record->realisasi_pct }}%</p>
                </div>
                <div><p class="text-xs text-slate-400 mb-0.5">Petugas</p>
                    <p class="text-sm text-slate-700">{{ $record->petugas?->name ?? $record->tim_op ?? '—' }}</p>
                </div>
            </div>

            @if($record->anggaran || $record->realisasi_anggaran)
            <div class="mt-4 pt-4 border-t border-slate-100 grid grid-cols-2 gap-4">
                <div><p class="text-xs text-slate-400 mb-0.5">Anggaran</p>
                    <p class="text-sm text-slate-700">{{ $record->anggaran ? 'Rp ' . number_format($record->anggaran,0,',','.') : '—' }}</p>
                </div>
                <div><p class="text-xs text-slate-400 mb-0.5">Realisasi Anggaran</p>
                    <p class="text-sm text-slate-700">{{ $record->realisasi_anggaran ? 'Rp ' . number_format($record->realisasi_anggaran,0,',','.') : '—' }}</p>
                </div>
            </div>
            @endif
        </div>

        @if($record->kegiatan_dilakukan)
        <div class="bg-white border border-slate-200 rounded-xl p-5">
            <h3 class="text-sm font-semibold text-slate-700 mb-3">Kegiatan Dilakukan</h3>
            <ul class="space-y-1.5">
                @foreach($record->kegiatan_dilakukan as $k)
                <li class="flex items-start gap-2 text-sm text-slate-600">
                    <i class="ti ti-check text-emerald-500 mt-0.5 flex-shrink-0"></i> {{ $k }}
                </li>
                @endforeach
            </ul>
        </div>
        @endif

        @if($record->kendala)
        <div class="bg-amber-50 border border-amber-200 rounded-xl p-4">
            <h3 class="text-sm font-semibold text-amber-800 mb-2 flex items-center gap-2">
                <i class="ti ti-alert-triangle text-amber-500"></i> Kendala
            </h3>
            <p class="text-sm text-amber-700">{{ $record->kendala }}</p>
        </div>
        @endif

        @if($record->foto_paths)
        <div class="bg-white border border-slate-200 rounded-xl p-5">
            <h3 class="text-sm font-semibold text-slate-700 mb-3">Foto Dokumentasi</h3>
            <div class="grid grid-cols-3 gap-2">
                @foreach($record->foto_paths as $foto)
                <img src="{{ Storage::url($foto) }}" class="w-full h-28 object-cover rounded-lg border border-slate-200">
                @endforeach
            </div>
        </div>
        @endif
    </div>

    <div>
        <div class="bg-white border border-slate-200 rounded-xl p-5">
            <h3 class="text-sm font-semibold text-slate-700 mb-3">Progress Realisasi</h3>
            <div class="text-center py-3">
                <div class="text-4xl font-bold {{ $record->realisasi_pct >= 80 ? 'text-emerald-600' : ($record->realisasi_pct >= 50 ? 'text-amber-600' : 'text-red-600') }}">
                    {{ $record->realisasi_pct }}%
                </div>
            </div>
            <div class="h-3 bg-slate-100 rounded-full overflow-hidden">
                <div class="h-full rounded-full transition-all {{ $record->realisasi_pct >= 80 ? 'bg-emerald-500' : ($record->realisasi_pct >= 50 ? 'bg-amber-500' : 'bg-red-500') }}"
                     style="width: {{ min($record->realisasi_pct, 100) }}%"></div>
            </div>
            <div class="mt-4 space-y-2 text-sm">
                <a href="{{ route('assets.show', $record->asset) }}"
                   class="flex items-center gap-2 text-sky-600 hover:underline">
                    <i class="ti ti-building-bridge text-sm"></i> Lihat detail aset
                </a>
                <a href="{{ route('op.records.index', ['asset_id' => $record->asset_id]) }}"
                   class="flex items-center gap-2 text-slate-500 hover:text-slate-700">
                    <i class="ti ti-list text-sm"></i> OP lainnya untuk aset ini
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
