@extends('layouts.app')
@section('title', isset($item) ? 'Edit Koleksi' : 'Tambah Koleksi')

@section('breadcrumb')
    <a href="{{ route('library.index') }}" class="text-slate-500 hover:text-slate-700 text-sm">Perpustakaan</a>
    <i class="ti ti-chevron-right text-slate-300 text-xs"></i>
    <span class="text-slate-800 font-medium text-sm">{{ isset($item) ? 'Edit' : 'Tambah Koleksi' }}</span>
@endsection

@section('content')
@php
    $isEdit = isset($item);
    $item   = $item ?? null;
@endphp
<div class="max-w-2xl">
<x-page-header :title="$isEdit ? 'Edit: ' . ($item?->judul ?? '') : 'Tambah Koleksi Perpustakaan'"
    icon="ti-books" />

<form method="POST"
      action="{{ $isEdit ? route('library.update', $item) : route('library.store') }}"
      enctype="multipart/form-data"
      class="space-y-5">
    @csrf
    @if($isEdit) @method('PUT') @endif

    {{-- Identitas --}}
    <div class="bg-white border border-slate-200 rounded-xl p-5 space-y-4">
        <h3 class="text-sm font-semibold text-slate-700 pb-2 border-b border-slate-100">Identitas Koleksi</h3>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">
                    Kode Item <span class="text-red-500">*</span>
                </label>
                <input type="text" name="kode_item" required
                       value="{{ old('kode_item', $item?->kode_item ?? '') }}"
                       placeholder="BK-001, JRN-025..."
                       class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm font-mono
                              focus:outline-none focus:ring-2 focus:ring-sky-500
                              @error('kode_item') border-red-400 @enderror">
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">
                    Tipe <span class="text-red-500">*</span>
                </label>
                <select name="tipe" required
                        class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm
                               focus:outline-none focus:ring-2 focus:ring-sky-500">
                    @foreach($tipeList as $t)
                    <option value="{{ $t }}" @selected(old('tipe', $item?->tipe ?? '') === $t)>
                        {{ ucfirst(str_replace('_', ' ', $t)) }}
                    </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div>
            <label class="block text-xs font-medium text-slate-600 mb-1">
                Judul <span class="text-red-500">*</span>
            </label>
            <input type="text" name="judul" required
                   value="{{ old('judul', $item?->judul ?? '') }}"
                   placeholder="Judul lengkap buku/jurnal/standar..."
                   class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm
                          focus:outline-none focus:ring-2 focus:ring-sky-500
                          @error('judul') border-red-400 @enderror">
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Penulis / Pengarang</label>
                <input type="text" name="penulis"
                       value="{{ old('penulis', $item?->penulis ?? '') }}"
                       placeholder="Nama penulis"
                       class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm
                              focus:outline-none focus:ring-2 focus:ring-sky-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Penerbit</label>
                <input type="text" name="penerbit"
                       value="{{ old('penerbit', $item?->penerbit ?? '') }}"
                       placeholder="Nama penerbit"
                       class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm
                              focus:outline-none focus:ring-2 focus:ring-sky-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Tahun Terbit</label>
                <input type="number" name="tahun_terbit" min="1900" max="{{ date('Y') }}"
                       value="{{ old('tahun_terbit', $item?->tahun_terbit ?? '') }}"
                       placeholder="{{ date('Y') }}"
                       class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm
                              focus:outline-none focus:ring-2 focus:ring-sky-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">ISBN / No. Standar</label>
                <input type="text" name="isbn"
                       value="{{ old('isbn', $item?->isbn ?? '') }}"
                       placeholder="978-xxx-xxx"
                       class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm font-mono
                              focus:outline-none focus:ring-2 focus:ring-sky-500">
            </div>
        </div>

        <div>
            <label class="block text-xs font-medium text-slate-600 mb-1">Tags</label>
            <input type="text" name="tags"
                   value="{{ old('tags', $isEdit && $item?->tags ? implode(', ', $item->tags) : '') }}"
                   placeholder="irigasi, bendung, hidrologi — pisahkan dengan koma"
                   class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm
                          focus:outline-none focus:ring-2 focus:ring-sky-500">
        </div>
    </div>

    {{-- Stok & Lokasi --}}
    <div class="bg-white border border-slate-200 rounded-xl p-5 space-y-4">
        <h3 class="text-sm font-semibold text-slate-700 pb-2 border-b border-slate-100">Stok & Lokasi</h3>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">
                    Stok Fisik <span class="text-red-500">*</span>
                </label>
                <input type="number" name="stok_fisik" required min="0"
                       value="{{ old('stok_fisik', $item?->stok_fisik ?? 1) }}"
                       class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm
                              focus:outline-none focus:ring-2 focus:ring-sky-500">
                <p class="text-xs text-slate-400 mt-1">Jumlah eksemplar yang tersedia</p>
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Lokasi Rak</label>
                <select name="physical_location_id"
                        class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm
                               focus:outline-none focus:ring-2 focus:ring-sky-500">
                    <option value="">Pilih lokasi...</option>
                    @foreach($locations as $loc)
                    <option value="{{ $loc->id }}"
                            @selected(old('physical_location_id', $item?->physical_location_id ?? '') === $loc->id)>
                        {{ $loc->kode_lokasi }} — {{ $loc->gedung }}
                        @if($loc->rak) Rak {{ $loc->rak }} @endif
                    </option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    {{-- File digital --}}
    <div class="bg-white border border-slate-200 rounded-xl p-5 space-y-3">
        <h3 class="text-sm font-semibold text-slate-700 pb-2 border-b border-slate-100">
            File Digital (Opsional)
        </h3>
        @if($isEdit && $item?->ada_digital)
        <div class="flex items-center gap-2 text-sm text-emerald-600 bg-emerald-50
                    border border-emerald-200 rounded-lg px-3 py-2">
            <i class="ti ti-file-check"></i>
            <span>File digital sudah ada</span>
        </div>
        @endif
        <input type="file" name="file_digital" accept=".pdf"
               class="block w-full text-sm text-slate-500
                      file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0
                      file:text-sm file:font-medium file:bg-sky-50 file:text-sky-700
                      hover:file:bg-sky-100 cursor-pointer">
        <p class="text-xs text-slate-400">Format PDF, maks 50MB</p>
    </div>

    <div class="flex items-center justify-between">
        <a href="{{ route('library.index') }}" class="text-sm text-slate-500 hover:text-slate-700">← Kembali</a>
        <button type="submit"
                class="px-5 py-2.5 bg-sky-600 text-white text-sm font-medium rounded-xl hover:bg-sky-700">
            {{ $isEdit ? 'Simpan Perubahan' : 'Tambah Koleksi' }}
        </button>
    </div>
</form>
</div>
@endsection
