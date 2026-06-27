@extends('layouts.app')
@section('title', 'Detail Jadwal OP')

@section('breadcrumb')
    <a href="{{ route('op.schedules.index') }}" class="text-slate-500 hover:text-slate-700 text-sm">Jadwal OP</a>
    <i class="ti ti-chevron-right text-slate-300 text-xs"></i>
    <span class="text-slate-800 font-medium text-sm">Detail</span>
@endsection

@section('content')
<x-page-header :title="'Jadwal OP — ' . $schedule->asset?->nama" icon="ti-calendar-event">
    @if($schedule->status === 'draft')
        @can('op_schedule.edit')
        <a href="{{ route('op.schedules.edit', $schedule) }}"
           class="inline-flex items-center gap-1.5 px-4 py-2 bg-white border border-slate-200 text-sm text-slate-700 rounded-xl hover:bg-slate-50">
            <i class="ti ti-edit text-slate-400"></i> Edit
        </a>
        @endcan
        @can('op_schedule.approve')
        <form action="{{ route('op.schedules.approve', $schedule) }}" method="POST">
            @csrf
            <button onclick="return confirm('Setujui jadwal OP ini?')"
                    class="inline-flex items-center gap-1.5 px-4 py-2 bg-emerald-600 text-white text-sm rounded-xl hover:bg-emerald-700">
                <i class="ti ti-check"></i> Setujui
            </button>
        </form>
        @endcan
    @endif
</x-page-header>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-5">

        {{-- Info jadwal --}}
        <div class="bg-white border border-slate-200 rounded-xl p-5">
            <h3 class="text-sm font-semibold text-slate-700 mb-4 pb-2 border-b border-slate-100">Informasi Jadwal</h3>
            <div class="grid grid-cols-2 gap-4">
                <div><p class="text-xs text-slate-400 mb-0.5">Aset</p>
                    <a href="{{ route('assets.show', $schedule->asset) }}" class="text-sm font-medium text-sky-600 hover:underline">
                        {{ $schedule->asset?->nama }}
                    </a>
                </div>
                <div><p class="text-xs text-slate-400 mb-0.5">Satker</p>
                    <p class="text-sm text-slate-700">{{ $schedule->unitKerja?->singkatan }}</p>
                </div>
                <div><p class="text-xs text-slate-400 mb-0.5">Tahun</p>
                    <p class="text-sm font-semibold text-slate-700">{{ $schedule->tahun }}</p>
                </div>
                <div><p class="text-xs text-slate-400 mb-0.5">Status</p>
                    @php $badge = match($schedule->status){ 'approved'=>'bg-green-100 text-green-700','berjalan'=>'bg-blue-100 text-blue-700','selesai'=>'bg-slate-100 text-slate-600',default=>'bg-amber-100 text-amber-700' }; @endphp
                    <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium {{ $badge }}">{{ ucfirst($schedule->status) }}</span>
                </div>
                <div><p class="text-xs text-slate-400 mb-0.5">Anggaran OP Rutin</p>
                    <p class="text-sm text-slate-700">{{ $schedule->anggaran_op_rutin ? 'Rp ' . number_format($schedule->anggaran_op_rutin, 0, ',', '.') : '—' }}</p>
                </div>
                <div><p class="text-xs text-slate-400 mb-0.5">Anggaran OP Berkala</p>
                    <p class="text-sm text-slate-700">{{ $schedule->anggaran_op_berkala ? 'Rp ' . number_format($schedule->anggaran_op_berkala, 0, ',', '.') : '—' }}</p>
                </div>
                @if($schedule->kode_dipa)
                <div class="col-span-2"><p class="text-xs text-slate-400 mb-0.5">Kode DIPA</p>
                    <p class="text-sm font-mono text-slate-700">{{ $schedule->kode_dipa }}</p>
                </div>
                @endif
            </div>
        </div>

        {{-- Rencana kegiatan per bulan --}}
        @if($schedule->rencana_kegiatan)
        <div class="bg-white border border-slate-200 rounded-xl overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100">
                <h3 class="text-sm font-semibold text-slate-700">Rencana Kegiatan per Bulan</h3>
            </div>
            @php $bulanNames = ['','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember']; @endphp
            <div class="divide-y divide-slate-100">
                @foreach($schedule->rencana_kegiatan as $bulan => $kegiatan)
                <div class="px-5 py-3">
                    <p class="text-xs font-semibold text-slate-600 mb-1.5">{{ $bulanNames[(int)$bulan] ?? "Bulan $bulan" }}</p>
                    @if(is_array($kegiatan))
                        <ul class="space-y-0.5">
                            @foreach($kegiatan as $k)
                            <li class="text-sm text-slate-600 flex items-center gap-2">
                                <i class="ti ti-circle-filled text-sky-400 text-[6px]"></i> {{ $k }}
                            </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-sm text-slate-600">{{ $kegiatan }}</p>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Rekaman OP terkait --}}
        @if($schedule->opRecords->isNotEmpty())
        <div class="bg-white border border-slate-200 rounded-xl overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-slate-700">Rekaman OP Terkait</h3>
                <a href="{{ route('op.records.index') }}" class="text-xs text-sky-600 hover:underline">Lihat semua →</a>
            </div>
            <div class="divide-y divide-slate-100">
                @foreach($schedule->opRecords as $op)
                <div class="px-5 py-3 flex items-center justify-between">
                    <div>
                        <p class="text-sm text-slate-700">{{ $op->labelBulan() }} {{ $op->periode_tahun }}</p>
                        <p class="text-xs text-slate-400">{{ ucfirst($op->jenis_op) }}</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="text-sm font-semibold text-slate-800">{{ $op->realisasi_pct }}%</span>
                        <span class="text-xs px-2 py-0.5 rounded {{ $op->badgeStatus() }}">{{ $op->labelStatus() }}</span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    {{-- Sidebar --}}
    <div class="space-y-5">
        <div class="bg-white border border-slate-200 rounded-xl p-5">
            <h3 class="text-sm font-semibold text-slate-700 mb-3">Ringkasan Realisasi</h3>
            @php
                $total   = $schedule->opRecords->count();
                $selesai = $schedule->opRecords->where('status','selesai')->count();
                $pct     = $total > 0 ? round($selesai / $total * 100) : 0;
            @endphp
            <div class="text-center py-4">
                <p class="text-3xl font-bold text-slate-800">{{ $pct }}%</p>
                <p class="text-xs text-slate-400 mt-1">{{ $selesai }} dari {{ $total }} bulan selesai</p>
            </div>
            <div class="h-2 bg-slate-100 rounded-full overflow-hidden">
                <div class="h-full bg-emerald-500 rounded-full" style="width: {{ $pct }}%"></div>
            </div>
            @can('op_record.create')
            <a href="{{ route('op.records.create', ['asset_id' => $schedule->asset_id]) }}"
               class="mt-4 w-full inline-flex items-center justify-center gap-2 px-4 py-2
                      bg-sky-600 text-white text-sm rounded-xl hover:bg-sky-700">
                <i class="ti ti-plus"></i> Input Rekaman OP
            </a>
            @endcan
        </div>
    </div>
</div>
@endsection
