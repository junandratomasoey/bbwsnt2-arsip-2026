@extends('layouts.app')
@section('title', 'Jenis Aset')
@section('breadcrumb')
    <span class="text-slate-800 font-medium text-sm">Jenis Aset</span>
@endsection
@section('content')
<x-page-header title="Jenis Aset Infrastruktur" desc="Kelola tipe dan kategori aset SDA" icon="ti-building-bridge">
    @can('asset_type.create')
    <a href="{{ route('superadmin.asset-types.create') }}"
       class="inline-flex items-center gap-2 px-4 py-2 text-white text-sm font-medium rounded-xl"
       style="background:#003366">
        <i class="ti ti-plus"></i> Tambah Jenis Aset
    </a>
    @endcan
</x-page-header>

<div class="bg-white border border-slate-200 rounded-xl overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 border-b border-slate-200 text-xs font-medium text-slate-500 uppercase">
            <tr>
                <th class="px-4 py-3 text-left">#</th>
                <th class="px-4 py-3 text-left">Kode</th>
                <th class="px-4 py-3 text-left">Nama</th>
                <th class="px-4 py-3 text-left hidden md:table-cell">Kategori</th>
                <th class="px-4 py-3 text-left hidden lg:table-cell">Standar OP</th>
                <th class="px-4 py-3 text-center">Jml Aset</th>
                <th class="px-4 py-3 text-center">Status</th>
                <th class="px-4 py-3 text-center">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse($types as $i => $type)
            <tr class="hover:bg-slate-50">
                <td class="px-4 py-3 text-xs text-slate-400">{{ $i+1 }}</td>
                <td class="px-4 py-3">
                    <span class="font-mono font-bold text-xs px-2 py-0.5 rounded bg-slate-100">{{ $type->kode }}</span>
                </td>
                <td class="px-4 py-3 font-medium text-slate-800">{{ $type->nama }}</td>
                <td class="px-4 py-3 hidden md:table-cell text-xs text-slate-500">{{ $type->kategori }}</td>
                <td class="px-4 py-3 hidden lg:table-cell text-xs text-slate-500">{{ $type->standar_op ?: '—' }}</td>
                <td class="px-4 py-3 text-center font-semibold {{ $type->assets_count ? 'text-slate-700' : 'text-slate-300' }}">
                    {{ $type->assets_count }}
                </td>
                <td class="px-4 py-3 text-center">
                    <span class="text-xs px-2 py-0.5 rounded-full {{ $type->is_aktif ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                        {{ $type->is_aktif ? 'Aktif' : 'Nonaktif' }}
                    </span>
                </td>
                <td class="px-4 py-3">
                    <div class="flex items-center justify-center gap-1">
                        <a href="{{ route('superadmin.asset-types.edit', $type) }}"
                           class="p-1.5 text-slate-400 hover:text-amber-600 hover:bg-amber-50 rounded-lg" title="Edit">
                            <i class="ti ti-edit text-sm"></i>
                        </a>
                        @if($type->assets_count === 0)
                        <form action="{{ route('superadmin.asset-types.destroy', $type) }}" method="POST"
                              onsubmit="return confirm('Hapus {{ $type->nama }}?')">
                            @csrf @method('DELETE')
                            <button class="p-1.5 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg">
                                <i class="ti ti-trash text-sm"></i>
                            </button>
                        </form>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="8" class="px-5 py-10 text-center text-slate-400">Belum ada jenis aset</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
