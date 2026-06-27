{{-- resources/views/admin/unit-kerja/index.blade.php --}}
@extends('layouts.app')
@section('title', 'Unit Kerja')

@section('breadcrumb')
    <span class="text-slate-500 text-sm">Admin</span>
    <i class="ti ti-chevron-right text-slate-300 text-xs"></i>
    <span class="text-slate-800 font-medium text-sm">Unit Kerja</span>
@endsection

@section('content')
<x-page-header title="Unit Kerja" desc="Hierarki organisasi BBWS NT II" icon="ti-sitemap">
    @can('unit_kerja.create')
    <a href="{{ route('superadmin.unit-kerja.create') }}"
       class="inline-flex items-center gap-2 px-4 py-2 bg-sky-600 text-white text-sm font-medium rounded-xl hover:bg-sky-700">
        <i class="ti ti-plus"></i> Tambah Unit Kerja
    </a>
    @endcan
</x-page-header>

@if(isset($tree))
{{-- Tampilan pohon hierarki --}}
<div class="bg-white border border-slate-200 rounded-xl overflow-hidden">
    <div class="px-5 py-3.5 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Struktur Organisasi</p>
        <a href="{{ route('superadmin.unit-kerja.index', ['tipe'=>'satker']) }}"
           class="text-xs text-sky-600 hover:underline">Tampilan daftar →</a>
    </div>
    <div class="p-4">
        @foreach($tree as $balai)
        <div class="mb-4">
            {{-- Balai --}}
            <div class="flex items-center gap-2 px-3 py-2 bg-purple-50 border border-purple-100 rounded-xl mb-2">
                <i class="ti ti-building text-purple-500"></i>
                <span class="text-sm font-semibold text-purple-800">{{ $balai->namaLengkap() }}</span>
                <span class="ml-auto text-xs text-purple-500">{{ $balai->singkatan }}</span>
                <a href="{{ route('superadmin.unit-kerja.edit', $balai) }}"
                   class="p-1 text-purple-400 hover:text-purple-600 rounded"><i class="ti ti-edit text-xs"></i></a>
            </div>

            @foreach($balai->children as $child)
            {{-- Bagian/Bidang --}}
            <div class="ml-6 mb-2">
                <div class="flex items-center gap-2 px-3 py-2 bg-blue-50 border border-blue-100 rounded-lg mb-1">
                    <i class="ti ti-sitemap text-blue-500 text-sm"></i>
                    <span class="text-sm font-medium text-blue-800">{{ $child->namaLengkap() }}</span>
                    <span class="ml-auto text-xs px-1.5 py-0.5 rounded bg-blue-100 text-blue-600">{{ ucfirst($child->tipe) }}</span>
                    <a href="{{ route('superadmin.unit-kerja.edit', $child) }}"
                       class="p-1 text-blue-400 hover:text-blue-600 rounded"><i class="ti ti-edit text-xs"></i></a>
                </div>

                @foreach($child->children as $satker)
                {{-- Satker --}}
                <div class="ml-6 mb-1">
                    <div class="flex items-center gap-2 px-3 py-2 bg-amber-50 border border-amber-100 rounded-lg">
                        <i class="ti ti-building-community text-amber-500 text-sm"></i>
                        <span class="text-sm text-amber-800 font-medium">{{ $satker->namaLengkap() }}</span>
                        <span class="ml-auto text-xs text-amber-500">{{ $satker->users_count ?? 0 }} user</span>
                        <a href="{{ route('superadmin.unit-kerja.show', $satker) }}"
                           class="p-1 text-amber-400 hover:text-amber-600 rounded"><i class="ti ti-eye text-xs"></i></a>
                        <a href="{{ route('superadmin.unit-kerja.edit', $satker) }}"
                           class="p-1 text-amber-400 hover:text-amber-600 rounded"><i class="ti ti-edit text-xs"></i></a>
                    </div>

                    @foreach($satker->children as $ppk)
                    <div class="ml-6 mt-1 flex items-center gap-2 px-3 py-1.5 border border-slate-100 rounded-lg">
                        <i class="ti ti-users text-slate-400 text-sm"></i>
                        <span class="text-xs text-slate-600">{{ $ppk->namaLengkap() }}</span>
                        <a href="{{ route('superadmin.unit-kerja.edit', $ppk) }}"
                           class="ml-auto p-1 text-slate-300 hover:text-slate-500 rounded"><i class="ti ti-edit text-xs"></i></a>
                    </div>
                    @endforeach
                </div>
                @endforeach
            </div>
            @endforeach
        </div>
        @endforeach
    </div>
</div>
@else
{{-- Tampilan daftar --}}
<div class="bg-white border border-slate-200 rounded-xl overflow-hidden mb-4">
    <div class="px-5 py-3.5 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Daftar Unit Kerja</p>
        <a href="{{ route('superadmin.unit-kerja.index') }}" class="text-xs text-sky-600 hover:underline">Tampilan pohon →</a>
    </div>
    <table class="w-full text-sm">
        <thead class="bg-slate-50 border-b border-slate-200 text-xs font-medium text-slate-500 uppercase tracking-wide">
            <tr>
                <th class="px-5 py-3 text-left">Nama</th>
                <th class="px-5 py-3 text-left">Tipe</th>
                <th class="px-5 py-3 text-left hidden md:table-cell">Kode</th>
                <th class="px-5 py-3 text-left hidden lg:table-cell">Induk</th>
                <th class="px-5 py-3 text-center">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse($rows as $uk)
            <tr class="hover:bg-slate-50">
                <td class="px-5 py-3">
                    <p class="font-medium text-slate-800">{{ $uk->namaLengkap() }}</p>
                    @if($uk->kepala_nama)
                    <p class="text-xs text-slate-400">Kepala: {{ $uk->kepala_nama }}</p>
                    @endif
                </td>
                <td class="px-5 py-3">
                    <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium {{ $uk->badgeClass() }}">
                        {{ ucfirst($uk->tipe) }}
                    </span>
                </td>
                <td class="px-5 py-3 hidden md:table-cell text-xs font-mono text-slate-500">{{ $uk->kode }}</td>
                <td class="px-5 py-3 hidden lg:table-cell text-xs text-slate-500">{{ $uk->parent?->singkatan ?? '—' }}</td>
                <td class="px-5 py-3">
                    <div class="flex items-center justify-center gap-1">
                        <a href="{{ route('superadmin.unit-kerja.show', $uk) }}"
                           class="p-1.5 text-slate-400 hover:text-sky-600 hover:bg-sky-50 rounded-lg">
                            <i class="ti ti-eye text-sm"></i>
                        </a>
                        <a href="{{ route('superadmin.unit-kerja.edit', $uk) }}"
                           class="p-1.5 text-slate-400 hover:text-amber-600 hover:bg-amber-50 rounded-lg">
                            <i class="ti ti-edit text-sm"></i>
                        </a>
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="5" class="px-5 py-8 text-center text-slate-400">Tidak ada data.</td></tr>
            @endforelse
        </tbody>
    </table>
    @if($rows->hasPages())
    <div class="px-5 py-4 border-t border-slate-100">{{ $rows->links() }}</div>
    @endif
</div>
@endif
@endsection
