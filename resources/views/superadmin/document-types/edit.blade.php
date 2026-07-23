@extends('layouts.app')
@section('title', isset($documentType) ? 'Edit Jenis Dokumen' : 'Tambah Jenis Dokumen')
@section('breadcrumb')
    <a href="{{ route('superadmin.document-types.index') }}" class="text-slate-500 hover:text-slate-700 text-sm">Jenis Dokumen</a>
    <i class="ti ti-chevron-right text-slate-300 text-xs"></i>
    <span class="text-slate-800 font-medium text-sm">{{ isset($documentType) ? 'Edit' : 'Tambah' }}</span>
@endsection
@section('content')
@php $isEdit = isset($documentType); $documentType = $documentType ?? null; @endphp
<div class="max-w-xl">
<x-page-header :title="$isEdit ? 'Edit: ' . ($documentType?->nama ?? '') : 'Tambah Jenis Dokumen'" icon="ti-file-description" />

<form method="POST"
      action="{{ $isEdit ? route('superadmin.document-types.update', $documentType) : route('superadmin.document-types.store') }}"
      class="space-y-5">
    @csrf @if($isEdit) @method('PUT') @endif

    <div class="bg-white border border-slate-200 rounded-xl p-5 space-y-4">
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wide mb-1.5">
                    Kode <span class="text-red-500">*</span>
                </label>
                <input type="text" name="kode" required
                       value="{{ old('kode', $documentType?->kode ?? '') }}"
                       placeholder="KTR, GBR, GABT..."
                       class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm font-mono uppercase
                              focus:outline-none focus:ring-2 focus:ring-sky-500
                              @error('kode') border-red-400 @enderror">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wide mb-1.5">
                    Kategori <span class="text-red-500">*</span>
                </label>
                <input type="text" name="kategori" required list="kat-list"
                       value="{{ old('kategori', $documentType?->kategori ?? '') }}"
                       class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm
                              focus:outline-none focus:ring-2 focus:ring-sky-500">
                <datalist id="kat-list">
                    <option value="teknis">
                    <option value="administrasi">
                    <option value="laporan">
                    <option value="perencanaan">
                    <option value="hukum">
                    <option value="keuangan">
                </datalist>
            </div>
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wide mb-1.5">
                Nama Jenis Dokumen <span class="text-red-500">*</span>
            </label>
            <input type="text" name="nama" required
                   value="{{ old('nama', $documentType?->nama ?? '') }}"
                   placeholder="Gambar As-Built, Kontrak, Laporan Akhir..."
                   class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm
                          focus:outline-none focus:ring-2 focus:ring-sky-500">
        </div>

        <div class="grid grid-cols-3 gap-4">
            <div>
                <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wide mb-1.5">
                    Retensi Aktif (thn) <span class="text-red-500">*</span>
                </label>
                <input type="number" name="retensi_aktif_tahun" required min="1"
                       value="{{ old('retensi_aktif_tahun', $documentType?->retensi_aktif_tahun ?? 5) }}"
                       class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm
                              focus:outline-none focus:ring-2 focus:ring-sky-500">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wide mb-1.5">
                    Retensi Inaktif (thn) <span class="text-red-500">*</span>
                </label>
                <input type="number" name="retensi_inaktif_tahun" required min="1"
                       value="{{ old('retensi_inaktif_tahun', $documentType?->retensi_inaktif_tahun ?? 5) }}"
                       class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm
                              focus:outline-none focus:ring-2 focus:ring-sky-500">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wide mb-1.5">
                    Nasib Akhir <span class="text-red-500">*</span>
                </label>
                <select name="nasib_akhir" required
                        class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm
                               focus:outline-none focus:ring-2 focus:ring-sky-500">
                    @foreach(['permanen'=>'Permanen','musnah'=>'Musnah','sampling'=>'Sampling'] as $v=>$l)
                    <option value="{{ $v }}" @selected(old('nasib_akhir', $documentType?->nasib_akhir) === $v)>{{ $l }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        @if($isEdit)
        <label class="flex items-center gap-2 cursor-pointer">
            <input type="checkbox" name="is_aktif" value="1"
                   @checked(old('is_aktif', $documentType?->is_aktif ?? true))
                   class="rounded border-slate-300 text-sky-600">
            <span class="text-sm text-slate-700">Jenis dokumen aktif</span>
        </label>
        @endif
    </div>

    <div class="flex items-center justify-between">
        <a href="{{ route('superadmin.document-types.index') }}" class="text-sm text-slate-500 hover:text-slate-700">← Kembali</a>
        <button type="submit"
                class="px-5 py-2.5 text-white text-sm font-medium rounded-xl hover:opacity-90"
                style="background:#003366">
            {{ $isEdit ? 'Simpan Perubahan' : 'Tambah Jenis Dokumen' }}
        </button>
    </div>
</form>
</div>
@endsection
