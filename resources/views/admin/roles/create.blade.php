@extends('layouts.app')
@section('title', isset($role) ? 'Edit Role' : 'Buat Role')

@section('breadcrumb')
    <a href="{{ route('superadmin.roles.index') }}" class="text-slate-500 hover:text-slate-700">Role & Akses</a>
    <i class="ti ti-chevron-right text-slate-300 text-xs"></i>
    <span class="text-slate-800 font-medium">{{ isset($role) ? 'Edit Permission' : 'Buat Role Baru' }}</span>
@endsection

@section('content')
@php $isEdit = isset($role); @endphp
<div class="max-w-3xl">
<x-page-header
    :title="$isEdit ? 'Edit Permission: ' . ucfirst(str_replace('_',' ',$role->name)) : 'Buat Role Baru'"
    desc="Centang permission yang diizinkan untuk role ini" />

<form method="POST"
      action="{{ $isEdit ? route('superadmin.roles.update', $role) : route('superadmin.roles.store') }}"
      class="space-y-5">
    @csrf
    @if($isEdit) @method('PUT') @endif

    @if(!$isEdit)
    <div class="bg-white border border-slate-200 rounded-xl p-5">
        <label class="block text-sm font-medium text-slate-700 mb-1.5">
            Nama Role <span class="text-red-500">*</span>
        </label>
        <input type="text" name="name" value="{{ old('name') }}"
               placeholder="contoh: koordinator_irigasi"
               required
               class="w-full border border-slate-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500
                      @error('name') border-red-400 @enderror">
        <p class="mt-1.5 text-xs text-slate-400">Gunakan huruf kecil dan garis bawah. Spasi otomatis diganti underscore.</p>
        @error('name')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
    </div>
    @endif

    {{-- Permission per modul --}}
    <div class="bg-white border border-slate-200 rounded-xl overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
            <div>
                <p class="text-sm font-semibold text-slate-800">Hak Akses (Permissions)</p>
                <p class="text-xs text-slate-500 mt-0.5">Pilih aksi yang diizinkan per modul</p>
            </div>
            <div class="flex items-center gap-2">
                <button type="button" onclick="toggleAll(true)"
                        class="text-xs px-3 py-1.5 border border-slate-200 rounded-lg text-slate-600 hover:bg-slate-50">
                    Pilih semua
                </button>
                <button type="button" onclick="toggleAll(false)"
                        class="text-xs px-3 py-1.5 border border-slate-200 rounded-lg text-slate-600 hover:bg-slate-50">
                    Hapus semua
                </button>
            </div>
        </div>

        <div class="divide-y divide-slate-100">
        @foreach($permissions as $modul => $perms)
        <div class="px-5 py-4" x-data="{ open: true }">
            <div class="flex items-center justify-between mb-3 cursor-pointer" @click="open = !open">
                <div class="flex items-center gap-3">
                    <input type="checkbox" class="modul-check w-4 h-4 rounded border-slate-300 text-sky-600"
                           data-modul="{{ $modul }}"
                           onchange="toggleModul('{{ $modul }}', this.checked)"
                           @php
                           $allChecked = $perms->every(fn($p) => in_array($p->name, $rolePermissions ?? []));
                           @endphp
                           {{ $allChecked ? 'checked' : '' }}>
                    <p class="text-sm font-semibold text-slate-700 capitalize">
                        {{ str_replace('_', ' ', $modul) }}
                    </p>
                    <span class="text-xs text-slate-400">{{ $perms->count() }} aksi</span>
                </div>
                <i :class="open ? 'ti-chevron-up' : 'ti-chevron-down'"
                   class="ti text-slate-400 text-xs"></i>
            </div>

            <div x-show="open" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-2">
                @foreach($perms as $perm)
                @php
                $action    = explode('.', $perm->name)[1] ?? $perm->name;
                $isChecked = in_array($perm->name, $rolePermissions ?? []);
                $actionIcon = match($action) {
                    'view'         => 'ti-eye',
                    'view_rahasia' => 'ti-eye-off',
                    'create'       => 'ti-plus',
                    'edit'         => 'ti-edit',
                    'delete'       => 'ti-trash',
                    'approve'      => 'ti-check',
                    'assign'       => 'ti-user-plus',
                    'download'     => 'ti-download',
                    'upload'       => 'ti-upload',
                    'export'       => 'ti-file-export',
                    default        => 'ti-circle',
                };
                @endphp
                <label class="perm-item flex items-center gap-2 p-2.5 rounded-lg border cursor-pointer transition-colors
                              {{ $isChecked ? 'border-sky-200 bg-sky-50' : 'border-slate-100 hover:border-slate-200 hover:bg-slate-50' }}"
                       data-modul="{{ $modul }}">
                    <input type="checkbox" name="permissions[]" value="{{ $perm->name }}"
                           class="w-4 h-4 rounded border-slate-300 text-sky-600 focus:ring-sky-500"
                           {{ $isChecked ? 'checked' : '' }}
                           onchange="this.closest('label').classList.toggle('border-sky-200', this.checked);
                                     this.closest('label').classList.toggle('bg-sky-50', this.checked);
                                     this.closest('label').classList.toggle('border-slate-100', !this.checked);
                                     updateModulCheck('{{ $modul }}')">
                    <i class="ti {{ $actionIcon }} text-sm text-slate-400 flex-shrink-0"></i>
                    <span class="text-xs text-slate-700 capitalize">{{ str_replace('_', ' ', $action) }}</span>
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
                class="px-5 py-2.5 bg-sky-600 text-white text-sm font-medium rounded-xl hover:bg-sky-700 transition-colors">
            {{ $isEdit ? 'Simpan Permission' : 'Buat Role' }}
        </button>
    </div>
</form>
</div>

<script>
function toggleAll(check) {
    document.querySelectorAll('input[name="permissions[]"]').forEach(cb => {
        cb.checked = check;
        cb.closest('label')?.classList.toggle('border-sky-200', check);
        cb.closest('label')?.classList.toggle('bg-sky-50', check);
        cb.closest('label')?.classList.toggle('border-slate-100', !check);
    });
    document.querySelectorAll('.modul-check').forEach(cb => cb.checked = check);
}

function toggleModul(modul, check) {
    document.querySelectorAll(`label[data-modul="${modul}"] input[name="permissions[]"]`).forEach(cb => {
        cb.checked = check;
        cb.closest('label')?.classList.toggle('border-sky-200', check);
        cb.closest('label')?.classList.toggle('bg-sky-50', check);
        cb.closest('label')?.classList.toggle('border-slate-100', !check);
    });
}

function updateModulCheck(modul) {
    const all  = document.querySelectorAll(`label[data-modul="${modul}"] input[name="permissions[]"]`);
    const done = [...all].filter(c => c.checked).length;
    const mc   = document.querySelector(`.modul-check[data-modul="${modul}"]`);
    if (mc) mc.checked = done === all.length;
}
</script>
@endsection
