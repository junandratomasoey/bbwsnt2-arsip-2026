@extends('layouts.app')
@section('title', isset($record) ? 'Edit Rekaman OP' : 'Input Rekaman OP')

@section('breadcrumb')
    <a href="{{ route('op.records.index') }}" class="text-slate-500 hover:text-slate-700 text-sm">Rekaman OP</a>
    <i class="ti ti-chevron-right text-slate-300 text-xs"></i>
    <span class="text-slate-800 font-medium text-sm">{{ isset($record) ? 'Edit' : 'Input' }}</span>
@endsection

@section('content')
@php $isEdit  = isset($record);
    $record = $record ?? null; @endphp
<div class="max-w-2xl">
<x-page-header :title="$isEdit ? 'Edit Rekaman OP' : 'Input Rekaman OP'" icon="ti-settings-2" />

<form method="POST"
      action="{{ $isEdit ? route('op.records.update', $record) : route('op.records.store') }}"
      enctype="multipart/form-data" class="space-y-5">
    @csrf
    @if($isEdit) @method('PUT') @endif

    <div class="bg-white border border-slate-200 rounded-xl p-5 space-y-4">
        <h3 class="text-sm font-semibold text-slate-700 pb-2 border-b border-slate-100">Informasi OP</h3>

        @if(!$isEdit)
        <div>
            <label class="block text-xs font-medium text-slate-600 mb-1">Aset <span class="text-red-500">*</span></label>
            <select name="asset_id" required
                    class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500">
                <option value="">Pilih aset...</option>
                @foreach($assets as $a)
                <option value="{{ $a->id }}" @selected(old('asset_id', $assetId ?? '') === $a->id)>
                    [{{ $a->asset_code }}] {{ $a->nama }}
                </option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-slate-600 mb-1">Satker <span class="text-red-500">*</span></label>
            <select name="unit_kerja_id" required
                    class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500">
                <option value="">Pilih satker...</option>
                @foreach($unitKerjas as $uk)
                <option value="{{ $uk->id }}" @selected(old('unit_kerja_id') === $uk->id)>{{ $uk->singkatan }}</option>
                @endforeach
            </select>
        </div>
        <div class="grid grid-cols-3 gap-4">
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Tahun <span class="text-red-500">*</span></label>
                <input type="number" name="periode_tahun" required min="2015" max="{{ now()->year + 1 }}"
                       value="{{ old('periode_tahun', now()->year) }}"
                       class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Bulan <span class="text-red-500">*</span></label>
                <select name="periode_bulan" required
                        class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500">
                    @foreach(['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Ags','Sep','Okt','Nov','Des'] as $i => $b)
                    <option value="{{ $i+1 }}" @selected(old('periode_bulan', now()->month) == $i+1)>{{ $b }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Jenis OP <span class="text-red-500">*</span></label>
                <select name="jenis_op" required
                        class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500">
                    @foreach(['rutin'=>'Rutin','berkala'=>'Berkala','darurat'=>'Darurat','rehabilitasi_minor'=>'Rehabilitasi Minor'] as $v=>$l)
                    <option value="{{ $v }}" @selected(old('jenis_op','rutin') === $v)>{{ $l }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        @endif

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Status <span class="text-red-500">*</span></label>
                <select name="status" required
                        class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500">
                    @foreach(['belum'=>'Belum','berjalan'=>'Berjalan','selesai'=>'Selesai','tidak_terlaksana'=>'Tidak Terlaksana'] as $v=>$l)
                    <option value="{{ $v }}" @selected(old('status', $record->status ?? 'belum') === $v)>{{ $l }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Realisasi (%) <span class="text-red-500">*</span></label>
                <input type="number" name="realisasi_pct" required min="0" max="100" step="0.1"
                       value="{{ old('realisasi_pct', $record?->realisasi_pct ?? 0) }}"
                       class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500">
            </div>
        </div>

        <div>
            <label class="block text-xs font-medium text-slate-600 mb-1">Tanggal Pelaksanaan</label>
            <input type="date" name="tgl_pelaksanaan"
                   value="{{ old('tgl_pelaksanaan', ($record?->tgl_pelaksanaan)?->format('Y-m-d') ?? '') }}"
                   class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500">
        </div>

        <div>
            <label class="block text-xs font-medium text-slate-600 mb-1">Tim OP</label>
            <input type="text" name="tim_op"
                   value="{{ old('tim_op', $record?->tim_op ?? '') }}"
                   placeholder="Nama-nama anggota tim"
                   class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500">
        </div>
    </div>

    <div class="bg-white border border-slate-200 rounded-xl p-5 space-y-4">
        <h3 class="text-sm font-semibold text-slate-700 pb-2 border-b border-slate-100">Kegiatan & Anggaran</h3>
        <div>
            <label class="block text-xs font-medium text-slate-600 mb-1">Kegiatan yang Dilakukan</label>
            <p class="text-xs text-slate-400 mb-1.5">Satu kegiatan per baris</p>
            <textarea name="kegiatan_text" rows="4" placeholder="Pembersihan saluran 500m&#10;Pelumasan 3 pintu air&#10;Pengukuran debit"
                      class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm resize-none focus:outline-none focus:ring-2 focus:ring-sky-500">{{ old('kegiatan_text', $isEdit && $record->kegiatan_dilakukan ? implode("\n", $record->kegiatan_dilakukan) : '') }}</textarea>
        </div>
        <div>
            <label class="block text-xs font-medium text-slate-600 mb-1">Kendala</label>
            <textarea name="kendala" rows="2"
                      class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm resize-none focus:outline-none focus:ring-2 focus:ring-sky-500">{{ old('kendala', $record?->kendala ?? '') }}</textarea>
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Anggaran (Rp)</label>
                <input type="number" name="anggaran" min="0"
                       value="{{ old('anggaran', $record?->anggaran ?? '') }}"
                       class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Realisasi Anggaran (Rp)</label>
                <input type="number" name="realisasi_anggaran" min="0"
                       value="{{ old('realisasi_anggaran', $record?->realisasi_anggaran ?? '') }}"
                       class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500">
            </div>
        </div>
        <div>
            <label class="block text-xs font-medium text-slate-600 mb-1">Foto Dokumentasi</label>
            <input type="file" name="foto[]" multiple accept="image/*"
                   class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4
                          file:rounded-lg file:border-0 file:text-sm file:bg-sky-50 file:text-sky-700
                          hover:file:bg-sky-100 cursor-pointer">
            <p class="text-xs text-slate-400 mt-1">Bisa pilih beberapa foto sekaligus, maks 5MB per file</p>
        </div>
    </div>

    <div class="flex items-center justify-between">
        <a href="{{ route('op.records.index') }}" class="text-sm text-slate-500 hover:text-slate-700">← Kembali</a>
        <button type="submit"
                class="px-5 py-2.5 bg-sky-600 text-white text-sm font-medium rounded-xl hover:bg-sky-700">
            {{ $isEdit ? 'Simpan Perubahan' : 'Simpan Rekaman OP' }}
        </button>
    </div>
</form>
</div>
@endsection
