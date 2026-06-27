@extends('layouts.app')
@section('title', 'Input Kondisi Aset')
@section('breadcrumb')
    <a href="{{ route('assets.show', $asset) }}" class="text-slate-500 hover:text-slate-700 text-sm">{{ $asset->nama }}</a>
    <i class="ti ti-chevron-right text-slate-300 text-xs"></i>
    <span class="text-slate-800 font-medium text-sm">{{ isset($condition) ? 'Edit' : 'Input' }} Kondisi</span>
@endsection
@section('content')
@php
    $isEdit    = isset($condition);
    $condition = $condition ?? null;
@endphp
<div class="max-w-2xl">
<x-page-header :title="($isEdit ? 'Edit' : 'Input') . ' Kondisi — ' . $asset->nama" icon="ti-clipboard-check" />
<form method="POST"
      action="{{ $isEdit ? route('assets.conditions.update', [$asset, $condition]) : route('assets.conditions.store', $asset) }}"
      enctype="multipart/form-data" class="space-y-5">
    @csrf @if($isEdit) @method('PUT') @endif
    <div class="bg-white border border-slate-200 rounded-xl p-5 space-y-4">
        <div class="grid grid-cols-2 gap-4">
            <div><label class="block text-xs font-medium text-slate-600 mb-1">Tanggal Inspeksi <span class="text-red-500">*</span></label>
                <input type="date" name="tgl_inspeksi" required
                       value="{{ old('tgl_inspeksi', $condition?->tgl_inspeksi?->format('Y-m-d') ?? today()->format('Y-m-d')) }}"
                       class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500">
            </div>
            <div><label class="block text-xs font-medium text-slate-600 mb-1">Jenis Inspeksi <span class="text-red-500">*</span></label>
                <select name="jenis_inspeksi" required class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500">
                    @foreach(['rutin'=>'Rutin','tahunan'=>'Tahunan','khusus'=>'Khusus','amdal'=>'AMDAL'] as $v=>$l)
                    <option value="{{ $v }}" @selected(old('jenis_inspeksi', $condition?->jenis_inspeksi ?? 'rutin') === $v)>{{ $l }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div><label class="block text-xs font-medium text-slate-600 mb-1">Kondisi <span class="text-red-500">*</span></label>
                <select name="kondisi" required class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500">
                    @foreach(['A'=>'A — Baik (>80%)','B'=>'B — Sedang (60-80%)','C'=>'C — Rusak Ringan (40-60%)','D'=>'D — Rusak Berat (<40%)'] as $v=>$l)
                    <option value="{{ $v }}" @selected(old('kondisi', $condition?->kondisi ?? '') === $v)>{{ $l }}</option>
                    @endforeach
                </select>
            </div>
            <div><label class="block text-xs font-medium text-slate-600 mb-1">RCI Score (0-100)</label>
                <input type="number" name="rci_score" min="0" max="100" step="0.1"
                       value="{{ old('rci_score', $condition?->rci_score ?? '') }}"
                       class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500">
            </div>
        </div>
        <div><label class="block text-xs font-medium text-slate-600 mb-1">Temuan</label>
            <textarea name="temuan" rows="3" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm resize-none focus:outline-none focus:ring-2 focus:ring-sky-500">{{ old('temuan', $condition?->temuan ?? '') }}</textarea>
        </div>
        <div><label class="block text-xs font-medium text-slate-600 mb-1">Rekomendasi</label>
            <textarea name="rekomendasi" rows="2" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm resize-none focus:outline-none focus:ring-2 focus:ring-sky-500">{{ old('rekomendasi', $condition?->rekomendasi ?? '') }}</textarea>
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div><label class="block text-xs font-medium text-slate-600 mb-1">Urgensi Tindak Lanjut</label>
                <select name="urgensi_tindak_lanjut" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500">
                    <option value="">Tidak perlu</option>
                    @foreach(['segera'=>'Segera (<1 bln)','mendesak'=>'Mendesak (1-3 bln)','rutin'=>'Rutin (3-12 bln)','jangka_panjang'=>'Jangka Panjang (>1 thn)'] as $v=>$l)
                    <option value="{{ $v }}" @selected(old('urgensi_tindak_lanjut', $condition?->urgensi_tindak_lanjut ?? '') === $v)>{{ $l }}</option>
                    @endforeach
                </select>
            </div>
            <div><label class="block text-xs font-medium text-slate-600 mb-1">Estimasi Biaya (Rp)</label>
                <input type="number" name="estimasi_biaya" min="0"
                       value="{{ old('estimasi_biaya', $condition?->estimasi_biaya ?? '') }}"
                       class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500">
            </div>
        </div>
        <div><label class="block text-xs font-medium text-slate-600 mb-1">Tim Inspeksi</label>
            <input type="text" name="tim_inspeksi" placeholder="Nama anggota tim"
                   value="{{ old('tim_inspeksi', $condition?->tim_inspeksi ?? '') }}"
                   class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500">
        </div>
        <div><label class="block text-xs font-medium text-slate-600 mb-1">Foto Lapangan</label>
            <input type="file" name="foto[]" multiple accept="image/*"
                   class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:bg-sky-50 file:text-sky-700 hover:file:bg-sky-100 cursor-pointer">
        </div>
    </div>
    <div class="flex items-center justify-between">
        <a href="{{ route('assets.show', $asset) }}" class="text-sm text-slate-500 hover:text-slate-700">← Kembali</a>
        <button type="submit" class="px-5 py-2.5 bg-sky-600 text-white text-sm font-medium rounded-xl hover:bg-sky-700">
            {{ $isEdit ? 'Simpan Perubahan' : 'Simpan Data Kondisi' }}
        </button>
    </div>
</form>
</div>
@endsection
