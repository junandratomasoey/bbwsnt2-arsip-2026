@extends('layouts.app')
@section('title', isset($assetType) ? 'Edit Jenis Aset' : 'Tambah Jenis Aset')
@section('breadcrumb')
    <a href="{{ route('superadmin.asset-types.index') }}" class="text-slate-500 hover:text-slate-700 text-sm">Jenis Aset</a>
    <i class="ti ti-chevron-right text-slate-300 text-xs"></i>
    <span class="text-slate-800 font-medium text-sm">{{ isset($assetType) ? 'Edit' : 'Tambah' }}</span>
@endsection
@section('content')
@php $isEdit = isset($assetType); $assetType = $assetType ?? null; @endphp
<div class="max-w-xl">
<x-page-header :title="$isEdit ? 'Edit: ' . ($assetType?->nama ?? '') : 'Tambah Jenis Aset'" icon="ti-building-bridge" />

<form method="POST"
      action="{{ $isEdit ? route('superadmin.asset-types.update', $assetType) : route('superadmin.asset-types.store') }}"
      class="space-y-5">
    @csrf @if($isEdit) @method('PUT') @endif

    <div class="bg-white border border-slate-200 rounded-xl p-5 space-y-4">
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wide mb-1.5">
                    Kode <span class="text-red-500">*</span>
                </label>
                <input type="text" name="kode" required
                       value="{{ old('kode', $assetType?->kode ?? '') }}"
                       placeholder="BDG, EMB, WDK..."
                       class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm font-mono uppercase
                              focus:outline-none focus:ring-2 focus:ring-sky-500
                              @error('kode') border-red-400 @enderror">
                <p class="text-xs text-slate-400 mt-1">Maks 20 karakter, unik</p>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wide mb-1.5">
                    Urutan
                </label>
                <input type="number" name="urutan" min="0"
                       value="{{ old('urutan', $assetType?->urutan ?? 0) }}"
                       class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm
                              focus:outline-none focus:ring-2 focus:ring-sky-500">
            </div>
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wide mb-1.5">
                Nama Jenis Aset <span class="text-red-500">*</span>
            </label>
            <input type="text" name="nama" required
                   value="{{ old('nama', $assetType?->nama ?? '') }}"
                   placeholder="Bendung, Embung, Waduk..."
                   class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm
                          focus:outline-none focus:ring-2 focus:ring-sky-500
                          @error('nama') border-red-400 @enderror">
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wide mb-1.5">
                Kategori <span class="text-red-500">*</span>
            </label>
            <input type="text" name="kategori" required list="kategori-list"
                   value="{{ old('kategori', $assetType?->kategori ?? '') }}"
                   placeholder="bendung, embung, saluran..."
                   class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm
                          focus:outline-none focus:ring-2 focus:ring-sky-500">
            <datalist id="kategori-list">
                <option value="bendung">
                <option value="embung">
                <option value="waduk">
                <option value="saluran">
                <option value="jaringan_irigasi">
                <option value="bangunan_air">
                <option value="tanggul">
                <option value="pompa">
            </datalist>
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wide mb-1.5">
                Referensi Standar OP
            </label>
            <input type="text" name="standar_op"
                   value="{{ old('standar_op', $assetType?->standar_op ?? '') }}"
                   placeholder="Permen PUPR No. 12/2015..."
                   class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm
                          focus:outline-none focus:ring-2 focus:ring-sky-500">
        </div>

        @if($isEdit)
        <label class="flex items-center gap-2 cursor-pointer">
            <input type="checkbox" name="is_aktif" value="1"
                   @checked(old('is_aktif', $assetType?->is_aktif ?? true))
                   class="rounded border-slate-300 text-sky-600">
            <span class="text-sm text-slate-700">Jenis aset aktif</span>
        </label>
        @endif
    </div>

    <div class="flex items-center justify-between">
        <a href="{{ route('superadmin.asset-types.index') }}" class="text-sm text-slate-500 hover:text-slate-700">← Kembali</a>
        <button type="submit"
                class="px-5 py-2.5 text-white text-sm font-medium rounded-xl hover:opacity-90"
                style="background:#003366">
            {{ $isEdit ? 'Simpan Perubahan' : 'Tambah Jenis Aset' }}
        </button>
    </div>
</form>
</div>
@endsection
