@extends('layouts.app')
@section('title', 'Rekaman OP')

@section('breadcrumb')
    <span class="text-slate-500 text-sm">OP</span>
    <i class="ti ti-chevron-right text-slate-300 text-xs"></i>
    <span class="text-slate-800 font-medium text-sm">Rekaman OP</span>
@endsection

@section('content')
<x-page-header title="Rekaman Operasi & Pemeliharaan" icon="ti-settings-2"
    desc="Rekaman pelaksanaan OP aset infrastruktur BBWS NT II">
    @can('op_record.create')
    <a href="{{ route('op.records.create') }}"
       class="inline-flex items-center gap-2 px-4 py-2 bg-sky-600 text-white text-sm font-medium rounded-xl hover:bg-sky-700">
        <i class="ti ti-plus"></i> Input OP
    </a>
    @endcan
</x-page-header>

{{-- Ringkasan status --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-5">
    @php
    $total = $ringkasan->sum();
    @endphp
    <x-stat-card label="Selesai"         value="{{ $ringkasan['selesai'] ?? 0 }}"          icon="ti-circle-check"     color="green" />
    <x-stat-card label="Berjalan"        value="{{ $ringkasan['berjalan'] ?? 0 }}"          icon="ti-loader"           color="sky" />
    <x-stat-card label="Belum"           value="{{ $ringkasan['belum'] ?? 0 }}"             icon="ti-clock"            color="amber" />
    <x-stat-card label="Tidak Terlaksana" value="{{ $ringkasan['tidak_terlaksana'] ?? 0 }}" icon="ti-circle-x"        color="red" />
</div>

{{-- Filter --}}
<div class="bg-white border border-slate-200 rounded-xl p-4 mb-4">
    <form method="GET" class="flex flex-wrap gap-2">
        <select name="tahun" class="border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500">
            @foreach($tahunList as $t)
            <option value="{{ $t }}" @selected($tahun == $t)>{{ $t }}</option>
            @endforeach
        </select>
        <select name="bulan" class="border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500">
            <option value="">Semua bulan</option>
            @foreach(range(1,12) as $m)
            <option value="{{ $m }}" @selected($bulan == $m)>
                {{ \Carbon\Carbon::create(null, $m)->translatedFormat('F') }}
            </option>
            @endforeach
        </select>
        <select name="status" class="border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500">
            <option value="">Semua status</option>
            @foreach(['belum'=>'Belum','berjalan'=>'Berjalan','selesai'=>'Selesai','tidak_terlaksana'=>'Tidak Terlaksana'] as $v=>$l)
            <option value="{{ $v }}" @selected(request('status') === $v)>{{ $l }}</option>
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
        <a href="{{ route('op.map') }}"
           class="px-3 py-2 text-sm border border-slate-200 rounded-lg text-slate-600 hover:bg-slate-50 flex items-center gap-1.5">
            <i class="ti ti-map-pin"></i> Peta Sebaran
        </a>
    </form>
</div>

{{-- Tabel --}}
<div class="bg-white border border-slate-200 rounded-xl overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 border-b border-slate-200 text-xs font-medium text-slate-500 uppercase tracking-wide">
            <tr>
                <th class="px-5 py-3 text-left">Aset</th>
                <th class="px-5 py-3 text-left hidden md:table-cell">Satker</th>
                <th class="px-5 py-3 text-center">Periode</th>
                <th class="px-5 py-3 text-center">Jenis</th>
                <th class="px-5 py-3 text-center">Realisasi</th>
                <th class="px-5 py-3 text-center">Status</th>
                <th class="px-5 py-3 text-center">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse($query as $op)
            <tr class="hover:bg-slate-50 transition-colors">
                <td class="px-5 py-3">
                    <a href="{{ route('assets.show', $op->asset) }}"
                       class="font-medium text-slate-800 hover:text-sky-600">
                        {{ $op->asset?->nama }}
                    </a>
                    <p class="text-xs text-slate-400 font-mono mt-0.5">{{ $op->asset?->asset_code }}</p>
                </td>
                <td class="px-5 py-3 hidden md:table-cell text-xs text-slate-600">
                    {{ $op->unitKerja?->singkatan }}
                </td>
                <td class="px-5 py-3 text-center text-xs text-slate-600">
                    {{ $op->labelBulan() }} {{ $op->periode_tahun }}
                </td>
                <td class="px-5 py-3 text-center">
                    <span class="text-xs px-2 py-0.5 rounded bg-slate-100 text-slate-600">
                        {{ ucfirst($op->jenis_op) }}
                    </span>
                </td>
                <td class="px-5 py-3 text-center">
                    <div class="flex flex-col items-center">
                        <span class="text-sm font-semibold text-slate-800">{{ $op->realisasi_pct }}%</span>
                        <div class="w-14 h-1.5 bg-slate-100 rounded-full mt-1">
                            <div class="h-full bg-sky-500 rounded-full"
                                 style="width: {{ min($op->realisasi_pct, 100) }}%"></div>
                        </div>
                    </div>
                </td>
                <td class="px-5 py-3 text-center">
                    <span class="inline-flex px-2 py-0.5 rounded text-xs {{ $op->badgeStatus() }}">
                        {{ $op->labelStatus() }}
                    </span>
                </td>
                <td class="px-5 py-3">
                    <div class="flex items-center justify-center gap-1">
                        <a href="{{ route('op.records.show', $op) }}"
                           class="p-1.5 text-slate-400 hover:text-sky-600 hover:bg-sky-50 rounded-lg">
                            <i class="ti ti-eye text-sm"></i>
                        </a>
                        @can('op_record.edit')
                        <a href="{{ route('op.records.edit', $op) }}"
                           class="p-1.5 text-slate-400 hover:text-amber-600 hover:bg-amber-50 rounded-lg">
                            <i class="ti ti-edit text-sm"></i>
                        </a>
                        @endcan
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="7" class="px-5 py-10 text-center text-slate-400">Tidak ada rekaman OP.</td></tr>
            @endforelse
        </tbody>
    </table>
    @if($query->hasPages())
    <div class="px-5 py-4 border-t border-slate-100">{{ $query->links() }}</div>
    @endif
</div>
@endsection
