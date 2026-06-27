@extends('layouts.app')
@section('title', 'Upload Versi Baru — ' . $document->judul)

@section('breadcrumb')
    <a href="{{ route('documents.index') }}" class="text-slate-500 hover:text-slate-700 text-sm">Dokumen</a>
    <i class="ti ti-chevron-right text-slate-300 text-xs"></i>
    <a href="{{ route('documents.show', $document) }}" class="text-slate-500 hover:text-slate-700 text-sm truncate max-w-40">
        {{ $document->judul }}
    </a>
    <i class="ti ti-chevron-right text-slate-300 text-xs"></i>
    <span class="text-slate-800 font-medium text-sm">Versi Baru</span>
@endsection

@section('content')
<div class="max-w-xl">
<x-page-header title="Upload Versi Baru" icon="ti-file-plus" />

{{-- Info dokumen lama --}}
<div class="bg-white border border-slate-200 rounded-xl p-4 mb-5 flex items-start gap-3">
    <div class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center flex-shrink-0">
        <i class="ti ti-file-description text-slate-500 text-lg"></i>
    </div>
    <div class="min-w-0 flex-1">
        <p class="text-sm font-semibold text-slate-800 truncate">{{ $document->judul }}</p>
        <p class="text-xs text-slate-500 mt-0.5">
            {{ $document->doc_number }} ·
            {{ $document->documentType?->nama }} ·
            Versi saat ini: <span class="font-semibold">{{ $document->versiLabel() }}</span>
        </p>
    </div>
    <span class="flex-shrink-0 text-xs px-2 py-0.5 rounded {{ $document->badgeStatus() }}">
        {{ ucfirst($document->status) }}
    </span>
</div>

{{-- Warning --}}
<div class="bg-amber-50 border border-amber-200 rounded-xl px-4 py-3 mb-5 flex items-start gap-2.5">
    <i class="ti ti-alert-triangle text-amber-500 flex-shrink-0 mt-0.5"></i>
    <div class="text-xs text-amber-700">
        <p class="font-semibold mb-1">Perhatian</p>
        <p>Mengunggah versi baru akan mengubah status dokumen ini menjadi
            <strong>Superseded</strong>. Versi baru akan berstatus <strong>Draft</strong>
            dan perlu disetujui kembali. Versi baru akan menjadi
            <strong>{{ $document->judul }} v{{ $document->versi_mayor }}.{{ $document->versi_minor + 1 }}</strong>.
        </p>
    </div>
</div>

<form method="POST"
      action="{{ route('documents.new-version', $document) }}"
      enctype="multipart/form-data"
      class="space-y-4">
    @csrf

    <div class="bg-white border border-slate-200 rounded-xl p-5 space-y-4">

        {{-- File upload --}}
        <div>
            <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wide mb-2">
                File Dokumen Baru <span class="text-red-500">*</span>
            </label>
            <div class="border-2 border-dashed border-slate-200 rounded-xl p-6 text-center
                        hover:border-sky-300 hover:bg-sky-50 transition-colors cursor-pointer"
                 onclick="document.getElementById('file-input').click()">
                <i class="ti ti-cloud-upload text-3xl text-slate-300 block mb-2"></i>
                <p class="text-sm font-medium text-slate-600" id="file-label">
                    Klik untuk pilih file atau drag & drop
                </p>
                <p class="text-xs text-slate-400 mt-1">PDF, DOC, DOCX, XLS, XLSX — maks 50MB</p>
            </div>
            <input type="file" id="file-input" name="file" required
                   accept=".pdf,.doc,.docx,.xls,.xlsx,.dwg,.zip"
                   class="hidden"
                   onchange="document.getElementById('file-label').textContent = this.files[0]?.name || 'Pilih file'">
            @error('file')
            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- Keterangan perubahan --}}
        <div>
            <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wide mb-1.5">
                Keterangan Perubahan
            </label>
            <textarea name="keterangan" rows="3"
                      placeholder="Jelaskan apa yang berubah dari versi sebelumnya..."
                      class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm resize-none
                             focus:outline-none focus:ring-2 focus:ring-sky-500">{{ old('keterangan') }}</textarea>
        </div>
    </div>

    <div class="flex items-center justify-between">
        <a href="{{ route('documents.show', $document) }}"
           class="text-sm text-slate-500 hover:text-slate-700">← Kembali</a>
        <button type="submit"
                class="inline-flex items-center gap-2 px-5 py-2.5 text-white text-sm
                       font-semibold rounded-xl transition-all hover:opacity-90"
                style="background: #003366"
                onclick="return confirm('Upload versi baru? Dokumen lama akan menjadi Superseded.')">
            <i class="ti ti-upload"></i>
            Upload Versi Baru
        </button>
    </div>
</form>
</div>
@endsection
