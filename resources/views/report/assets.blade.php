{{-- resources/views/report/assets.blade.php --}}
@extends('layouts.app')
@section('title', 'Laporan Aset')

@section('breadcrumb')
    <a href="{{ route('reports.index') }}" class="text-slate-500 hover:text-slate-700 text-sm">Laporan</a>
    <i class="ti ti-chevron-right text-slate-300 text-xs"></i>
    <span class="text-slate-800 font-medium text-sm">Aset</span>
@endsection

@section('content')
<x-page-header title="Laporan Aset Infrastruktur" icon="ti-building-bridge">
    @can('report.export')
    <a href="{{ route('reports.export.assets', request()->query()) }}"
       class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 text-white text-sm font-medium rounded-xl hover:bg-emerald-700">
        <i class="ti ti-file-excel"></i> Export Excel
    </a>
    @endcan
</x-page-header>

{{-- Filter --}}
<div class="bg-white border border-slate-200 rounded-xl p-4 mb-4">
    <form method="GET" class="flex flex-wrap gap-2">
        <select name="asset_type_id" class="border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500">
            <option value="">Semua jenis</option>
            @foreach($assetTypes as $t)
            <option value="{{ $t->id }}" @selected(request('asset_type_id') === $t->id)>{{ $t->nama }}</option>
            @endforeach
        </select>
        <select name="kondisi" class="border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500">
            <option value="">Semua kondisi</option>
            @foreach(['A'=>'A - Baik','B'=>'B - Sedang','C'=>'C - Rusak Ringan','D'=>'D - Rusak Berat'] as $v=>$l)
            <option value="{{ $v }}" @selected(request('kondisi') === $v)>{{ $l }}</option>
            @endforeach
        </select>
        <select name="unit_kerja_id" class="border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500">
            <option value="">Semua satker</option>
            @foreach($unitKerjas as $uk)
            <option value="{{ $uk->id }}" @selected(request('unit_kerja_id') === $uk->id)>{{ $uk->singkatan }}</option>
            @endforeach
        </select>
        <button type="submit" class="px-4 py-2 bg-slate-800 text-white text-sm rounded-lg hover:bg-slate-700">Filter</button>
        @if(request()->hasAny(['asset_type_id','kondisi','unit_kerja_id']))
        <a href="{{ route('reports.assets') }}" class="px-4 py-2 text-sm border border-slate-200 text-slate-500 rounded-lg">Reset</a>
        @endif
    </form>
</div>

{{-- Summary --}}
<div class="grid grid-cols-3 sm:grid-cols-6 gap-3 mb-4">
    @foreach(['total'=>['Total','sky','ti-building-bridge'],'kondisi_a'=>['Baik (A)','green','ti-circle-check'],'kondisi_b'=>['Sedang (B)','yellow','ti-circle-half'],'kondisi_c'=>['Rusak Ringan (C)','orange','ti-alert-triangle'],'kondisi_d'=>['Rusak Berat (D)','red','ti-circle-x'],'belum_dinilai'=>['Belum Dinilai','slate','ti-question-mark']] as $key=>[$label,$color,$icon])
    <div class="bg-white border border-slate-200 rounded-xl p-3 text-center">
        <p class="text-xl font-bold text-slate-800">{{ $summary[$key] }}</p>
        <p class="text-xs text-slate-500 mt-0.5">{{ $label }}</p>
    </div>
    @endforeach
</div>

{{-- Tabel --}}
<div class="bg-white border border-slate-200 rounded-xl overflow-hidden">
    <div class="px-5 py-3 bg-slate-50 border-b border-slate-200 text-xs font-medium text-slate-500">
        {{ $data->count() }} aset ditemukan
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 border-b border-slate-200 text-xs font-medium text-slate-500 uppercase tracking-wide">
                <tr>
                    <th class="px-4 py-3 text-left">Kode</th>
                    <th class="px-4 py-3 text-left">Nama</th>
                    <th class="px-4 py-3 text-left">Jenis</th>
                    <th class="px-4 py-3 text-left">Satker</th>
                    <th class="px-4 py-3 text-left">Kabupaten</th>
                    <th class="px-4 py-3 text-center">Thn Bangun</th>
                    <th class="px-4 py-3 text-center">Kondisi</th>
                    <th class="px-4 py-3 text-center">RCI</th>
                    <th class="px-4 py-3 text-center">Lifecycle</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($data as $asset)
                <tr class="hover:bg-slate-50">
                    <td class="px-4 py-2.5 font-mono text-xs text-slate-500">{{ $asset->asset_code }}</td>
                    <td class="px-4 py-2.5"><a href="{{ route('assets.show',$asset) }}" class="text-sky-600 hover:underline">{{ $asset->nama }}</a></td>
                    <td class="px-4 py-2.5 text-xs text-slate-600">{{ $asset->assetType?->nama }}</td>
                    <td class="px-4 py-2.5 text-xs text-slate-600">{{ $asset->unitKerja?->singkatan }}</td>
                    <td class="px-4 py-2.5 text-xs text-slate-600">{{ $asset->kabupaten }}</td>
                    <td class="px-4 py-2.5 text-center text-xs text-slate-600">{{ $asset->tahun_bangun ?? '—' }}</td>
                    <td class="px-4 py-2.5 text-center">
                        @if($asset->kondisi_terakhir)
                        <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium {{ $asset->badgeKondisi() }}">{{ $asset->kondisi_terakhir }}</span>
                        @else<span class="text-xs text-slate-400">—</span>@endif
                    </td>
                    <td class="px-4 py-2.5 text-center text-xs text-slate-600">{{ $asset->rci_score_terakhir ?? '—' }}</td>
                    <td class="px-4 py-2.5 text-center text-xs text-slate-600">{{ $asset->labelLifecycle() }}</td>
                </tr>
                @empty
                <tr><td colspan="9" class="px-5 py-8 text-center text-slate-400">Tidak ada data</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
