@extends('layouts.app')
@section('title', 'Aset Infrastruktur')

@section('breadcrumb')
    <i class="ti ti-building-bridge text-slate-400"></i>
    <span class="text-slate-800 font-medium text-sm">Aset Infrastruktur</span>
@endsection

@section('content')
<x-page-header title="Aset Infrastruktur" desc="Data inventarisasi aset infrastruktur sumber daya air BBWS NT II" icon="ti-building-bridge">
    @can('asset.create')
    <a href="{{ route('assets.create') }}"
       class="inline-flex items-center gap-2 px-4 py-2 bg-sky-600 text-white text-sm font-medium rounded-xl hover:bg-sky-700 transition-colors">
        <i class="ti ti-plus"></i> Tambah Aset
    </a>
    @endcan
    <a href="{{ route('gis.assets') }}"
       class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-slate-200 text-slate-700 text-sm font-medium rounded-xl hover:bg-slate-50 transition-colors">
        <i class="ti ti-map-2 text-slate-500"></i> Lihat Peta
    </a>
</x-page-header>

{{-- Stats --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-5">
    <x-stat-card label="Total Aset"      value="{{ $stats['total'] }}"         icon="ti-building-bridge" color="sky" />
    <x-stat-card label="Kondisi Buruk"   value="{{ $stats['kondisi_buruk'] }}"  icon="ti-alert-triangle"  color="red" />
    <x-stat-card label="Belum Dinilai"   value="{{ $stats['belum_dinilai'] }}"  icon="ti-question-mark"   color="amber" />
    <x-stat-card label="Operasional"     value="{{ $stats['operating'] }}"      icon="ti-check"           color="green" />
</div>

{{-- Filter --}}
<div class="bg-white border border-slate-200 rounded-xl p-4 mb-4">
    <form method="GET" class="flex flex-wrap gap-2">
        <input type="text" name="search" value="{{ request('search') }}"
               placeholder="Cari nama, kode, kabupaten..."
               class="flex-1 min-w-48 border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500">
        <select name="asset_type_id" class="border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500">
            <option value="">Semua jenis</option>
            @foreach($assetTypes as $t)
            <option value="{{ $t->id }}" @selected(request('asset_type_id') === $t->id)>{{ $t->nama }}</option>
            @endforeach
        </select>
        <select name="kondisi" class="border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500">
            <option value="">Semua kondisi</option>
            @foreach(['A'=>'A — Baik','B'=>'B — Sedang','C'=>'C — Rusak Ringan','D'=>'D — Rusak Berat'] as $k=>$l)
            <option value="{{ $k }}" @selected(request('kondisi') === $k)>{{ $l }}</option>
            @endforeach
        </select>
        <select name="unit_kerja_id" class="border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500">
            <option value="">Semua satker</option>
            @foreach($unitKerjas as $uk)
            <option value="{{ $uk->id }}" @selected(request('unit_kerja_id') === $uk->id)>{{ $uk->singkatan }}</option>
            @endforeach
        </select>
        <button type="submit" class="px-4 py-2 bg-slate-800 text-white text-sm rounded-lg hover:bg-slate-700">
            <i class="ti ti-search"></i>
        </button>
        @if(request()->hasAny(['search','asset_type_id','kondisi','unit_kerja_id']))
        <a href="{{ route('assets.index') }}" class="px-4 py-2 text-sm text-slate-500 border border-slate-200 rounded-lg hover:bg-slate-50">
            Reset
        </a>
        @endif
    </form>
</div>

{{-- Tabel --}}
<div class="bg-white border border-slate-200 rounded-xl overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 border-b border-slate-200 text-xs font-medium text-slate-500 uppercase tracking-wide">
            <tr>
                <th class="px-5 py-3 text-left">Aset</th>
                <th class="px-5 py-3 text-left hidden md:table-cell">Jenis</th>
                <th class="px-5 py-3 text-left hidden lg:table-cell">Lokasi</th>
                <th class="px-5 py-3 text-left hidden xl:table-cell">Satker</th>
                <th class="px-5 py-3 text-center">Kondisi</th>
                <th class="px-5 py-3 text-center">Lifecycle</th>
                <th class="px-5 py-3 text-center">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse($query as $asset)
            <tr class="hover:bg-slate-50 transition-colors">
                <td class="px-5 py-3">
                    <div>
                        <a href="{{ route('assets.show', $asset) }}"
                           class="font-medium text-slate-800 hover:text-sky-600">
                            {{ $asset->nama }}
                        </a>
                        <p class="text-xs text-slate-400 mt-0.5 font-mono">{{ $asset->asset_code }}</p>
                    </div>
                </td>
                <td class="px-5 py-3 text-slate-600 hidden md:table-cell">
                    {{ $asset->assetType?->nama ?? '-' }}
                </td>
                <td class="px-5 py-3 hidden lg:table-cell">
                    <p class="text-slate-600 text-xs">{{ $asset->kabupaten }}</p>
                    <p class="text-slate-400 text-xs">{{ $asset->kecamatan }}</p>
                </td>
                <td class="px-5 py-3 hidden xl:table-cell">
                    <span class="text-xs text-slate-600">{{ $asset->unitKerja?->singkatan }}</span>
                </td>
                <td class="px-5 py-3 text-center">
                    @if($asset->kondisi_terakhir)
                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold {{ $asset->badgeKondisi() }}">
                        {{ $asset->kondisi_terakhir }}
                    </span>
                    @else
                    <span class="text-xs text-slate-400">—</span>
                    @endif
                </td>
                <td class="px-5 py-3 text-center">
                    <span class="text-xs text-slate-500">{{ $asset->labelLifecycle() }}</span>
                </td>
                <td class="px-5 py-3">
                    <div class="flex items-center justify-center gap-1">
                        <a href="{{ route('assets.show', $asset) }}"
                           class="p-1.5 text-slate-400 hover:text-sky-600 hover:bg-sky-50 rounded-lg" title="Detail">
                            <i class="ti ti-eye text-sm"></i>
                        </a>
                        @can('asset.edit')
                        <a href="{{ route('assets.edit', $asset) }}"
                           class="p-1.5 text-slate-400 hover:text-amber-600 hover:bg-amber-50 rounded-lg" title="Edit">
                            <i class="ti ti-edit text-sm"></i>
                        </a>
                        @endcan
                        @can('asset.delete')
                        <form action="{{ route('assets.destroy', $asset) }}" method="POST"
                              onsubmit="return confirm('Hapus aset {{ $asset->nama }}?')">
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
                <td colspan="7" class="px-5 py-12 text-center">
                    <i class="ti ti-building-bridge text-4xl text-slate-200 block mb-3"></i>
                    <p class="text-slate-400">Tidak ada aset ditemukan</p>
                    @can('asset.create')
                    <a href="{{ route('assets.create') }}"
                       class="mt-3 inline-flex items-center gap-1.5 text-sm text-sky-600 hover:underline">
                        <i class="ti ti-plus"></i> Tambah aset pertama
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
