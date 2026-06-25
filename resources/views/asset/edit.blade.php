@extends('layouts.app')
@section('title', isset($asset) ? 'Edit Aset' : 'Tambah Aset')

@section('breadcrumb')
    <a href="{{ route('assets.index') }}" class="text-slate-500 hover:text-slate-700 text-sm">Aset</a>
    <i class="ti ti-chevron-right text-slate-300 text-xs"></i>
    <span class="text-slate-800 font-medium text-sm">{{ isset($asset) ? 'Edit' : 'Tambah' }}</span>
@endsection

@section('content')
@php $isEdit = isset($asset); @endphp

<div class="max-w-3xl">
<x-page-header
    :title="$isEdit ? 'Edit: ' . $asset->nama : 'Tambah Aset Infrastruktur'"
    icon="ti-building-bridge" />

<form method="POST"
      action="{{ $isEdit ? route('assets.update', $asset) : route('assets.store') }}"
      enctype="multipart/form-data"
      class="space-y-5">
    @csrf
    @if($isEdit) @method('PUT') @endif

    {{-- Identitas --}}
    <div class="bg-white border border-slate-200 rounded-xl p-5 space-y-4">
        <h3 class="text-sm font-semibold text-slate-700 pb-2 border-b border-slate-100">Identitas Aset</h3>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Kode Aset</label>
                <input type="text" name="asset_code"
                       value="{{ old('asset_code', $asset->asset_code ?? '') }}"
                       placeholder="Auto-generate jika kosong"
                       class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm font-mono
                              focus:outline-none focus:ring-2 focus:ring-sky-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">
                    Jenis Aset <span class="text-red-500">*</span>
                </label>
                <select name="asset_type_id" required
                        class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm
                               focus:outline-none focus:ring-2 focus:ring-sky-500
                               @error('asset_type_id') border-red-400 @enderror">
                    <option value="">Pilih jenis...</option>
                    @foreach($assetTypes as $t)
                    <option value="{{ $t->id }}" @selected(old('asset_type_id', $asset->asset_type_id ?? '') === $t->id)>
                        {{ $t->nama }}
                    </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div>
            <label class="block text-xs font-medium text-slate-600 mb-1">
                Nama Aset <span class="text-red-500">*</span>
            </label>
            <input type="text" name="nama" required
                   value="{{ old('nama', $asset->nama ?? '') }}"
                   placeholder="Contoh: Bendung Oesao"
                   class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm
                          focus:outline-none focus:ring-2 focus:ring-sky-500
                          @error('nama') border-red-400 @enderror">
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">
                    Pengelola (Satker/PPK) <span class="text-red-500">*</span>
                </label>
                <select name="unit_kerja_id" required
                        class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm
                               focus:outline-none focus:ring-2 focus:ring-sky-500">
                    <option value="">Pilih unit kerja...</option>
                    @foreach($unitKerjas as $uk)
                    <option value="{{ $uk->id }}" @selected(old('unit_kerja_id', $asset->unit_kerja_id ?? '') === $uk->id)>
                        [{{ strtoupper($uk->tipe) }}] {{ $uk->singkatan ?? $uk->nama }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">
                    Status Lifecycle <span class="text-red-500">*</span>
                </label>
                <select name="lifecycle_status" required
                        class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm
                               focus:outline-none focus:ring-2 focus:ring-sky-500">
                    @foreach(['planning'=>'Perencanaan','construction'=>'Konstruksi','commissioning'=>'Uji Coba','operating'=>'Operasional','rehabilitating'=>'Rehabilitasi','decommissioned'=>'Nonaktif'] as $v=>$l)
                    <option value="{{ $v }}" @selected(old('lifecycle_status', $asset->lifecycle_status ?? 'operating') === $v)>
                        {{ $l }}
                    </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div>
            <label class="block text-xs font-medium text-slate-600 mb-1">Deskripsi</label>
            <textarea name="deskripsi" rows="2"
                      class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm resize-none
                             focus:outline-none focus:ring-2 focus:ring-sky-500">{{ old('deskripsi', $asset->deskripsi ?? '') }}</textarea>
        </div>
    </div>

    {{-- Lokasi --}}
    <div class="bg-white border border-slate-200 rounded-xl p-5 space-y-4">
        <h3 class="text-sm font-semibold text-slate-700 pb-2 border-b border-slate-100">Lokasi Administratif</h3>
        <div class="grid grid-cols-2 gap-4">
            @foreach([['kabupaten','Kabupaten'],['kecamatan','Kecamatan'],['desa','Desa'],['das','DAS'],['wilayah_sungai','Wilayah Sungai']] as [$field, $label])
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">{{ $label }}</label>
                <input type="text" name="{{ $field }}"
                       value="{{ old($field, $asset->{$field} ?? '') }}"
                       class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm
                              focus:outline-none focus:ring-2 focus:ring-sky-500">
            </div>
            @endforeach
        </div>
    </div>

    {{-- Data teknis --}}
    <div class="bg-white border border-slate-200 rounded-xl p-5 space-y-4">
        <h3 class="text-sm font-semibold text-slate-700 pb-2 border-b border-slate-100">Data Teknis</h3>
        <div class="grid grid-cols-3 gap-4">
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Tahun Bangun</label>
                <input type="number" name="tahun_bangun" min="1900" max="{{ date('Y') }}"
                       value="{{ old('tahun_bangun', $asset->tahun_bangun ?? '') }}"
                       class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm
                              focus:outline-none focus:ring-2 focus:ring-sky-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Tahun Desain</label>
                <input type="number" name="tahun_desain" min="1900" max="{{ date('Y') }}"
                       value="{{ old('tahun_desain', $asset->tahun_desain ?? '') }}"
                       class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm
                              focus:outline-none focus:ring-2 focus:ring-sky-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Umur Rencana (tahun)</label>
                <input type="number" name="umur_rencana_tahun" min="1" max="200"
                       value="{{ old('umur_rencana_tahun', $asset->umur_rencana_tahun ?? 50) }}"
                       class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm
                              focus:outline-none focus:ring-2 focus:ring-sky-500">
            </div>
        </div>
    </div>

    {{-- Valuasi --}}
    @can('asset.view_valuasi')
    <div class="bg-white border border-slate-200 rounded-xl p-5 space-y-4">
        <h3 class="text-sm font-semibold text-slate-700 pb-2 border-b border-slate-100">Valuasi Aset (BMN)</h3>
        <div class="grid grid-cols-3 gap-4">
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Nilai Perolehan (Rp)</label>
                <input type="number" name="nilai_perolehan" min="0"
                       value="{{ old('nilai_perolehan', $asset->nilai_perolehan ?? '') }}"
                       class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm
                              focus:outline-none focus:ring-2 focus:ring-sky-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Nilai Buku (Rp)</label>
                <input type="number" name="nilai_buku" min="0"
                       value="{{ old('nilai_buku', $asset->nilai_buku ?? '') }}"
                       class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm
                              focus:outline-none focus:ring-2 focus:ring-sky-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Tahun Perolehan</label>
                <input type="number" name="tahun_perolehan" min="1900" max="{{ date('Y') }}"
                       value="{{ old('tahun_perolehan', $asset->tahun_perolehan ?? '') }}"
                       class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm
                              focus:outline-none focus:ring-2 focus:ring-sky-500">
            </div>
        </div>
    </div>
    @endcan

    {{-- Foto --}}
    <div class="bg-white border border-slate-200 rounded-xl p-5">
        <h3 class="text-sm font-semibold text-slate-700 mb-3">Foto Utama</h3>
        @if($isEdit && $asset->foto_utama_path)
        <img src="{{ Storage::url($asset->foto_utama_path) }}"
             class="w-40 h-28 object-cover rounded-lg mb-3 border border-slate-200">
        @endif
        <input type="file" name="foto_utama" accept="image/*"
               class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4
                      file:rounded-lg file:border-0 file:text-sm file:font-medium
                      file:bg-sky-50 file:text-sky-700 hover:file:bg-sky-100 cursor-pointer">
        <p class="text-xs text-slate-400 mt-1.5">JPG/PNG, maks 5MB</p>
    </div>

    {{-- Tombol --}}
    <div class="flex items-center justify-between">
        <a href="{{ $isEdit ? route('assets.show', $asset) : route('assets.index') }}"
           class="text-sm text-slate-500 hover:text-slate-700">← Kembali</a>
        <button type="submit"
                class="px-5 py-2.5 bg-sky-600 text-white text-sm font-medium rounded-xl hover:bg-sky-700">
            {{ $isEdit ? 'Simpan Perubahan' : 'Tambah Aset' }}
        </button>
    </div>
</form>
</div>
@endsection
