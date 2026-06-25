@extends('layouts.app')
@section('title', 'Dashboard Eksekutif')

@section('breadcrumb')
    <a href="{{ route('dashboard') }}" class="text-slate-500 hover:text-slate-700 text-sm">Dashboard</a>
    <i class="ti ti-chevron-right text-slate-300 text-xs"></i>
    <span class="text-slate-800 font-medium text-sm">Eksekutif</span>
@endsection

@push('styles')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
@endpush

@section('content')

{{-- Header --}}
<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
    <div>
        <h1 class="text-xl font-semibold text-slate-800">Dashboard Eksekutif</h1>
        <p class="text-sm text-slate-500 mt-0.5">Ringkasan kinerja infrastruktur BBWS NT II — Tahun {{ $tahun }}</p>
    </div>
    <div class="flex items-center gap-2 text-xs text-slate-400">
        <i class="ti ti-refresh"></i>
        Diperbarui: {{ now()->format('d M Y H:i') }}
    </div>
</div>

{{-- KPI Cards --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="bg-white border border-slate-200 rounded-xl p-5">
        <p class="text-xs font-medium text-slate-500 uppercase tracking-wide">Total Nilai Kontrak</p>
        <p class="text-2xl font-bold text-slate-800 mt-1">
            Rp {{ number_format(($summary['total_nilai_kontrak'] ?? 0) / 1e9, 1) }} M
        </p>
        <p class="text-xs text-slate-400 mt-1">Tahun {{ $tahun }}</p>
    </div>
    <div class="bg-white border border-slate-200 rounded-xl p-5">
        <p class="text-xs font-medium text-slate-500 uppercase tracking-wide">Avg Realisasi Fisik</p>
        <p class="text-2xl font-bold text-slate-800 mt-1">{{ $summary['avg_realisasi_fisik'] ?? 0 }}%</p>
        <div class="w-full h-1.5 bg-slate-100 rounded-full mt-2">
            <div class="h-full bg-sky-500 rounded-full"
                 style="width: {{ min($summary['avg_realisasi_fisik'] ?? 0, 100) }}%"></div>
        </div>
    </div>
    <div class="bg-white border border-slate-200 rounded-xl p-5">
        <p class="text-xs font-medium text-slate-500 uppercase tracking-wide">Aset Kondisi Baik</p>
        <p class="text-2xl font-bold text-slate-800 mt-1">{{ $summary['aset_kondisi_baik_pct'] ?? 0 }}%</p>
        <div class="w-full h-1.5 bg-slate-100 rounded-full mt-2">
            <div class="h-full bg-emerald-500 rounded-full"
                 style="width: {{ min($summary['aset_kondisi_baik_pct'] ?? 0, 100) }}%"></div>
        </div>
    </div>
    <div class="bg-white border border-slate-200 rounded-xl p-5">
        <p class="text-xs font-medium text-slate-500 uppercase tracking-wide">OP Selesai</p>
        <p class="text-2xl font-bold text-slate-800 mt-1">{{ $summary['op_selesai_pct'] ?? 0 }}%</p>
        <div class="w-full h-1.5 bg-slate-100 rounded-full mt-2">
            <div class="h-full bg-amber-500 rounded-full"
                 style="width: {{ min($summary['op_selesai_pct'] ?? 0, 100) }}%"></div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">

    {{-- Tren OP bulanan --}}
    <div class="bg-white border border-slate-200 rounded-xl p-5">
        <h3 class="text-sm font-semibold text-slate-800 mb-4">Tren OP Bulanan {{ $tahun }}</h3>
        @if(count($trenOp) > 0)
        <canvas id="chartOP" height="200"></canvas>
        @else
        <div class="h-48 flex items-center justify-center text-slate-400">
            <div class="text-center">
                <i class="ti ti-chart-line text-3xl block mb-2"></i>
                <p class="text-sm">Belum ada data OP</p>
            </div>
        </div>
        @endif
    </div>

    {{-- Realisasi proyek per fase --}}
    <div class="bg-white border border-slate-200 rounded-xl p-5">
        <h3 class="text-sm font-semibold text-slate-800 mb-4">Status Proyek {{ $tahun }}</h3>
        @if(count($realisasiProyek) > 0)
        <canvas id="chartProyek" height="200"></canvas>
        @else
        <div class="h-48 flex items-center justify-center text-slate-400">
            <div class="text-center">
                <i class="ti ti-chart-bar text-3xl block mb-2"></i>
                <p class="text-sm">Belum ada data proyek</p>
            </div>
        </div>
        @endif
    </div>
</div>

{{-- Aset per jenis --}}
<div class="bg-white border border-slate-200 rounded-xl overflow-hidden mb-6">
    <div class="px-5 py-4 border-b border-slate-100">
        <h3 class="text-sm font-semibold text-slate-800">Kondisi Aset per Jenis</h3>
    </div>
    @if(count($asetPerJenis) > 0)
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-xs font-medium text-slate-500 uppercase tracking-wide">
                <tr>
                    <th class="px-5 py-3 text-left">Jenis Aset</th>
                    <th class="px-5 py-3 text-center">Total</th>
                    <th class="px-5 py-3 text-center">Kondisi A (Baik)</th>
                    <th class="px-5 py-3 text-center">Kondisi B (Sedang)</th>
                    <th class="px-5 py-3 text-center">Kondisi C/D (Buruk)</th>
                    <th class="px-5 py-3 text-left">Distribusi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach($asetPerJenis as $row)
                @php $total = $row->jumlah ?: 1; @endphp
                <tr class="hover:bg-slate-50">
                    <td class="px-5 py-3 font-medium text-slate-700">{{ $row->nama }}</td>
                    <td class="px-5 py-3 text-center font-semibold text-slate-800">{{ $row->jumlah }}</td>
                    <td class="px-5 py-3 text-center">
                        <span class="text-emerald-700 font-medium">{{ $row->kondisi_a }}</span>
                    </td>
                    <td class="px-5 py-3 text-center">
                        <span class="text-amber-700 font-medium">{{ $row->kondisi_b }}</span>
                    </td>
                    <td class="px-5 py-3 text-center">
                        <span class="text-red-600 font-medium">{{ $row->kondisi_buruk }}</span>
                    </td>
                    <td class="px-5 py-3">
                        @if($row->jumlah > 0)
                        <div class="flex h-2 rounded-full overflow-hidden w-32 gap-px">
                            <div class="bg-emerald-400 rounded-l-full"
                                 style="width: {{ round($row->kondisi_a / $total * 100) }}%"></div>
                            <div class="bg-amber-400"
                                 style="width: {{ round($row->kondisi_b / $total * 100) }}%"></div>
                            <div class="bg-red-400 rounded-r-full"
                                 style="width: {{ round($row->kondisi_buruk / $total * 100) }}%"></div>
                        </div>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <p class="px-5 py-8 text-center text-slate-400 text-sm">Belum ada data aset</p>
    @endif
</div>

{{-- Kelengkapan dokumen per satker --}}
<div class="bg-white border border-slate-200 rounded-xl overflow-hidden">
    <div class="px-5 py-4 border-b border-slate-100">
        <h3 class="text-sm font-semibold text-slate-800">Kelengkapan Dokumen per Satker</h3>
    </div>
    @if(count($kelengkapanDokPerSatker) > 0)
    <div class="divide-y divide-slate-100">
        @foreach($kelengkapanDokPerSatker as $row)
        @php $pct = (float)($row->pct_kelengkapan ?? 0); @endphp
        <div class="px-5 py-3 flex items-center gap-4">
            <div class="w-36 flex-shrink-0">
                <p class="text-sm font-medium text-slate-700 truncate">{{ $row->singkatan }}</p>
                <p class="text-xs text-slate-400">{{ $row->total_aset }} aset</p>
            </div>
            <div class="flex-1">
                <div class="flex items-center gap-3">
                    <div class="flex-1 h-2 bg-slate-100 rounded-full overflow-hidden">
                        <div class="h-full rounded-full transition-all"
                             style="width: {{ min($pct, 100) }}%;
                                    background: {{ $pct >= 80 ? '#22c55e' : ($pct >= 50 ? '#eab308' : '#ef4444') }}">
                        </div>
                    </div>
                    <span class="text-sm font-semibold text-slate-700 w-12 text-right">
                        {{ number_format($pct, 1) }}%
                    </span>
                </div>
            </div>
            <div class="text-xs text-slate-400 flex-shrink-0">
                {{ $row->aset_dengan_dokumen }}/{{ $row->total_aset }}
            </div>
        </div>
        @endforeach
    </div>
    @else
    <p class="px-5 py-8 text-center text-slate-400 text-sm">Belum ada data</p>
    @endif
</div>

@endsection

@push('scripts')
<script>
// Chart tren OP bulanan
@if(count($trenOp) > 0)
const opData = @json($trenOp);
const bulanNames = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Ags','Sep','Okt','Nov','Des'];

new Chart(document.getElementById('chartOP'), {
    type: 'bar',
    data: {
        labels: opData.map(d => bulanNames[d.periode_bulan - 1]),
        datasets: [{
            label: 'Avg Realisasi (%)',
            data: opData.map(d => parseFloat(d.avg_realisasi) || 0),
            backgroundColor: 'rgba(14, 165, 233, 0.7)',
            borderColor: 'rgb(14, 165, 233)',
            borderWidth: 1,
            borderRadius: 4,
        }, {
            label: 'Selesai',
            data: opData.map(d => parseInt(d.selesai) || 0),
            type: 'line',
            borderColor: 'rgb(34, 197, 94)',
            backgroundColor: 'rgba(34, 197, 94, 0.1)',
            borderWidth: 2,
            pointRadius: 4,
            tension: 0.3,
            yAxisID: 'y1',
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { position: 'bottom' } },
        scales: {
            y:  { beginAtZero: true, max: 100, title: { display: true, text: '%' } },
            y1: { beginAtZero: true, position: 'right', grid: { drawOnChartArea: false },
                  title: { display: true, text: 'Jumlah' } },
        }
    }
});
@endif

// Chart proyek per fase
@if(count($realisasiProyek) > 0)
const proyekData = @json($realisasiProyek);
const faseLabels = {
    'perencanaan': 'Perencanaan', 'pengadaan': 'Pengadaan',
    'pelaksanaan': 'Pelaksanaan', 'serah_terima_1': 'PHO',
    'pemeliharaan': 'Pemeliharaan', 'serah_terima_2': 'FHO',
    'selesai': 'Selesai', 'dibatalkan': 'Dibatalkan'
};
const faseColors = {
    'perencanaan': '#94a3b8', 'pengadaan': '#f59e0b',
    'pelaksanaan': '#3b82f6', 'serah_terima_1': '#8b5cf6',
    'pemeliharaan': '#06b6d4', 'serah_terima_2': '#10b981',
    'selesai': '#22c55e', 'dibatalkan': '#ef4444'
};

new Chart(document.getElementById('chartProyek'), {
    type: 'doughnut',
    data: {
        labels: proyekData.map(d => faseLabels[d.lifecycle_phase] || d.lifecycle_phase),
        datasets: [{
            data: proyekData.map(d => parseInt(d.jumlah)),
            backgroundColor: proyekData.map(d => faseColors[d.lifecycle_phase] || '#94a3b8'),
            borderWidth: 2,
            borderColor: '#fff',
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { position: 'bottom', labels: { padding: 12, font: { size: 11 } } }
        }
    }
});
@endif
</script>
@endpush
