@extends('layouts.app')
@section('title', 'Role & Akses')

@section('breadcrumb')
    <span class="text-slate-500">Administrasi</span>
    <i class="ti ti-chevron-right text-slate-300 text-xs"></i>
    <span class="text-slate-800 font-medium">Role & Akses</span>
@endsection

@section('content')
<x-page-header title="Role & Hak Akses" desc="Kelola role dan permission yang dapat diberikan ke pengguna">
    @can('role.create')
    <a href="{{ route('superadmin.roles.create') }}"
       class="inline-flex items-center gap-2 px-4 py-2 bg-sky-600 text-white text-sm font-medium rounded-xl hover:bg-sky-700 transition-colors">
        <i class="ti ti-plus"></i> Buat Role Baru
    </a>
    @endcan
</x-page-header>

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
    @foreach($roles as $role)
    @php
    $isProtected = in_array($role->name, ['superadmin','admin_satker','arsiparis','operator_teknis','peminjam','viewer']);
    $colorMap = [
        'superadmin'      => 'border-purple-200 bg-purple-50',
        'admin_satker'    => 'border-blue-200 bg-blue-50',
        'arsiparis'       => 'border-teal-200 bg-teal-50',
        'operator_teknis' => 'border-amber-200 bg-amber-50',
        'peminjam'        => 'border-slate-200 bg-slate-50',
        'viewer'          => 'border-red-200 bg-red-50',
    ];
    $headerCls = $colorMap[$role->name] ?? 'border-slate-200 bg-slate-50';
    @endphp

    <div class="bg-white border border-slate-200 rounded-xl overflow-hidden">
        <div class="px-5 py-4 border-b {{ $headerCls }}">
            <div class="flex items-start justify-between gap-2">
                <div>
                    <p class="font-semibold text-slate-800">{{ ucfirst(str_replace('_', ' ', $role->name)) }}</p>
                    <p class="text-xs text-slate-500 mt-0.5">{{ $role->users_count }} pengguna</p>
                </div>
                @if($isProtected)
                <span class="flex-shrink-0 text-xs bg-slate-200 text-slate-600 px-2 py-0.5 rounded-md">Bawaan</span>
                @endif
            </div>
        </div>

        {{-- Permissions grouped --}}
        <div class="px-5 py-4">
            @php
            $grouped = $role->permissions->groupBy(fn($p) => explode('.', $p->name)[0]);
            @endphp
            @if($grouped->isEmpty())
            <p class="text-xs text-slate-400 italic">Belum ada permission</p>
            @else
            <div class="space-y-2">
                @foreach($grouped as $modul => $perms)
                <div>
                    <p class="text-xs font-medium text-slate-500 uppercase tracking-wide mb-1">{{ $modul }}</p>
                    <div class="flex flex-wrap gap-1">
                        @foreach($perms as $perm)
                        @php $action = explode('.', $perm->name)[1] ?? $perm->name; @endphp
                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs bg-slate-100 text-slate-600">
                            {{ $action }}
                        </span>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>

        {{-- Aksi --}}
        <div class="px-5 py-3 border-t border-slate-100 flex items-center gap-2">
            <a href="{{ route('superadmin.roles.show', $role) }}"
               class="flex-1 text-center text-xs text-slate-600 hover:text-sky-600 py-1.5 border border-slate-200 rounded-lg hover:border-sky-200 hover:bg-sky-50 transition-colors">
                Lihat pengguna
            </a>
            @if(!($role->name === 'superadmin'))
            @can('role.edit')
            <a href="{{ route('superadmin.roles.edit', $role) }}"
               class="flex-1 text-center text-xs text-slate-600 hover:text-amber-600 py-1.5 border border-slate-200 rounded-lg hover:border-amber-200 hover:bg-amber-50 transition-colors">
                Edit permission
            </a>
            @endcan
            @if(!$isProtected)
            @can('role.delete')
            <form action="{{ route('superadmin.roles.destroy', $role) }}" method="POST"
                  onsubmit="return confirm('Hapus role {{ $role->name }}?')">
                @csrf @method('DELETE')
                <button class="px-3 py-1.5 text-xs text-slate-400 hover:text-red-600 border border-slate-200 rounded-lg hover:border-red-200 hover:bg-red-50 transition-colors">
                    <i class="ti ti-trash"></i>
                </button>
            </form>
            @endcan
            @endif
            @endif
        </div>
    </div>
    @endforeach
</div>
@endsection
