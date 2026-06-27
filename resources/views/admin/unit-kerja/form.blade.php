{{-- resources/views/admin/unit-kerja/form.blade.php --}}
@extends('layouts.app')
@section('title', isset($unitKerja) ? 'Edit Unit Kerja' : 'Tambah Unit Kerja')

@section('breadcrumb')
    <a href="{{ route('superadmin.unit-kerja.index') }}" class="text-slate-500 hover:text-slate-700 text-sm">Unit Kerja</a>
    <i class="ti ti-chevron-right text-slate-300 text-xs"></i>
    <span class="text-slate-800 font-medium text-sm">{{ isset($unitKerja) ? 'Edit' : 'Tambah' }}</span>
@endsection

@section('content')
@php $isEdit  = isset($unitKerja);
    $unitKerja = $unitKerja ?? null; @endphp
<div class="max-w-xl">
<x-page-header :title="$isEdit ? 'Edit: ' . $unitKerja->namaLengkap() : 'Tambah Unit Kerja'" icon="ti-sitemap" />

<form method="POST"
      action="{{ $isEdit ? route('superadmin.unit-kerja.update', $unitKerja) : route('superadmin.unit-kerja.store') }}"
      class="space-y-5">
    @csrf
    @if($isEdit) @method('PUT') @endif

    <div class="bg-white border border-slate-200 rounded-xl p-5 space-y-4">

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Tipe <span class="text-red-500">*</span></label>
                <select name="tipe" required
                        class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500">
                    @foreach(['balai','bagian','bidang','satker','ppk'] as $t)
                    <option value="{{ $t }}" @selected(old('tipe', $unitKerja->tipe ?? $tipeAnak ?? '') === $t)>
                        {{ ucfirst($t) }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Kode <span class="text-red-500">*</span></label>
                <input type="text" name="kode" required
                       value="{{ old('kode', $unitKerja->kode ?? '') }}"
                       placeholder="BBWSNT2, BID-OP, SK-PJSA..."
                       class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-sky-500">
            </div>
        </div>

        <div>
            <label class="block text-xs font-medium text-slate-600 mb-1">Nama <span class="text-red-500">*</span></label>
            <input type="text" name="nama" required
                   value="{{ old('nama', $unitKerja->nama ?? '') }}"
                   class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500">
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Singkatan</label>
                <input type="text" name="singkatan"
                       value="{{ old('singkatan', $unitKerja->singkatan ?? '') }}"
                       class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Urutan</label>
                <input type="number" name="urutan" min="0"
                       value="{{ old('urutan', $unitKerja->urutan ?? 0) }}"
                       class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500">
            </div>
        </div>

        @if(isset($parentFixed))
        <div>
            <label class="block text-xs font-medium text-slate-600 mb-1">Induk</label>
            <div class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm bg-slate-50 text-slate-600">
                {{ $parentFixed->namaLengkap() }}
            </div>
            <input type="hidden" name="parent_id" value="{{ $parentFixed->id }}">
        </div>
        @elseif($parents->isNotEmpty())
        <div>
            <label class="block text-xs font-medium text-slate-600 mb-1">Unit Induk</label>
            <select name="parent_id"
                    class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500">
                <option value="">Tidak ada (root)</option>
                @foreach($parents as $p)
                <option value="{{ $p->id }}" @selected(old('parent_id', $unitKerja->parent_id ?? '') === $p->id)>
                    [{{ ucfirst($p->tipe) }}] {{ $p->namaLengkap() }}
                </option>
                @endforeach
            </select>
        </div>
        @endif

        <div class="pt-3 border-t border-slate-100 space-y-3">
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Kepala Unit</p>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs text-slate-500 mb-1">Nama Kepala</label>
                    <input type="text" name="kepala_nama"
                           value="{{ old('kepala_nama', $unitKerja->kepala_nama ?? '') }}"
                           class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500">
                </div>
                <div>
                    <label class="block text-xs text-slate-500 mb-1">NIP Kepala</label>
                    <input type="text" name="kepala_nip"
                           value="{{ old('kepala_nip', $unitKerja->kepala_nip ?? '') }}"
                           class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-sky-500">
                </div>
            </div>
            <div>
                <label class="block text-xs text-slate-500 mb-1">Email</label>
                <input type="email" name="email"
                       value="{{ old('email', $unitKerja->email ?? '') }}"
                       class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500">
            </div>
        </div>

        @if($isEdit)
        <div class="pt-3 border-t border-slate-100">
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" name="is_aktif" value="1"
                       @checked(old('is_aktif', $unitKerja->is_aktif ?? true))
                       class="rounded border-slate-300 text-sky-600">
                <span class="text-sm text-slate-700">Unit kerja aktif</span>
            </label>
        </div>
        @endif
    </div>

    <div class="flex items-center justify-between">
        <a href="{{ route('superadmin.unit-kerja.index') }}" class="text-sm text-slate-500 hover:text-slate-700">← Kembali</a>
        <button type="submit"
                class="px-5 py-2.5 bg-sky-600 text-white text-sm font-medium rounded-xl hover:bg-sky-700">
            {{ $isEdit ? 'Simpan Perubahan' : 'Tambah Unit Kerja' }}
        </button>
    </div>
</form>
</div>
@endsection
