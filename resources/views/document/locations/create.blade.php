@extends('layouts.app')
@section('title', isset($location) ? 'Edit Lokasi Fisik' : 'Tambah Lokasi Fisik')

@section('breadcrumb')
    <a href="{{ route('locations.index') }}" class="text-slate-500 hover:text-slate-700 text-sm">Lokasi Fisik</a>
    <i class="ti ti-chevron-right text-slate-300 text-xs"></i>
    <span class="text-slate-800 font-medium text-sm">{{ isset($location) ? 'Edit' : 'Tambah' }}</span>
@endsection

@section('content')
@php
    $isEdit   = isset($location);
    $location = $location ?? null;
@endphp

<div class="max-w-xl">
<x-page-header :title="$isEdit ? 'Edit Lokasi: ' . ($location->kode_lokasi ?? '') : 'Tambah Lokasi Fisik'"
    icon="ti-box" />

<form method="POST"
      action="{{ $isEdit ? route('locations.update', $location) : route('locations.store') }}"
      class="space-y-5">
    @csrf
    @if($isEdit) @method('PUT') @endif

    <div class="bg-white border border-slate-200 rounded-xl p-5 space-y-4">
        <h3 class="text-sm font-semibold text-slate-700 pb-2 border-b border-slate-100">
            Identitas Lokasi
        </h3>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">
                    Kode Lokasi <span class="text-red-500">*</span>
                </label>
                <input type="text" name="kode_lokasi" required
                       value="{{ old('kode_lokasi', $location?->kode_lokasi ?? '') }}"
                       placeholder="GD-A/L1/R1/LMR-A1"
                       class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm font-mono
                              focus:outline-none focus:ring-2 focus:ring-sky-500
                              @error('kode_lokasi') border-red-400 @enderror">
                <p class="text-xs text-slate-400 mt-1">Format: GD-A/L1/R2/LMR-A1</p>
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Kapasitas (item)</label>
                <input type="number" name="kapasitas_item" min="1"
                       value="{{ old('kapasitas_item', $location?->kapasitas_item ?? '') }}"
                       placeholder="100"
                       class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm
                              focus:outline-none focus:ring-2 focus:ring-sky-500">
            </div>
        </div>
    </div>

    <div class="bg-white border border-slate-200 rounded-xl p-5 space-y-4">
        <h3 class="text-sm font-semibold text-slate-700 pb-2 border-b border-slate-100">
            Detail Lokasi Penyimpanan
        </h3>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">
                    Gedung <span class="text-red-500">*</span>
                </label>
                <input type="text" name="gedung" required list="gedung-list"
                       value="{{ old('gedung', $location?->gedung ?? '') }}"
                       placeholder="Gedung Utama"
                       class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm
                              focus:outline-none focus:ring-2 focus:ring-sky-500
                              @error('gedung') border-red-400 @enderror">
                <datalist id="gedung-list">
                    @foreach($gedungList as $g)
                    <option value="{{ $g }}">
                    @endforeach
                </datalist>
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Lantai</label>
                <input type="text" name="lantai"
                       value="{{ old('lantai', $location?->lantai ?? '') }}"
                       placeholder="1 / 2 / Dasar"
                       class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm
                              focus:outline-none focus:ring-2 focus:ring-sky-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Ruang</label>
                <input type="text" name="ruang"
                       value="{{ old('ruang', $location?->ruang ?? '') }}"
                       placeholder="Ruang Arsip / R.01"
                       class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm
                              focus:outline-none focus:ring-2 focus:ring-sky-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Lemari</label>
                <input type="text" name="lemari"
                       value="{{ old('lemari', $location?->lemari ?? '') }}"
                       placeholder="A / B / 01"
                       class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm
                              focus:outline-none focus:ring-2 focus:ring-sky-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Rak</label>
                <input type="text" name="rak"
                       value="{{ old('rak', $location?->rak ?? '') }}"
                       placeholder="1 / 2 / A1"
                       class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm
                              focus:outline-none focus:ring-2 focus:ring-sky-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Laci / Boks</label>
                <input type="text" name="laci"
                       value="{{ old('laci', $location?->laci ?? '') }}"
                       placeholder="1 / A / Top"
                       class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm
                              focus:outline-none focus:ring-2 focus:ring-sky-500">
            </div>
        </div>

        <div>
            <label class="block text-xs font-medium text-slate-600 mb-1">Keterangan</label>
            <textarea name="keterangan" rows="2"
                      placeholder="Keterangan tambahan tentang lokasi ini"
                      class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm
                             resize-none focus:outline-none focus:ring-2 focus:ring-sky-500">{{ old('keterangan', $location?->keterangan ?? '') }}</textarea>
        </div>
    </div>

    {{-- Preview kode lokasi otomatis --}}
    <div class="bg-sky-50 border border-sky-100 rounded-xl px-4 py-3"
         x-data="{
             gedung: '{{ $location?->gedung ?? '' }}',
             lantai: '{{ $location?->lantai ?? '' }}',
             ruang:  '{{ $location?->ruang ?? '' }}',
             lemari: '{{ $location?->lemari ?? '' }}',
             rak:    '{{ $location?->rak ?? '' }}',
             laci:   '{{ $location?->laci ?? '' }}',
         }">
        <p class="text-xs font-medium text-sky-700 mb-1">
            <i class="ti ti-info-circle"></i>
            Contoh kode lokasi berdasarkan input:
        </p>
        <p class="text-sm font-mono text-sky-800">
            <span x-text="[gedung,lantai,ruang,lemari,rak,laci].filter(Boolean).join(' / ') || '—'"></span>
        </p>
        <p class="text-xs text-sky-600 mt-1">
            Isi Kode Lokasi di atas sesuai konvensi yang berlaku di instansi.
        </p>
    </div>

    <div class="flex items-center justify-between">
        <a href="{{ route('locations.index') }}"
           class="text-sm text-slate-500 hover:text-slate-700">← Kembali</a>
        <button type="submit"
                class="px-5 py-2.5 bg-sky-600 text-white text-sm font-medium rounded-xl hover:bg-sky-700">
            {{ $isEdit ? 'Simpan Perubahan' : 'Tambah Lokasi' }}
        </button>
    </div>
</form>
</div>
@endsection
