{{-- resources/views/admin/roles/form.blade.php --}}
@extends('layouts.app')
@section('title', isset($role) ? 'Edit Role' : 'Tambah Role')

@section('breadcrumb')
    <a href="{{ route('superadmin.roles.index') }}" class="text-slate-500 hover:text-slate-700 text-sm">Role</a>
    <i class="ti ti-chevron-right text-slate-300 text-xs"></i>
    <span class="text-slate-800 font-medium text-sm">{{ isset($role) ? 'Edit' : 'Tambah' }}</span>
@endsection

@section('content')
@php $isEdit  = isset($role);
    $role = $role ?? null; @endphp
<div class="max-w-3xl">
<x-page-header :title="$isEdit ? 'Edit Role: ' . ucfirst(str_replace('_',' ',$role->name)) : 'Tambah Role'" icon="ti-shield-lock" />

<form method="POST"
      action="{{ $isEdit ? route('superadmin.roles.update', $role) : route('superadmin.roles.store') }}"
      class="space-y-5">
    @csrf
    @if($isEdit) @method('PUT') @endif

    @if(!$isEdit)
    <div class="bg-white border border-slate-200 rounded-xl p-5">
        <label class="block text-xs font-medium text-slate-600 mb-1">Nama Role <span class="text-red-500">*</span></label>
        <input type="text" name="name" required value="{{ old('name') }}"
               placeholder="contoh: supervisor_teknis"
               class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-sky-500">
        <p class="text-xs text-slate-400 mt-1.5">Gunakan lowercase dan underscore untuk spasi</p>
    </div>
    @endif

    {{-- Permissions per grup --}}
    <div class="bg-white border border-slate-200 rounded-xl overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
            <h3 class="text-sm font-semibold text-slate-700">Pilih Hak Akses</h3>
            <div class="flex gap-2">
                <button type="button" onclick="checkAll(true)"
                        class="text-xs px-3 py-1 bg-sky-50 text-sky-600 rounded-lg hover:bg-sky-100">
                    Pilih Semua
                </button>
                <button type="button" onclick="checkAll(false)"
                        class="text-xs px-3 py-1 bg-slate-100 text-slate-600 rounded-lg hover:bg-slate-200">
                    Hapus Semua
                </button>
            </div>
        </div>

        @php
        $rolePermNames = $rolePermissions ?? [];
        $groupIcons = [
            'user'       => 'ti-users',
            'role'       => 'ti-shield-lock',
            'unit_kerja' => 'ti-sitemap',
            'asset'      => 'ti-building-bridge',
            'asset_condition'  => 'ti-clipboard-check',
            'asset_geometry'   => 'ti-map-pin',
            'project'    => 'ti-timeline',
            'project_progress' => 'ti-chart-line',
            'op_record'  => 'ti-settings-2',
            'op_schedule'=> 'ti-calendar-event',
            'document'   => 'ti-files',
            'physical_location'=> 'ti-box',
            'loan'       => 'ti-book-download',
            'knowledge'  => 'ti-brain',
            'library'    => 'ti-books',
            'library_loan'     => 'ti-book',
            'dashboard'  => 'ti-home-2',
            'report'     => 'ti-chart-dots',
            'audit_log'  => 'ti-history',
            'system_config'    => 'ti-settings',
            'workflow'   => 'ti-git-branch',
        ];
        @endphp

        <div class="divide-y divide-slate-100">
            @foreach($permissions as $group => $perms)
            <div class="px-5 py-4" x-data="{ open: {{ $perms->contains(fn($p) => in_array($p->name, $rolePermNames)) ? 'true' : 'false' }} }">
                <button type="button" @click="open = !open"
                        class="w-full flex items-center gap-3 text-left">
                    <i class="ti {{ $groupIcons[$group] ?? 'ti-circle' }} text-slate-400 flex-shrink-0"></i>
                    <span class="text-sm font-medium text-slate-700 capitalize flex-1">
                        {{ ucfirst(str_replace('_',' ',$group)) }}
                    </span>
                    <span class="text-xs text-slate-400">
                        {{ $perms->filter(fn($p) => in_array($p->name, $rolePermNames))->count() }}/{{ $perms->count() }}
                    </span>
                    <i :class="open ? 'ti-chevron-up' : 'ti-chevron-down'" class="ti text-xs text-slate-400"></i>
                </button>

                <div x-show="open" x-cloak class="mt-3 grid grid-cols-2 sm:grid-cols-3 gap-2">
                    @foreach($perms as $perm)
                    @php $permLabel = explode('.', $perm->name)[1] ?? $perm->name; @endphp
                    <label class="flex items-center gap-2 cursor-pointer p-2 rounded-lg hover:bg-slate-50">
                        <input type="checkbox" name="permissions[]" value="{{ $perm->name }}"
                               @checked(in_array($perm->name, $rolePermNames))
                               class="permission-cb rounded border-slate-300 text-sky-600 focus:ring-sky-500">
                        <span class="text-xs text-slate-600">{{ ucfirst(str_replace('_',' ',$permLabel)) }}</span>
                    </label>
                    @endforeach
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <div class="flex items-center justify-between">
        <a href="{{ route('superadmin.roles.index') }}" class="text-sm text-slate-500 hover:text-slate-700">← Kembali</a>
        <button type="submit"
                class="px-5 py-2.5 bg-sky-600 text-white text-sm font-medium rounded-xl hover:bg-sky-700">
            {{ $isEdit ? 'Simpan Permission' : 'Buat Role' }}
        </button>
    </div>
</form>
</div>
@endsection

@push('scripts')
<script>
function checkAll(val) {
    document.querySelectorAll('.permission-cb').forEach(cb => cb.checked = val);
}
</script>
@endpush
