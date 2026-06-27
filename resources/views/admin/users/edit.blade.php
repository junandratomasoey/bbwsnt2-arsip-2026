@extends('layouts.app')
@section('title', 'Tambah Pengguna')
@section('breadcrumb')
    <a href="{{ route('superadmin.users.index') }}" class="text-slate-500 hover:text-slate-700 text-sm">Pengguna</a>
    <i class="ti ti-chevron-right text-slate-300 text-xs"></i>
    <span class="text-slate-800 font-medium text-sm">{{ isset($user) ? 'Edit' : 'Tambah' }}</span>
@endsection
@section('content')
@php $isEdit  = isset($user);
    $user = $user ?? null; @endphp
<div class="max-w-xl">
<x-page-header :title="$isEdit ? 'Edit: ' . ($user?->name ?? '') : 'Tambah Pengguna'" icon="ti-user-plus" />
<form method="POST" action="{{ $isEdit ? route('superadmin.users.update', $user) : route('superadmin.users.store') }}" class="space-y-5">
    @csrf @if($isEdit) @method('PUT') @endif
    <div class="bg-white border border-slate-200 rounded-xl p-5 space-y-4">
        <div class="grid grid-cols-2 gap-4">
            <div><label class="block text-xs font-medium text-slate-600 mb-1">Nama Lengkap <span class="text-red-500">*</span></label>
                <input type="text" name="name" required value="{{ old('name', $user?->name ?? '') }}"
                       class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500">
            </div>
            <div><label class="block text-xs font-medium text-slate-600 mb-1">NIP</label>
                <input type="text" name="nip" value="{{ old('nip', $user?->nip ?? '') }}"
                       class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-sky-500">
            </div>
        </div>
        <div><label class="block text-xs font-medium text-slate-600 mb-1">Email <span class="text-red-500">*</span></label>
            <input type="email" name="email" required value="{{ old('email', $user?->email ?? '') }}"
                   class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500">
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div><label class="block text-xs font-medium text-slate-600 mb-1">Jabatan</label>
                <input type="text" name="jabatan_struktural" value="{{ old('jabatan_struktural', $user?->jabatan_struktural ?? '') }}"
                       class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500">
            </div>
            <div><label class="block text-xs font-medium text-slate-600 mb-1">No. HP</label>
                <input type="text" name="no_hp" value="{{ old('no_hp', $user?->no_hp ?? '') }}"
                       class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500">
            </div>
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div><label class="block text-xs font-medium text-slate-600 mb-1">Unit Kerja</label>
                <select name="unit_kerja_id" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500">
                    <option value="">Pilih unit kerja...</option>
                    @foreach($unitKerjas as $uk)
                    <option value="{{ $uk->id }}" @selected(old('unit_kerja_id', $user?->unit_kerja_id ?? '') === $uk->id)>
                        [{{ strtoupper($uk->tipe) }}] {{ $uk->singkatan ?? $uk->nama }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div><label class="block text-xs font-medium text-slate-600 mb-1">Role <span class="text-red-500">*</span></label>
                <select name="role" required class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500">
                    <option value="">Pilih role...</option>
                    @foreach($roles as $r)
                    <option value="{{ $r->name }}" @selected(old('role', $user?->roles?->first()?->name ?? '') === $r->name)>
                        {{ ucfirst(str_replace('_',' ',$r->name)) }}
                    </option>
                    @endforeach
                </select>
            </div>
        </div>
        @if($isEdit)
        <div><label class="block text-xs font-medium text-slate-600 mb-1">Status</label>
            <select name="status" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500">
                @foreach(['aktif'=>'Aktif','nonaktif'=>'Nonaktif'] as $v=>$l)
                <option value="{{ $v }}" @selected(old('status', $user?->status) === $v)>{{ $l }}</option>
                @endforeach
            </select>
        </div>
        @endif
        <div class="pt-3 border-t border-slate-100">
            <p class="text-xs font-medium text-slate-600 mb-3">Password {{ $isEdit ? '(kosongkan jika tidak ganti)' : '' }}</p>
            <div class="grid grid-cols-2 gap-3">
                <div><label class="block text-xs text-slate-500 mb-1">Password {{ !$isEdit ? '*' : '' }}</label>
                    <input type="password" name="password" {{ !$isEdit ? 'required' : '' }}
                           class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500">
                </div>
                <div><label class="block text-xs text-slate-500 mb-1">Konfirmasi {{ !$isEdit ? '*' : '' }}</label>
                    <input type="password" name="password_confirmation"
                           class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500">
                </div>
            </div>
        </div>
    </div>
    <div class="flex items-center justify-between">
        <a href="{{ route('superadmin.users.index') }}" class="text-sm text-slate-500 hover:text-slate-700">← Kembali</a>
        <button type="submit" class="px-5 py-2.5 bg-sky-600 text-white text-sm font-medium rounded-xl hover:bg-sky-700">
            {{ $isEdit ? 'Simpan Perubahan' : 'Tambah Pengguna' }}
        </button>
    </div>
</form>
</div>
@endsection
