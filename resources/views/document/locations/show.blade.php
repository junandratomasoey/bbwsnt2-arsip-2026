@extends('layouts.app')
@section('title', $location->kode_lokasi)

@section('breadcrumb')
    <a href="{{ route('locations.index') }}" class="text-slate-500 hover:text-slate-700 text-sm">Lokasi Fisik</a>
    <i class="ti ti-chevron-right text-slate-300 text-xs"></i>
    <span class="text-slate-800 font-medium text-sm">{{ $location->kode_lokasi }}</span>
@endsection

@section('content')
<x-page-header :title="'Lokasi — ' . $location->kode_lokasi" icon="ti-box">
    @can('physical_location.edit')
    <a href="{{ route('locations.edit', $location) }}"
       class="inline-flex items-center gap-1.5 px-4 py-2 bg-white border border-slate-200
              text-sm text-slate-700 rounded-xl hover:bg-slate-50">
        <i class="ti ti-edit text-slate-400"></i> Edit
    </a>
    @endcan
</x-page-header>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="space-y-5">
        <div class="bg-white border border-slate-200 rounded-xl p-5">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-12 h-12 rounded-xl bg-sky-50 border border-sky-100
                            flex items-center justify-center">
                    <i class="ti ti-box text-sky-600 text-2xl"></i>
                </div>
                <div>
                    <p class="text-lg font-bold text-slate-800 font-mono">{{ $location->kode_lokasi }}</p>
                    <p class="text-sm text-slate-500">{{ $location->documents->count() }} dokumen tersimpan</p>
                </div>
            </div>

            <div class="space-y-3">
                @foreach([
                    ['Gedung',  $location->gedung],
                    ['Lantai',  $location->lantai],
                    ['Ruang',   $location->ruang],
                    ['Lemari',  $location->lemari],
                    ['Rak',     $location->rak],
                    ['Laci',    $location->laci],
                    ['Kapasitas', $location->kapasitas_item ? $location->kapasitas_item . ' item' : null],
                ] as [$label, $val])
                @if($val)
                <div class="flex items-center justify-between text-sm">
                    <span class="text-slate-500">{{ $label }}</span>
                    <span class="font-medium text-slate-700">{{ $val }}</span>
                </div>
                @endif
                @endforeach
            </div>

            @if($location->keterangan)
            <div class="mt-4 pt-4 border-t border-slate-100">
                <p class="text-xs text-slate-400 mb-1">Keterangan</p>
                <p class="text-sm text-slate-600">{{ $location->keterangan }}</p>
            </div>
            @endif
        </div>
    </div>

    {{-- Dokumen di lokasi ini --}}
    <div class="lg:col-span-2">
        <div class="bg-white border border-slate-200 rounded-xl overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-slate-700">
                    Dokumen di Lokasi Ini ({{ $location->documents->count() }})
                </h3>
            </div>
            @if($location->documents->isEmpty())
            <p class="px-5 py-8 text-center text-sm text-slate-400">
                Belum ada dokumen di lokasi ini
            </p>
            @else
            <div class="divide-y divide-slate-100">
                @foreach($location->documents as $doc)
                <div class="px-5 py-3 flex items-center justify-between gap-3">
                    <div class="min-w-0">
                        <a href="{{ route('documents.show', $doc) }}"
                           class="text-sm font-medium text-slate-800 hover:text-sky-600 truncate block">
                            {{ $doc->judul }}
                        </a>
                        <p class="text-xs text-slate-400 mt-0.5">
                            {{ $doc->doc_number ?? '—' }} ·
                            {{ $doc->documentType?->nama }}
                        </p>
                    </div>
                    <span class="flex-shrink-0 text-xs px-2 py-0.5 rounded {{ $doc->badgeStatus() }}">
                        {{ ucfirst($doc->status) }}
                    </span>
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
