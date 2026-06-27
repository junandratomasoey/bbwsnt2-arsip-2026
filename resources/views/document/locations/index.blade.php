@extends('layouts.app')
@section('title', 'Lokasi Fisik Dokumen')

@section('breadcrumb')
    <i class="ti ti-box text-slate-400"></i>
    <span class="text-slate-800 font-medium text-sm">Lokasi Fisik</span>
@endsection

@section('content')
<x-page-header title="Lokasi Fisik Dokumen" desc="Daftar lokasi penyimpanan arsip fisik" icon="ti-box">
    @can('physical_location.create')
    <a href="{{ route('locations.create') }}"
       class="inline-flex items-center gap-2 px-4 py-2 bg-sky-600 text-white text-sm font-medium rounded-xl hover:bg-sky-700">
        <i class="ti ti-plus"></i> Tambah Lokasi
    </a>
    @endcan
</x-page-header>

{{-- Filter --}}
<div class="bg-white border border-slate-200 rounded-xl p-4 mb-4">
    <form method="GET" class="flex flex-wrap gap-2">
        <input type="text" name="search" value="{{ request('search') }}"
               placeholder="Cari kode lokasi, gedung..."
               class="flex-1 min-w-48 border border-slate-200 rounded-lg px-3 py-2 text-sm
                      focus:outline-none focus:ring-2 focus:ring-sky-500">
        <select name="gedung" class="border border-slate-200 rounded-lg px-3 py-2 text-sm
                                     focus:outline-none focus:ring-2 focus:ring-sky-500">
            <option value="">Semua gedung</option>
            @foreach($gedungList as $g)
            <option value="{{ $g }}" @selected(request('gedung') === $g)>{{ $g }}</option>
            @endforeach
        </select>
        <button type="submit" class="px-4 py-2 bg-slate-800 text-white text-sm rounded-lg hover:bg-slate-700">
            <i class="ti ti-search"></i>
        </button>
        @if(request()->hasAny(['search','gedung']))
        <a href="{{ route('locations.index') }}"
           class="px-4 py-2 text-sm text-slate-500 border border-slate-200 rounded-lg hover:bg-slate-50">
            Reset
        </a>
        @endif
    </form>
</div>

<div class="bg-white border border-slate-200 rounded-xl overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 border-b border-slate-200 text-xs font-medium text-slate-500 uppercase tracking-wide">
            <tr>
                <th class="px-5 py-3 text-left">Kode Lokasi</th>
                <th class="px-5 py-3 text-left">Gedung</th>
                <th class="px-5 py-3 text-left hidden md:table-cell">Lantai / Ruang</th>
                <th class="px-5 py-3 text-left hidden lg:table-cell">Lemari / Rak / Laci</th>
                <th class="px-5 py-3 text-center">Dokumen</th>
                <th class="px-5 py-3 text-center">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse($query as $loc)
            <tr class="hover:bg-slate-50 transition-colors">
                <td class="px-5 py-3">
                    <span class="font-mono text-sm font-semibold text-slate-800">
                        {{ $loc->kode_lokasi }}
                    </span>
                </td>
                <td class="px-5 py-3 text-sm text-slate-700">{{ $loc->gedung }}</td>
                <td class="px-5 py-3 hidden md:table-cell text-xs text-slate-500">
                    {{ collect([$loc->lantai, $loc->ruang])->filter()->join(' / ') ?: '—' }}
                </td>
                <td class="px-5 py-3 hidden lg:table-cell text-xs text-slate-500">
                    {{ collect([$loc->lemari, $loc->rak, $loc->laci])->filter()->join(' / ') ?: '—' }}
                </td>
                <td class="px-5 py-3 text-center">
                    <span class="text-sm font-semibold text-slate-700">
                        {{ $loc->documents_count }}
                    </span>
                    @if($loc->kapasitas_item)
                    <span class="text-xs text-slate-400">/ {{ $loc->kapasitas_item }}</span>
                    @endif
                </td>
                <td class="px-5 py-3">
                    <div class="flex items-center justify-center gap-1">
                        <a href="{{ route('locations.show', $loc) }}"
                           class="p-1.5 text-slate-400 hover:text-sky-600 hover:bg-sky-50 rounded-lg" title="Detail">
                            <i class="ti ti-eye text-sm"></i>
                        </a>
                        @can('physical_location.edit')
                        <a href="{{ route('locations.edit', $loc) }}"
                           class="p-1.5 text-slate-400 hover:text-amber-600 hover:bg-amber-50 rounded-lg" title="Edit">
                            <i class="ti ti-edit text-sm"></i>
                        </a>
                        @endcan
                        @can('physical_location.delete')
                        <form action="{{ route('locations.destroy', $loc) }}" method="POST"
                              onsubmit="return confirm('Hapus lokasi {{ $loc->kode_lokasi }}?')">
                            @csrf @method('DELETE')
                            <button class="p-1.5 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg" title="Hapus">
                                <i class="ti ti-trash text-sm"></i>
                            </button>
                        </form>
                        @endcan
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="px-5 py-12 text-center">
                    <i class="ti ti-box text-4xl text-slate-200 block mb-3"></i>
                    <p class="text-slate-400">Belum ada lokasi fisik terdaftar</p>
                    @can('physical_location.create')
                    <a href="{{ route('locations.create') }}"
                       class="mt-3 inline-flex items-center gap-1.5 text-sm text-sky-600 hover:underline">
                        <i class="ti ti-plus"></i> Tambah lokasi pertama
                    </a>
                    @endcan
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
    @if($query->hasPages())
    <div class="px-5 py-4 border-t border-slate-100">{{ $query->links() }}</div>
    @endif
</div>
@endsection
