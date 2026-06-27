{{-- resources/views/admin/roles/index.blade.php --}}
@extends('layouts.app')
@section('title', 'Role & Akses')

@section('breadcrumb')
    <span class="text-slate-500 text-sm">Admin</span>
    <i class="ti ti-chevron-right text-slate-300 text-xs"></i>
    <span class="text-slate-800 font-medium text-sm">Role & Akses</span>
@endsection

@section('content')
<x-page-header title="Role & Akses" desc="Kelola role dan hak akses pengguna sistem" icon="ti-shield-lock">
    @can('role.create')
    <a href="{{ route('superadmin.roles.create') }}"
       class="inline-flex items-center gap-2 px-4 py-2 bg-sky-600 text-white text-sm font-medium rounded-xl hover:bg-sky-700">
        <i class="ti ti-plus"></i> Tambah Role
    </a>
    @endcan
</x-page-header>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
    @foreach($roles as $role)
    @php
    $protected = in_array($role->name, ['superadmin','admin_satker','arsiparis','operator_teknis','peminjam','viewer','pimpinan']);
    $colorMap = [
        'superadmin'      => ['bg-red-50 border-red-100',     'text-red-600',    'ti-crown'],
        'admin_satker'    => ['bg-blue-50 border-blue-100',   'text-blue-600',   'ti-shield-half'],
        'arsiparis'       => ['bg-teal-50 border-teal-100',   'text-teal-600',   'ti-archive'],
        'operator_teknis' => ['bg-amber-50 border-amber-100', 'text-amber-600',  'ti-tool'],
        'peminjam'        => ['bg-purple-50 border-purple-100','text-purple-600','ti-book-download'],
        'viewer'          => ['bg-slate-50 border-slate-200', 'text-slate-500',  'ti-eye'],
        'pimpinan'        => ['bg-emerald-50 border-emerald-100','text-emerald-600','ti-chart-bar'],
    ];
    $colors = $colorMap[$role->name] ?? ['bg-sky-50 border-sky-100', 'text-sky-600', 'ti-shield'];
    @endphp
    <div class="bg-white border border-slate-200 rounded-xl p-5 hover:border-slate-300 transition-colors">
        <div class="flex items-start justify-between gap-3 mb-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl {{ $colors[0] }} border flex items-center justify-center flex-shrink-0">
                    <i class="ti {{ $colors[2] }} {{ $colors[1] }} text-lg"></i>
                </div>
                <div>
                    <p class="font-semibold text-slate-800">{{ ucfirst(str_replace('_',' ',$role->name)) }}</p>
                    <p class="text-xs text-slate-400">{{ $role->users_count }} pengguna</p>
                </div>
            </div>
            @if($protected)
            <span class="text-xs px-2 py-0.5 rounded bg-slate-100 text-slate-500 flex-shrink-0">Bawaan</span>
            @endif
        </div>

        {{-- Daftar permission --}}
        <div class="space-y-1 mb-4">
            @php $permGroups = $role->permissions->groupBy(fn($p) => explode('.', $p->name)[0]); @endphp
            @foreach($permGroups->take(5) as $group => $perms)
            <div class="flex items-center justify-between text-xs">
                <span class="text-slate-500 capitalize">{{ str_replace('_',' ',$group) }}</span>
                <span class="text-slate-400">{{ $perms->count() }} akses</span>
            </div>
            @endforeach
            @if($permGroups->count() > 5)
            <p class="text-xs text-slate-400">+{{ $permGroups->count() - 5 }} grup lainnya</p>
            @endif
        </div>

        <div class="flex gap-2 pt-3 border-t border-slate-100">
            @can('role.edit')
            <a href="{{ route('superadmin.roles.edit', $role) }}"
               class="flex-1 text-center py-1.5 text-sm text-slate-600 border border-slate-200 rounded-lg hover:bg-slate-50">
                Edit Permission
            </a>
            @endcan
            @if(!$protected)
            @can('role.delete')
            <form action="{{ route('superadmin.roles.destroy', $role) }}" method="POST"
                  onsubmit="return confirm('Hapus role {{ $role->name }}?')">
                @csrf @method('DELETE')
                <button class="p-1.5 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg border border-slate-200">
                    <i class="ti ti-trash text-sm"></i>
                </button>
            </form>
            @endcan
            @endif
        </div>
    </div>
    @endforeach
</div>
@endsection
