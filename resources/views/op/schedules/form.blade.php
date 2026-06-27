{{-- resources/views/op/schedules/form.blade.php --}}
@extends('layouts.app')
@section('title', isset($schedule) ? 'Edit Jadwal OP' : 'Buat Jadwal OP')

@section('breadcrumb')
    <a href="{{ route('op.schedules.index') }}" class="text-slate-500 hover:text-slate-700 text-sm">Jadwal OP</a>
    <i class="ti ti-chevron-right text-slate-300 text-xs"></i>
    <span class="text-slate-800 font-medium text-sm">{{ isset($schedule) ? 'Edit' : 'Buat' }}</span>
@endsection

@section('content')
@php $isEdit = isset($schedule); @endphp
<div class="max-w-2xl">
<x-page-header :title="$isEdit ? 'Edit Jadwal OP' : 'Buat Jadwal OP'" icon="ti-calendar-event" />

<form method="POST"
      action="{{ $isEdit ? route('op.schedules.update', $schedule) : route('op.schedules.store') }}"
      class="space-y-5">
    @csrf
    @if($isEdit) @method('PUT') @endif

    <div class="bg-white border border-slate-200 rounded-xl p-5 space-y-4">
        <div>
            <label class="block text-xs font-medium text-slate-600 mb-1">Aset <span class="text-red-500">*</span></label>
            <select name="asset_id" required {{ $isEdit ? 'disabled' : '' }}
                    class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500">
                <option value="">Pilih aset...</option>
                @foreach($assets as $a)
                <option value="{{ $a->id }}" @selected(old('asset_id', $schedule->asset_id ?? '') === $a->id)>
                    [{{ $a->asset_code }}] {{ $a->nama }}
                </option>
                @endforeach
            </select>
            @if($isEdit) <input type="hidden" name="asset_id" value="{{ $schedule->asset_id }}"> @endif
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Satker <span class="text-red-500">*</span></label>
                <select name="unit_kerja_id" required
                        class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500">
                    <option value="">Pilih satker...</option>
                    @foreach($unitKerjas as $uk)
                    <option value="{{ $uk->id }}" @selected(old('unit_kerja_id', $schedule->unit_kerja_id ?? '') === $uk->id)>
                        {{ $uk->singkatan }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Tahun <span class="text-red-500">*</span></label>
                <input type="number" name="tahun" required min="2020" max="{{ now()->year + 2 }}"
                       value="{{ old('tahun', $schedule->tahun ?? now()->year + 1) }}"
                       class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500">
            </div>
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Anggaran OP Rutin (Rp)</label>
                <input type="number" name="anggaran_op_rutin" min="0"
                       value="{{ old('anggaran_op_rutin', $schedule->anggaran_op_rutin ?? '') }}"
                       class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Anggaran OP Berkala (Rp)</label>
                <input type="number" name="anggaran_op_berkala" min="0"
                       value="{{ old('anggaran_op_berkala', $schedule->anggaran_op_berkala ?? '') }}"
                       class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500">
            </div>
        </div>
        <div>
            <label class="block text-xs font-medium text-slate-600 mb-1">Kode DIPA</label>
            <input type="text" name="kode_dipa"
                   value="{{ old('kode_dipa', $schedule->kode_dipa ?? '') }}"
                   class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500">
        </div>
    </div>

    <div class="flex items-center justify-between">
        <a href="{{ route('op.schedules.index') }}" class="text-sm text-slate-500 hover:text-slate-700">← Kembali</a>
        <button type="submit"
                class="px-5 py-2.5 bg-sky-600 text-white text-sm font-medium rounded-xl hover:bg-sky-700">
            {{ $isEdit ? 'Simpan Perubahan' : 'Buat Jadwal OP' }}
        </button>
    </div>
</form>
</div>
@endsection
