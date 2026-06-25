{{-- resources/views/search/results.blade.php --}}
@extends('layouts.app')
@section('title', 'Hasil Pencarian')

@section('breadcrumb')
    <span class="text-slate-800 font-medium text-sm">Hasil Pencarian: "{{ $q }}"</span>
@endsection

@section('content')
<x-page-header title="Hasil Pencarian" :desc="'Menampilkan hasil untuk &quot;' . $q . '&quot;'" icon="ti-search" />

@php
$totalResults = $results['assets']->count() + $results['documents']->count() + $results['knowledge']->count();
@endphp

@if($totalResults === 0)
<div class="bg-white border border-slate-200 rounded-xl py-16 text-center">
    <i class="ti ti-search-off text-4xl text-slate-200 block mb-3"></i>
    <p class="text-slate-500 font-medium">Tidak ada hasil untuk "{{ $q }}"</p>
    <p class="text-slate-400 text-sm mt-1">Coba kata kunci yang berbeda</p>
</div>
@else
<div class="space-y-6">
    @if($results['assets']->isNotEmpty())
    <div>
        <h3 class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-3 flex items-center gap-2">
            <i class="ti ti-building-bridge"></i> Aset ({{ $results['assets']->count() }})
        </h3>
        <div class="space-y-2">
            @foreach($results['assets'] as $a)
            <a href="{{ route('assets.show', $a) }}"
               class="flex items-center gap-3 bg-white border border-slate-200 rounded-xl px-4 py-3 hover:border-sky-200 transition-colors">
                <i class="ti ti-building-bridge text-sky-500 text-lg flex-shrink-0"></i>
                <div>
                    <p class="text-sm font-medium text-slate-800">{{ $a->nama }}</p>
                    <p class="text-xs text-slate-400">{{ $a->asset_code }} · {{ $a->assetType?->nama }} · {{ $a->kabupaten }}</p>
                </div>
            </a>
            @endforeach
        </div>
    </div>
    @endif

    @if($results['documents']->isNotEmpty())
    <div>
        <h3 class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-3 flex items-center gap-2">
            <i class="ti ti-files"></i> Dokumen ({{ $results['documents']->count() }})
        </h3>
        <div class="space-y-2">
            @foreach($results['documents'] as $d)
            <a href="{{ route('documents.show', $d) }}"
               class="flex items-center gap-3 bg-white border border-slate-200 rounded-xl px-4 py-3 hover:border-sky-200 transition-colors">
                <i class="ti ti-file-description text-amber-500 text-lg flex-shrink-0"></i>
                <div>
                    <p class="text-sm font-medium text-slate-800">{{ $d->judul }}</p>
                    <p class="text-xs text-slate-400">{{ $d->doc_number }} · {{ $d->documentType?->nama }}</p>
                </div>
            </a>
            @endforeach
        </div>
    </div>
    @endif

    @if($results['knowledge']->isNotEmpty())
    <div>
        <h3 class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-3 flex items-center gap-2">
            <i class="ti ti-brain"></i> Knowledge ({{ $results['knowledge']->count() }})
        </h3>
        <div class="space-y-2">
            @foreach($results['knowledge'] as $k)
            <a href="{{ route('knowledge.show', $k->slug) }}"
               class="flex items-center gap-3 bg-white border border-slate-200 rounded-xl px-4 py-3 hover:border-sky-200 transition-colors">
                <i class="ti ti-article text-purple-500 text-lg flex-shrink-0"></i>
                <div>
                    <p class="text-sm font-medium text-slate-800">{{ $k->judul }}</p>
                    <p class="text-xs text-slate-400">{{ $k->labelTipe() }} · {{ $k->author?->name }}</p>
                </div>
            </a>
            @endforeach
        </div>
    </div>
    @endif
</div>
@endif
@endsection
