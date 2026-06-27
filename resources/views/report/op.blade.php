{{-- resources/views/report/op.blade.php --}}
@extends('layouts.app')
@section('title', 'Laporan OP')

@section('breadcrumb')
    <a href="{{ route('reports.index') }}" class="text-slate-500 hover:text-slate-700 text-sm">Laporan</a>
    <i class="ti ti-chevron-right text-slate-300 text-xs"></i>
    <span class="text-slate-800 font-medium text-sm">OP</span>
@endsection

@section('content')
<x-page-header :title="'Laporan OP Tahun ' . $tahun" icon="ti-settings-2">
    @can('report.export')
    <a href="{{ route('reports.export.op', ['tahun'=>$tahun]) }}"
       class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 text-white text-sm font-medium rounded-xl hover:bg-emerald-700">
        <i class="ti ti-file-excel"></i> Export Excel
    </a>
    @endcan
</x-page-header>

<div class="bg-white border border-slate-200 rounded-xl p-4 mb-4">
    <form method="GET" class="flex gap-2">
        <select name="tahun" class="border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500">
            @foreach($tahunList as $t)
            <option value="{{ $t }}" @selected($tahun == $t)>Tahun {{ $t }}</option>
            @endforeach
        </select>
        <button type="submit" class="px-4 py-2 bg-slate-800 text-white text-sm rounded-lg hover:bg-slate-700">Tampilkan</button>
    </form>
</div>

<div class="bg-white border border-slate-200 rounded-xl overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 border-b border-slate-200 text-xs font-medium text-slate-500 uppercase tracking-wide">
                <tr>
                    <th class="px-4 py-3 text-left">Kode</th>
                    <th class="px-4 py-3 text-left">Nama Aset</th>
                    <th class="px-4 py-3 text-left">Jenis</th>
                    <th class="px-4 py-3 text-left">Satker</th>
                    <th class="px-4 py-3 text-center">Total OP</th>
                    <th class="px-4 py-3 text-center">Selesai</th>
                    <th class="px-4 py-3 text-center">Avg Realisasi</th>
                    <th class="px-4 py-3 text-center">Kinerja</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($data as $row)
                @php
                $pct = (float)($row->avg_realisasi ?? 0);
                $badge = $pct >= 90 ? 'bg-green-100 text-green-700' : ($pct >= 70 ? 'bg-amber-100 text-amber-700' : 'bg-red-100 text-red-700');
                @endphp
                <tr class="hover:bg-slate-50">
                    <td class="px-4 py-2.5 font-mono text-xs text-slate-500">{{ $row->asset_code }}</td>
                    <td class="px-4 py-2.5 font-medium text-slate-800">{{ $row->nama_aset }}</td>
                    <td class="px-4 py-2.5 text-xs text-slate-600">{{ $row->jenis }}</td>
                    <td class="px-4 py-2.5 text-xs text-slate-600">{{ $row->satker }}</td>
                    <td class="px-4 py-2.5 text-center text-slate-700">{{ $row->total_op }}</td>
                    <td class="px-4 py-2.5 text-center text-slate-700">{{ $row->selesai }}</td>
                    <td class="px-4 py-2.5 text-center">
                        <div class="flex flex-col items-center">
                            <span class="font-semibold text-slate-800">{{ number_format($pct, 1) }}%</span>
                            <div class="w-16 h-1.5 bg-slate-100 rounded-full mt-0.5">
                                <div class="h-full rounded-full {{ $pct >= 90 ? 'bg-green-500' : ($pct >= 70 ? 'bg-amber-500' : 'bg-red-500') }}"
                                     style="width: {{ min($pct, 100) }}%"></div>
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-2.5 text-center">
                        @if($row->total_op > 0)
                        <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium {{ $badge }}">
                            {{ $pct >= 90 ? 'Sangat Baik' : ($pct >= 70 ? 'Baik' : ($pct >= 50 ? 'Cukup' : 'Kurang')) }}
                        </span>
                        @else
                        <span class="text-xs text-slate-400">Tidak ada data</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="px-5 py-8 text-center text-slate-400">Tidak ada data</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
