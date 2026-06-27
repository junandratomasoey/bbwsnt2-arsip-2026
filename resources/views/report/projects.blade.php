{{-- resources/views/report/projects.blade.php --}}
@extends('layouts.app')
@section('title', 'Laporan Proyek')

@section('breadcrumb')
    <a href="{{ route('reports.index') }}" class="text-slate-500 hover:text-slate-700 text-sm">Laporan</a>
    <i class="ti ti-chevron-right text-slate-300 text-xs"></i>
    <span class="text-slate-800 font-medium text-sm">Proyek</span>
@endsection

@section('content')
<x-page-header :title="'Laporan Proyek TA ' . $tahun" icon="ti-timeline">
    @can('report.export')
    <a href="{{ route('reports.export.projects', ['tahun'=>$tahun]) }}"
       class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 text-white text-sm font-medium rounded-xl hover:bg-emerald-700">
        <i class="ti ti-file-excel"></i> Export Excel
    </a>
    @endcan
</x-page-header>

<div class="bg-white border border-slate-200 rounded-xl p-4 mb-4">
    <form method="GET" class="flex gap-2">
        <select name="tahun" class="border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500">
            @foreach($tahunList as $t)
            <option value="{{ $t }}" @selected($tahun == $t)>TA {{ $t }}</option>
            @endforeach
        </select>
        <button type="submit" class="px-4 py-2 bg-slate-800 text-white text-sm rounded-lg hover:bg-slate-700">Tampilkan</button>
    </form>
</div>

{{-- Summary --}}
<div class="grid grid-cols-2 sm:grid-cols-5 gap-3 mb-4">
    <div class="bg-white border border-slate-200 rounded-xl p-3 text-center">
        <p class="text-xl font-bold text-slate-800">{{ $summary['total'] }}</p>
        <p class="text-xs text-slate-500 mt-0.5">Total</p>
    </div>
    <div class="bg-white border border-slate-200 rounded-xl p-3 text-center">
        <p class="text-xl font-bold text-sky-700">{{ $summary['aktif'] }}</p>
        <p class="text-xs text-slate-500 mt-0.5">Aktif</p>
    </div>
    <div class="bg-white border border-slate-200 rounded-xl p-3 text-center">
        <p class="text-xl font-bold text-emerald-700">{{ $summary['selesai'] }}</p>
        <p class="text-xs text-slate-500 mt-0.5">Selesai</p>
    </div>
    <div class="col-span-2 bg-white border border-slate-200 rounded-xl p-3 text-center">
        <p class="text-xl font-bold text-slate-800">Rp {{ number_format(($summary['total_nilai_kontrak'] ?? 0) / 1e9, 1) }} M</p>
        <p class="text-xs text-slate-500 mt-0.5">Total Nilai Kontrak</p>
    </div>
</div>

<div class="bg-white border border-slate-200 rounded-xl overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 border-b border-slate-200 text-xs font-medium text-slate-500 uppercase tracking-wide">
                <tr>
                    <th class="px-4 py-3 text-left">Kode</th>
                    <th class="px-4 py-3 text-left">Nama Proyek</th>
                    <th class="px-4 py-3 text-left">Satker</th>
                    <th class="px-4 py-3 text-left">Kontraktor</th>
                    <th class="px-4 py-3 text-right">Nilai Kontrak</th>
                    <th class="px-4 py-3 text-center">Fisik</th>
                    <th class="px-4 py-3 text-center">Fase</th>
                    <th class="px-4 py-3 text-center">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($data as $p)
                <tr class="hover:bg-slate-50">
                    <td class="px-4 py-2.5 font-mono text-xs text-slate-500">{{ $p->project_code }}</td>
                    <td class="px-4 py-2.5"><a href="{{ route('projects.show',$p) }}" class="text-sky-600 hover:underline font-medium">{{ $p->nama }}</a></td>
                    <td class="px-4 py-2.5 text-xs text-slate-600">{{ $p->unitKerja?->singkatan }}</td>
                    <td class="px-4 py-2.5 text-xs text-slate-600 max-w-32 truncate">{{ $p->kontraktor ?? '—' }}</td>
                    <td class="px-4 py-2.5 text-right text-xs text-slate-700">{{ $p->nilai_kontrak ? 'Rp '.number_format($p->nilai_kontrak,0,',','.') : '—' }}</td>
                    <td class="px-4 py-2.5 text-center">
                        <span class="font-semibold text-slate-800">{{ $p->realisasi_fisik_pct }}%</span>
                    </td>
                    <td class="px-4 py-2.5 text-center text-xs text-slate-600">{{ $p->labelPhase() }}</td>
                    <td class="px-4 py-2.5 text-center">
                        <span class="inline-flex px-2 py-0.5 rounded text-xs {{ $p->badgeHealth() }}">{{ ucfirst($p->healthStatus()) }}</span>
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
