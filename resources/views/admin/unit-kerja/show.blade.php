{{-- resources/views/admin/unit-kerja/show.blade.php --}}
@extends('layouts.app')
@section('title', $unitKerja->namaLengkap())

@section('breadcrumb')
    <a href="{{ route('superadmin.unit-kerja.index') }}" class="text-slate-500 hover:text-slate-700 text-sm">Unit Kerja</a>
    <i class="ti ti-chevron-right text-slate-300 text-xs"></i>
    <span class="text-slate-800 font-medium text-sm">{{ $unitKerja->singkatan ?? $unitKerja->nama }}</span>
@endsection

@section('content')
<x-page-header :title="$unitKerja->namaLengkap()" icon="ti-sitemap">
    <a href="{{ route('superadmin.unit-kerja.edit', $unitKerja) }}"
       class="inline-flex items-center gap-1.5 px-4 py-2 bg-white border border-slate-200 text-sm text-slate-700 rounded-xl hover:bg-slate-50">
        <i class="ti ti-edit text-slate-400"></i> Edit
    </a>
    @if($unitKerja->tipe === 'satker')
    <a href="{{ route('superadmin.unit-kerja.ppk.create', $unitKerja) }}"
       class="inline-flex items-center gap-2 px-4 py-2 bg-sky-600 text-white text-sm font-medium rounded-xl hover:bg-sky-700">
        <i class="ti ti-plus"></i> Tambah PPK
    </a>
    @endif
</x-page-header>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-5">

        <div class="bg-white border border-slate-200 rounded-xl p-5">
            <div class="flex items-start gap-3 mb-4">
                <span class="inline-flex px-2.5 py-1 rounded-lg text-sm font-medium {{ $unitKerja->badgeClass() }}">
                    {{ ucfirst($unitKerja->tipe) }}
                </span>
                <span class="font-mono text-sm text-slate-400 bg-slate-100 px-2 py-0.5 rounded">{{ $unitKerja->kode }}</span>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div><p class="text-xs text-slate-400 mb-0.5">Singkatan</p>
                    <p class="text-sm text-slate-700">{{ $unitKerja->singkatan ?? '—' }}</p>
                </div>
                <div><p class="text-xs text-slate-400 mb-0.5">Induk</p>
                    <p class="text-sm text-slate-700">{{ $unitKerja->parent?->namaLengkap() ?? '—' }}</p>
                </div>
                @if($unitKerja->kepala_nama)
                <div><p class="text-xs text-slate-400 mb-0.5">Kepala</p>
                    <p class="text-sm text-slate-700">{{ $unitKerja->kepala_nama }}</p>
                </div>
                <div><p class="text-xs text-slate-400 mb-0.5">NIP Kepala</p>
                    <p class="text-sm font-mono text-slate-700">{{ $unitKerja->kepala_nip ?? '—' }}</p>
                </div>
                @endif
                @if($unitKerja->email)
                <div><p class="text-xs text-slate-400 mb-0.5">Email</p>
                    <p class="text-sm text-slate-700">{{ $unitKerja->email }}</p>
                </div>
                @endif
                <div><p class="text-xs text-slate-400 mb-0.5">Status</p>
                    <span class="text-xs px-2 py-0.5 rounded {{ $unitKerja->is_aktif ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' }}">
                        {{ $unitKerja->is_aktif ? 'Aktif' : 'Nonaktif' }}
                    </span>
                </div>
            </div>
        </div>

        {{-- Sub-unit --}}
        @if($unitKerja->children->isNotEmpty())
        <div class="bg-white border border-slate-200 rounded-xl overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100">
                <h3 class="text-sm font-semibold text-slate-700">Sub-unit ({{ $unitKerja->children->count() }})</h3>
            </div>
            <div class="divide-y divide-slate-100">
                @foreach($unitKerja->children as $child)
                <div class="px-5 py-3 flex items-center justify-between">
                    <div>
                        <a href="{{ route('superadmin.unit-kerja.show', $child) }}"
                           class="text-sm font-medium text-slate-800 hover:text-sky-600">{{ $child->namaLengkap() }}</a>
                        <p class="text-xs text-slate-400">{{ $child->children->count() }} sub · {{ $child->users->count() }} user</p>
                    </div>
                    <span class="text-xs px-2 py-0.5 rounded {{ $child->badgeClass() }}">{{ ucfirst($child->tipe) }}</span>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    {{-- Sidebar: pengguna --}}
    <div>
        <div class="bg-white border border-slate-200 rounded-xl overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-slate-700">Pengguna ({{ $unitKerja->users->count() }})</h3>
            </div>
            @if($unitKerja->users->isEmpty())
            <p class="px-5 py-5 text-center text-xs text-slate-400">Belum ada pengguna</p>
            @else
            <div class="divide-y divide-slate-100">
                @foreach($unitKerja->users->take(10) as $u)
                <div class="px-5 py-2.5 flex items-center gap-3">
                    <div class="w-7 h-7 rounded-full bg-sky-100 flex items-center justify-center text-sky-700 text-xs font-bold flex-shrink-0">
                        {{ $u->inisial() }}
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs font-medium text-slate-700 truncate">{{ $u->name }}</p>
                        <p class="text-xs text-slate-400">{{ $u->namaRole() }}</p>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
