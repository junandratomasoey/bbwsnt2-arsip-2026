{{-- resources/views/superadmin/system-config/edit.blade.php --}}
@extends('layouts.app')
@section('title', 'Edit Konfigurasi')

@section('breadcrumb')
    <a href="{{ route('superadmin.system-config.index') }}" class="text-slate-500 hover:text-slate-700 text-sm">Konfigurasi</a>
    <i class="ti ti-chevron-right text-slate-300 text-xs"></i>
    <span class="text-slate-800 font-medium text-sm">Edit</span>
@endsection

@section('content')
<div class="max-w-lg">
<x-page-header :title="'Edit: ' . $systemConfig->label" icon="ti-settings" />

<form method="POST" action="{{ route('superadmin.system-config.update', $systemConfig) }}" class="space-y-4">
    @csrf @method('PUT')

    <div class="bg-white border border-slate-200 rounded-xl p-5 space-y-4">
        <div class="bg-slate-50 rounded-lg p-3">
            <p class="text-xs text-slate-500 font-mono">{{ $systemConfig->group }}.{{ $systemConfig->key }}</p>
            @if($systemConfig->deskripsi)
            <p class="text-xs text-slate-500 mt-1">{{ $systemConfig->deskripsi }}</p>
            @endif
        </div>

        <div>
            <label class="block text-xs font-medium text-slate-600 mb-1">
                Nilai <span class="text-xs text-slate-400">(tipe: {{ $systemConfig->tipe }})</span>
            </label>
            @if($systemConfig->tipe === 'boolean')
            <select name="value" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500">
                <option value="1" @selected($systemConfig->value === '1' || $systemConfig->value === 'true')>Ya (true)</option>
                <option value="0" @selected($systemConfig->value === '0' || $systemConfig->value === 'false')>Tidak (false)</option>
            </select>
            @elseif($systemConfig->tipe === 'integer')
            <input type="number" name="value" value="{{ old('value', $systemConfig->value) }}"
                   class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500">
            @else
            <input type="text" name="value" value="{{ old('value', $systemConfig->value) }}"
                   class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500">
            @endif
        </div>
    </div>

    <div class="flex items-center justify-between">
        <a href="{{ route('superadmin.system-config.index') }}" class="text-sm text-slate-500 hover:text-slate-700">← Kembali</a>
        <button type="submit"
                class="px-5 py-2.5 bg-sky-600 text-white text-sm font-medium rounded-xl hover:bg-sky-700">
            Simpan
        </button>
    </div>
</form>
</div>
@endsection
