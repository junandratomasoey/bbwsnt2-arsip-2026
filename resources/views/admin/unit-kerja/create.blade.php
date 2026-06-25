@extends('layouts.app')
@section('title', isset($unitKerja) ? 'Edit Unit Kerja' : 'Tambah Unit Kerja')

@section('breadcrumb')
    <a href="{{ route('superadmin.unit-kerja.index') }}" class="text-slate-500 hover:text-slate-700">Unit Kerja</a>
    <i class="ti ti-chevron-right text-slate-300 text-xs"></i>
    <span class="text-slate-800 font-medium">{{ isset($unitKerja) ? 'Edit' : 'Tambah' }}</span>
@endsection

@section('content')
@php $isEdit = isset($unitKerja); @endphp

<div class="max-w-2xl">
<x-page-header
    :title="$isEdit ? 'Edit ' . $unitKerja->labelTipe() . ': ' . $unitKerja->nama : 'Tambah Unit Kerja'"
    :desc="$isEdit ? 'Perbarui data ' . $unitKerja->breadcrumb() : 'Tambah balai, bagian, bidang, satker, atau PPK baru'" />

<form method="POST"
      action="{{ $isEdit ? route('superadmin.unit-kerja.update', $unitKerja) : route('superadmin.unit-kerja.store') }}"
      class="bg-white border border-slate-200 rounded-xl p-6 space-y-5">
    @csrf
    @if($isEdit) @method('PUT') @endif

    {{-- Tipe & Parent --}}
    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1.5">
                Tipe <span class="text-red-500">*</span>
            </label>
            <select name="tipe" id="tipe-select" required
                    class="w-full border border-slate-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500
                           @error('tipe') border-red-400 @enderror"
                    onchange="filterParent(this.value)">
                @foreach($tipeList as $t)
                <option value="{{ $t }}"
                    @selected(old('tipe', $tipeAnak ?? ($isEdit ? $unitKerja->tipe : '')) === $t)>
                    {{ ucfirst($t) }}
                </option>
                @endforeach
            </select>
            @error('tipe')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1.5">Induk Unit Kerja</label>
            <select name="parent_id"
                    class="w-full border border-slate-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500
                           @error('parent_id') border-red-400 @enderror"
                    {{ isset($parentFixed) ? 'disabled' : '' }}>
                <option value="">— Tidak ada (root) —</option>
                @foreach($parents as $p)
                <option value="{{ $p->id }}"
                        data-tipe="{{ $p->tipe }}"
                        @selected(old('parent_id', $isEdit ? $unitKerja->parent_id : ($parentFixed->id ?? '')) == $p->id)>
                    [{{ strtoupper($p->tipe) }}] {{ $p->namaLengkap() }}
                </option>
                @endforeach
            </select>
            @isset($parentFixed)
            <input type="hidden" name="parent_id" value="{{ $parentFixed->id }}">
            <p class="mt-1 text-xs text-slate-400">Di bawah: <strong>{{ $parentFixed->namaLengkap() }}</strong></p>
            @endisset
            @error('parent_id')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
        </div>
    </div>

    {{-- Nama & Singkatan --}}
    <div class="grid grid-cols-2 gap-4">
        <div class="col-span-2 sm:col-span-1">
            <label class="block text-sm font-medium text-slate-700 mb-1.5">
                Nama <span class="text-red-500">*</span>
            </label>
            <input type="text" name="nama" value="{{ old('nama', $isEdit ? $unitKerja->nama : '') }}"
                   placeholder="Contoh: Bendungan I" required
                   class="w-full border border-slate-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500
                          @error('nama') border-red-400 @enderror">
            @error('nama')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1.5">Singkatan</label>
            <input type="text" name="singkatan" value="{{ old('singkatan', $isEdit ? $unitKerja->singkatan : '') }}"
                   placeholder="Contoh: Satker Bend. I"
                   class="w-full border border-slate-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500">
        </div>
    </div>

    {{-- Kode & Urutan --}}
    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1.5">
                Kode <span class="text-red-500">*</span>
            </label>
            <input type="text" name="kode" value="{{ old('kode', $isEdit ? $unitKerja->kode : '') }}"
                   placeholder="Contoh: SATKER-BEND-I" required
                   class="w-full border border-slate-200 rounded-lg px-3 py-2.5 text-sm font-mono uppercase focus:outline-none focus:ring-2 focus:ring-sky-500
                          @error('kode') border-red-400 @enderror">
            @error('kode')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1.5">Urutan tampil</label>
            <input type="number" name="urutan" value="{{ old('urutan', $isEdit ? $unitKerja->urutan : 0) }}"
                   min="0" class="w-full border border-slate-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500">
        </div>
    </div>

    {{-- Kepala --}}
    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1.5">Nama Kepala</label>
            <input type="text" name="kepala" value="{{ old('kepala', $isEdit ? $unitKerja->kepala : '') }}"
                   class="w-full border border-slate-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500">
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1.5">NIP Kepala</label>
            <input type="text" name="nip_kepala" value="{{ old('nip_kepala', $isEdit ? $unitKerja->nip_kepala : '') }}"
                   class="w-full border border-slate-200 rounded-lg px-3 py-2.5 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-sky-500">
        </div>
    </div>

    {{-- Kontak --}}
    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1.5">Telepon</label>
            <input type="text" name="telp" value="{{ old('telp', $isEdit ? $unitKerja->telp : '') }}"
                   class="w-full border border-slate-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500">
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1.5">Email</label>
            <input type="email" name="email" value="{{ old('email', $isEdit ? $unitKerja->email : '') }}"
                   class="w-full border border-slate-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500">
        </div>
    </div>

    {{-- Alamat --}}
    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1.5">Alamat</label>
        <textarea name="alamat" rows="2"
                  class="w-full border border-slate-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500 resize-none">{{ old('alamat', $isEdit ? $unitKerja->alamat : '') }}</textarea>
    </div>

    {{-- Tupoksi --}}
    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1.5">Tugas Pokok & Fungsi</label>
        <textarea name="tupoksi" rows="3"
                  class="w-full border border-slate-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500 resize-none">{{ old('tupoksi', $isEdit ? $unitKerja->tupoksi : '') }}</textarea>
    </div>

    @if($isEdit)
    <div class="flex items-center gap-2">
        <input type="checkbox" name="is_aktif" id="is_aktif" value="1"
               @checked(old('is_aktif', $unitKerja->is_aktif))
               class="w-4 h-4 rounded border-slate-300 text-sky-600 focus:ring-sky-500">
        <label for="is_aktif" class="text-sm text-slate-700">Unit kerja aktif</label>
    </div>
    @endif

    {{-- Tombol --}}
    <div class="flex items-center justify-between pt-2 border-t border-slate-100">
        <a href="{{ route('superadmin.unit-kerja.index') }}"
           class="text-sm text-slate-500 hover:text-slate-700">← Kembali</a>
        <button type="submit"
                class="px-5 py-2.5 bg-sky-600 text-white text-sm font-medium rounded-xl hover:bg-sky-700 transition-colors">
            {{ $isEdit ? 'Simpan Perubahan' : 'Tambah Unit Kerja' }}
        </button>
    </div>
</form>
</div>

<script>
// Filter opsi parent berdasarkan tipe yang dipilih
const parentRules = {
    balai:  [],
    bagian: ['balai'],
    bidang: ['balai'],
    satker: ['balai','bagian','bidang'],
    ppk:    ['satker'],
};
function filterParent(tipe) {
    const sel = document.querySelector('select[name="parent_id"]');
    if (!sel) return;
    const boleh = parentRules[tipe] || [];
    sel.querySelectorAll('option[data-tipe]').forEach(opt => {
        opt.hidden = boleh.length > 0 && !boleh.includes(opt.dataset.tipe);
    });
    // Reset jika pilihan saat ini tidak valid
    const cur = sel.options[sel.selectedIndex];
    if (cur && cur.dataset.tipe && !boleh.includes(cur.dataset.tipe)) {
        sel.value = '';
    }
    // Balai = tidak perlu parent
    const emptyOpt = sel.querySelector('option[value=""]');
    if (emptyOpt) emptyOpt.hidden = tipe !== 'balai' && false;
}
// Jalankan saat halaman load
document.addEventListener('DOMContentLoaded', () => {
    const sel = document.getElementById('tipe-select');
    if (sel) filterParent(sel.value);
});
</script>
@endsection
