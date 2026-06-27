{{-- resources/views/project/form.blade.php --}}
@extends('layouts.app')
@section('title', isset($project) ? 'Edit Proyek' : 'Tambah Proyek')

@section('breadcrumb')
    <a href="{{ route('projects.index') }}" class="text-slate-500 hover:text-slate-700 text-sm">Proyek</a>
    <i class="ti ti-chevron-right text-slate-300 text-xs"></i>
    <span class="text-slate-800 font-medium text-sm">{{ isset($project) ? 'Edit' : 'Tambah' }}</span>
@endsection

@section('content')
@php
    $isEdit  = isset($project);
    $project = $project ?? null;
@endphp
<div class="max-w-3xl">
<x-page-header :title="$isEdit ? 'Edit: ' . ($project?->nama ?? '') : 'Tambah Proyek'" icon="ti-timeline" />

<form method="POST"
      action="{{ $isEdit ? route('projects.update', $project) : route('projects.store') }}"
      class="space-y-5">
    @csrf
    @if($isEdit) @method('PUT') @endif

    {{-- Identitas proyek --}}
    <div class="bg-white border border-slate-200 rounded-xl p-5 space-y-4">
        <h3 class="text-sm font-semibold text-slate-700 pb-2 border-b border-slate-100">Identitas Proyek</h3>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Kode Proyek</label>
                <input type="text" name="project_code"
                       value="{{ old('project_code', $project?->project_code ?? '') }}"
                       placeholder="Auto-generate jika kosong"
                       class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-sky-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Jenis <span class="text-red-500">*</span></label>
                <select name="jenis" required class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500">
                    @foreach($jenisOptions as $j)
                    <option value="{{ $j }}" @selected(old('jenis', $project?->jenis ?? '') === $j)>{{ ucfirst(str_replace('_',' ',$j)) }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div>
            <label class="block text-xs font-medium text-slate-600 mb-1">Nama Proyek <span class="text-red-500">*</span></label>
            <input type="text" name="nama" required value="{{ old('nama', $project?->nama ?? '') }}"
                   class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500">
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Satker <span class="text-red-500">*</span></label>
                <select name="unit_kerja_id" required class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500">
                    <option value="">Pilih satker...</option>
                    @foreach($unitKerjas->where('tipe','satker') as $uk)
                    <option value="{{ $uk->id }}" @selected(old('unit_kerja_id', $project?->unit_kerja_id ?? '') === $uk->id)>[Satker] {{ $uk->singkatan }}</option>
                    @endforeach
                    @foreach($unitKerjas->where('tipe','ppk') as $uk)
                    <option value="{{ $uk->id }}" @selected(old('unit_kerja_id', $project?->unit_kerja_id ?? '') === $uk->id)>[PPK] {{ $uk->singkatan }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Aset Terkait</label>
                <select name="asset_id" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500">
                    <option value="">Tidak terkait aset</option>
                    @foreach($assets as $a)
                    <option value="{{ $a->id }}" @selected(old('asset_id', $project?->asset_id ?? '') === $a->id)>{{ $a->nama }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Fase <span class="text-red-500">*</span></label>
                <select name="lifecycle_phase" required class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500">
                    @foreach(['perencanaan'=>'Perencanaan','pengadaan'=>'Pengadaan','pelaksanaan'=>'Pelaksanaan','serah_terima_1'=>'PHO','pemeliharaan'=>'Pemeliharaan','serah_terima_2'=>'FHO','selesai'=>'Selesai','dibatalkan'=>'Dibatalkan'] as $v=>$l)
                    <option value="{{ $v }}" @selected(old('lifecycle_phase', $project->lifecycle_phase ?? 'perencanaan') === $v)>{{ $l }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Tahun Anggaran <span class="text-red-500">*</span></label>
                <input type="number" name="tahun_anggaran" required min="2000" max="{{ now()->year + 2 }}"
                       value="{{ old('tahun_anggaran', $project?->tahun_anggaran ?? now()->year) }}"
                       class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500">
            </div>
        </div>
    </div>

    {{-- Kontrak --}}
    <div class="bg-white border border-slate-200 rounded-xl p-5 space-y-4">
        <h3 class="text-sm font-semibold text-slate-700 pb-2 border-b border-slate-100">Informasi Kontrak</h3>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">No. Kontrak</label>
                <input type="text" name="no_kontrak" value="{{ old('no_kontrak', $project?->no_kontrak ?? '') }}"
                       class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Sumber Dana</label>
                <input type="text" name="sumber_dana" value="{{ old('sumber_dana', $project?->sumber_dana ?? '') }}"
                       placeholder="APBN, APBD, Hibah..."
                       class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Kontraktor</label>
                <input type="text" name="kontraktor" value="{{ old('kontraktor', $project?->kontraktor ?? '') }}"
                       class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Nilai Kontrak (Rp)</label>
                <input type="number" name="nilai_kontrak" min="0" value="{{ old('nilai_kontrak', $project?->nilai_kontrak ?? '') }}"
                       class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Tanggal Mulai</label>
                <input type="date" name="tgl_mulai_rencana"
                       value="{{ old('tgl_mulai_rencana', ($project?->tgl_mulai_rencana)?->format('Y-m-d') ?? '') }}"
                       class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Tanggal Selesai</label>
                <input type="date" name="tgl_selesai_rencana"
                       value="{{ old('tgl_selesai_rencana', ($project?->tgl_selesai_rencana)?->format('Y-m-d') ?? '') }}"
                       class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500">
            </div>
        </div>
    </div>

    {{-- Realisasi --}}
    <div class="bg-white border border-slate-200 rounded-xl p-5 space-y-4">
        <h3 class="text-sm font-semibold text-slate-700 pb-2 border-b border-slate-100">Realisasi Terkini</h3>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Realisasi Fisik (%)</label>
                <input type="number" name="realisasi_fisik_pct" min="0" max="100" step="0.1"
                       value="{{ old('realisasi_fisik_pct', $project?->realisasi_fisik_pct ?? 0) }}"
                       class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Realisasi Keuangan (%)</label>
                <input type="number" name="realisasi_keuangan_pct" min="0" max="100" step="0.1"
                       value="{{ old('realisasi_keuangan_pct', $project?->realisasi_keuangan_pct ?? 0) }}"
                       class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500">
            </div>
        </div>
    </div>

    <div class="flex items-center justify-between">
        <a href="{{ $isEdit ? route('projects.show', $project) : route('projects.index') }}"
           class="text-sm text-slate-500 hover:text-slate-700">← Kembali</a>
        <button type="submit"
                class="px-5 py-2.5 bg-sky-600 text-white text-sm font-medium rounded-xl hover:bg-sky-700">
            {{ $isEdit ? 'Simpan Perubahan' : 'Tambah Proyek' }}
        </button>
    </div>
</form>
</div>
@endsection
