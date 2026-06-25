@extends('layouts.app')
@section('title', 'Rekap & Laporan')

@section('breadcrumb')
    <i class="ti ti-chart-dots text-slate-400"></i>
    <span class="text-slate-800 font-medium text-sm">Rekap & Laporan</span>
@endsection

@section('content')
<x-page-header title="Rekap & Laporan" desc="Ringkasan data dan ekspor laporan BBWS NT II" icon="ti-chart-dots" />

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
    @foreach([
        ['route'=>'reports.assets',  'icon'=>'ti-building-bridge','color'=>'sky',   'title'=>'Laporan Aset',       'desc'=>'Daftar aset infrastruktur, kondisi, dan penilaian RCI'],
        ['route'=>'reports.op',      'icon'=>'ti-settings-2',     'color'=>'teal',  'title'=>'Laporan OP',         'desc'=>'Realisasi operasi dan pemeliharaan per tahun per aset'],
        ['route'=>'reports.projects','icon'=>'ti-timeline',       'color'=>'purple','title'=>'Laporan Proyek',     'desc'=>'Realisasi fisik dan keuangan proyek per tahun anggaran'],
        ['route'=>'reports.documents','icon'=>'ti-files',         'color'=>'amber', 'title'=>'Laporan Dokumen',    'desc'=>'Inventarisasi dokumen dan status kelengkapan arsip'],
        ['route'=>'reports.loans',   'icon'=>'ti-book-download',  'color'=>'green', 'title'=>'Laporan Peminjaman', 'desc'=>'Riwayat peminjaman dokumen fisik dan digital'],
    ] as $r)
    <a href="{{ route($r['route']) }}"
       class="bg-white border border-slate-200 rounded-xl p-5 hover:border-sky-200 hover:shadow-sm transition-all group">
        <div class="flex items-start gap-4">
            <div class="w-12 h-12 rounded-xl bg-{{ $r['color'] }}-50 border border-{{ $r['color'] }}-100
                        flex items-center justify-center flex-shrink-0">
                <i class="ti {{ $r['icon'] }} text-2xl text-{{ $r['color'] }}-600"></i>
            </div>
            <div>
                <p class="font-semibold text-slate-800 group-hover:text-sky-700">{{ $r['title'] }}</p>
                <p class="text-sm text-slate-500 mt-1">{{ $r['desc'] }}</p>
            </div>
        </div>
        <div class="mt-4 flex items-center gap-1 text-xs text-sky-600 font-medium">
            Buka laporan <i class="ti ti-arrow-right"></i>
        </div>
    </a>
    @endforeach
</div>

{{-- Quick stats --}}
<div class="mt-6 bg-white border border-slate-200 rounded-xl p-5">
    <h3 class="text-sm font-semibold text-slate-800 mb-4">Ringkasan Cepat — {{ now()->translatedFormat('F Y') }}</h3>
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        @php
        $qStats = [
            ['label'=>'Total Aset Aktif',       'value'=> \App\Models\Asset::aktif()->count(),           'icon'=>'ti-building-bridge','color'=>'sky'],
            ['label'=>'Proyek Berjalan',         'value'=> \App\Models\Project::aktif()->count(),         'icon'=>'ti-timeline',       'color'=>'purple'],
            ['label'=>'OP Selesai Bulan Ini',    'value'=> \App\Models\OpRecord::tahun(now()->year)->bulan(now()->month)->selesai()->count(), 'icon'=>'ti-check','color'=>'green'],
            ['label'=>'Dokumen Kadaluwarsa',     'value'=> \App\Models\Document::kadaluwarsa()->count(),  'icon'=>'ti-alert-circle',   'color'=>'red'],
        ];
        @endphp
        @foreach($qStats as $s)
        <div class="text-center p-3 bg-{{ $s['color'] }}-50 rounded-xl border border-{{ $s['color'] }}-100">
            <i class="ti {{ $s['icon'] }} text-2xl text-{{ $s['color'] }}-600 block mb-1"></i>
            <p class="text-2xl font-bold text-{{ $s['color'] }}-700">{{ $s['value'] }}</p>
            <p class="text-xs text-{{ $s['color'] }}-600 mt-0.5">{{ $s['label'] }}</p>
        </div>
        @endforeach
    </div>
</div>
@endsection
