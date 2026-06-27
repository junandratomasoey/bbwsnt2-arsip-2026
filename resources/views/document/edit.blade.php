@extends('layouts.app')
@section('title', isset($document) ? 'Edit Dokumen' : 'Upload Dokumen')

@section('breadcrumb')
    <a href="{{ route('documents.index') }}" class="text-slate-500 hover:text-slate-700 text-sm">Dokumen</a>
    <i class="ti ti-chevron-right text-slate-300 text-xs"></i>
    <span class="text-slate-800 font-medium text-sm">{{ isset($document) ? 'Edit' : 'Upload' }}</span>
@endsection

@section('content')
@php $isEdit  = isset($document);
    $document = $document ?? null; @endphp
<div class="max-w-2xl">
<x-page-header :title="$isEdit ? 'Edit Dokumen' : 'Upload Dokumen'" icon="ti-upload" />

<form method="POST"
      action="{{ $isEdit ? route('documents.update', $document) : route('documents.store') }}"
      enctype="multipart/form-data" class="space-y-5">
    @csrf
    @if($isEdit) @method('PUT') @endif

    {{-- Entity pre-fill jika dari halaman aset/proyek --}}
    @if($entity)
    <div class="bg-sky-50 border border-sky-100 rounded-xl px-4 py-3 flex items-center gap-2">
        <i class="ti ti-link text-sky-500"></i>
        <p class="text-sm text-sky-700">
            Dokumen ini akan dilampirkan ke: <strong>{{ $entity->nama ?? $entity->judul }}</strong>
        </p>
    </div>
    <input type="hidden" name="entity_type" value="{{ $entityType }}">
    <input type="hidden" name="entity_id" value="{{ $entityId }}">
    @endif

    <div class="bg-white border border-slate-200 rounded-xl p-5 space-y-4">
        <h3 class="text-sm font-semibold text-slate-700 pb-2 border-b border-slate-100">Identitas Dokumen</h3>
        <div>
            <label class="block text-xs font-medium text-slate-600 mb-1">Judul <span class="text-red-500">*</span></label>
            <input type="text" name="judul" required
                   value="{{ old('judul', $document?->judul ?? '') }}"
                   class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500">
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">No. Dokumen</label>
                <input type="text" name="doc_number"
                       value="{{ old('doc_number', $document?->doc_number ?? '') }}"
                       placeholder="Auto jika kosong"
                       class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-sky-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Jenis Dokumen <span class="text-red-500">*</span></label>
                <select name="document_type_id" required
                        class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500">
                    <option value="">Pilih jenis...</option>
                    @foreach($docTypes->groupBy('kategori') as $kategori => $types)
                    <optgroup label="{{ ucfirst($kategori) }}">
                        @foreach($types as $t)
                        <option value="{{ $t->id }}" @selected(old('document_type_id', $document?->document_type_id ?? '') === $t->id)>
                            {{ $t->nama }}
                        </option>
                        @endforeach
                    </optgroup>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Unit Kerja</label>
                <select name="unit_kerja_id"
                        class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500">
                    <option value="">Pilih unit kerja...</option>
                    @foreach($unitKerjas as $uk)
                    <option value="{{ $uk->id }}" @selected(old('unit_kerja_id', $document?->unit_kerja_id ?? '') === $uk->id)>
                        {{ $uk->singkatan }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Fase</label>
                <select name="entity_fase"
                        class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500">
                    @foreach(['umum'=>'Umum','before'=>'Before (Pra-konstruksi)','during'=>'During (Konstruksi)','after'=>'After (Pasca-konstruksi)','op'=>'OP'] as $v=>$l)
                    <option value="{{ $v }}" @selected(old('entity_fase', $document->entity_fase ?? 'umum') === $v)>{{ $l }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="grid grid-cols-3 gap-4">
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Klasifikasi <span class="text-red-500">*</span></label>
                <select name="klasifikasi" required
                        class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500">
                    @foreach(['biasa'=>'Biasa','terbatas'=>'Terbatas','rahasia'=>'Rahasia'] as $v=>$l)
                    <option value="{{ $v }}" @selected(old('klasifikasi', $document->klasifikasi ?? 'biasa') === $v)>{{ $l }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Tgl Dokumen</label>
                <input type="date" name="tgl_dokumen"
                       value="{{ old('tgl_dokumen', ($document?->tgl_dokumen)?->format('Y-m-d') ?? '') }}"
                       class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Tgl Kadaluwarsa</label>
                <input type="date" name="tgl_kedaluwarsa"
                       value="{{ old('tgl_kedaluwarsa', ($document?->tgl_kedaluwarsa)?->format('Y-m-d') ?? '') }}"
                       class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500">
            </div>
        </div>
        <div>
            <label class="block text-xs font-medium text-slate-600 mb-1">Tags</label>
            <input type="text" name="tags"
                   value="{{ old('tags', $isEdit && $document->tags ? implode(', ', $document->tags) : '') }}"
                   placeholder="kontrak, 2024, bendung — pisahkan dengan koma"
                   class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500">
        </div>
        <div>
            <label class="block text-xs font-medium text-slate-600 mb-1">Deskripsi</label>
            <textarea name="deskripsi" rows="2"
                      class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm resize-none focus:outline-none focus:ring-2 focus:ring-sky-500">{{ old('deskripsi', $document?->deskripsi ?? '') }}</textarea>
        </div>
    </div>

    {{-- Fisik --}}
    <div class="bg-white border border-slate-200 rounded-xl p-5 space-y-4">
        <h3 class="text-sm font-semibold text-slate-700 pb-2 border-b border-slate-100">Ketersediaan</h3>
        <div class="flex gap-6">
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" name="ada_fisik" value="1"
                       @checked(old('ada_fisik', $document->ada_fisik ?? false))
                       class="rounded border-slate-300 text-sky-600">
                <span class="text-sm text-slate-700">Ada dokumen fisik</span>
            </label>
        </div>
        <div>
            <label class="block text-xs font-medium text-slate-600 mb-1">Lokasi Fisik</label>
            <select name="physical_location_id"
                    class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500">
                <option value="">Pilih lokasi...</option>
                @foreach($locations as $loc)
                <option value="{{ $loc->id }}" @selected(old('physical_location_id', $document?->physical_location_id ?? '') === $loc->id)>
                    {{ $loc->kode_lokasi }} — {{ $loc->labelLengkap() }}
                </option>
                @endforeach
            </select>
        </div>
        @if(!$isEdit)
        <div>
            <label class="block text-xs font-medium text-slate-600 mb-1">Upload File Digital</label>
            <input type="file" name="file" accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png,.zip"
                   class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4
                          file:rounded-lg file:border-0 file:text-sm file:bg-sky-50 file:text-sky-700
                          hover:file:bg-sky-100 cursor-pointer">
            <p class="text-xs text-slate-400 mt-1">PDF, Word, Excel, Gambar, ZIP — maks 50MB</p>
        </div>
        @endif
    </div>

    <div class="flex items-center justify-between">
        <a href="{{ $isEdit ? route('documents.show', $document) : route('documents.index') }}"
           class="text-sm text-slate-500 hover:text-slate-700">← Kembali</a>
        <button type="submit"
                class="px-5 py-2.5 bg-sky-600 text-white text-sm font-medium rounded-xl hover:bg-sky-700">
            {{ $isEdit ? 'Simpan Perubahan' : 'Upload Dokumen' }}
        </button>
    </div>
</form>
</div>
@endsection
