@extends('layouts.app')
@section('title', 'Riwayat Kondisi')
@section('breadcrumb')
    <a href="{{ route('assets.show', $asset) }}" class="text-slate-500 hover:text-slate-700 text-sm">{{ $asset->nama }}</a>
    <i class="ti ti-chevron-right text-slate-300 text-xs"></i>
    <span class="text-slate-800 font-medium text-sm">Riwayat Kondisi</span>
@endsection
@section('content')
<x-page-header :title="'Riwayat Kondisi — ' . $asset->nama" icon="ti-clipboard-check">
    @can('asset_condition.create')
    <a href="{{ route('assets.conditions.create', $asset) }}"
       class="inline-flex items-center gap-2 px-4 py-2 bg-sky-600 text-white text-sm font-medium rounded-xl hover:bg-sky-700">
        <i class="ti ti-plus"></i> Input Kondisi
    </a>
    @endcan
</x-page-header>
<div class="bg-white border border-slate-200 rounded-xl overflow-hidden">
    <div class="divide-y divide-slate-100">
        @forelse($conditions as $cond)
        <div class="px-5 py-4">
            <div class="flex items-start justify-between gap-4">
                <div class="flex items-center gap-3">
                    <span class="inline-flex w-10 h-10 rounded-xl items-center justify-center text-sm font-bold {{ $cond->badgeKondisi() }}">
                        {{ $cond->kondisi }}
                    </span>
                    <div>
                        <p class="text-sm font-medium text-slate-800">{{ $cond->labelKondisi() }}</p>
                        <p class="text-xs text-slate-400 mt-0.5">
                            {{ $cond->tgl_inspeksi?->format('d M Y') }} ·
                            {{ ucfirst($cond->jenis_inspeksi) }} ·
                            {{ $cond->inspektur?->name ?? 'Tim inspeksi' }}
                        </p>
                    </div>
                </div>
                <div class="text-right flex-shrink-0">
                    @if($cond->rci_score)
                    <p class="text-lg font-bold text-slate-800">{{ $cond->rci_score }}/100</p>
                    @endif
                    @can('asset_condition.edit')
                    <a href="{{ route('assets.conditions.edit', [$asset, $cond]) }}"
                       class="text-xs text-sky-600 hover:underline">Edit</a>
                    @endcan
                </div>
            </div>
            @if($cond->temuan)
            <p class="text-xs text-slate-600 mt-2 ml-13">{{ Str::limit($cond->temuan, 150) }}</p>
            @endif
        </div>
        @empty
        <p class="px-5 py-10 text-center text-slate-400">Belum ada data kondisi</p>
        @endforelse
    </div>
    @if($conditions->hasPages())
    <div class="px-5 py-4 border-t border-slate-100">{{ $conditions->links() }}</div>
    @endif
</div>
@endsection
