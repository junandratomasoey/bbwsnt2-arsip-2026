@extends('layouts.app')
@section('title', 'Dashboard')

@section('breadcrumb')
    <i class="ti ti-home-2 text-slate-400"></i>
    <span class="text-slate-800 font-medium text-sm">Dashboard</span>
@endsection

@section('content')

{{-- ── STAT CARDS ──────────────────────────────────────────────── --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <x-stat-card
        label="Total Aset"
        value="{{ number_format($totalAset) }}"
        icon="ti-building-bridge"
        color="sky"
        :sub="$asetBuruk . ' kondisi buruk'"
        :href="route('assets.index')" />

    <x-stat-card
        label="Total Dokumen"
        value="{{ number_format($totalDokumen) }}"
        icon="ti-files"
        color="purple"
        :sub="$dokumenExpired . ' kadaluwarsa'"
        :href="route('documents.index')" />

    <x-stat-card
        label="Proyek Aktif"
        value="{{ $proyekAktif }}"
        icon="ti-timeline"
        color="amber"
        sub="Tahun {{ $tahun }}"
        :href="route('projects.index')" />

    <x-stat-card
        label="Peminjaman"
        value="{{ $peminjamanAktif }}"
        icon="ti-book-download"
        color="green"
        sub="Aktif / menunggu"
        :href="route('loans.index')" />
</div>

{{-- ── MAIN GRID ────────────────────────────────────────────────── --}}
<div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

    {{-- Kolom kiri (2/3) --}}
    <div class="xl:col-span-2 space-y-6">

        {{-- Status OP bulan ini --}}
        <div class="bg-white border border-slate-200 rounded-xl overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h2 class="text-sm font-semibold text-slate-800">
                        Status OP Bulan {{ now()->translatedFormat('F Y') }}
                    </h2>
                    <p class="text-xs text-slate-400 mt-0.5">Operasi & Pemeliharaan infrastruktur</p>
                </div>
                <a href="{{ route('op.records.index') }}"
                   class="text-xs text-sky-600 hover:underline flex items-center gap-1">
                    Lihat semua <i class="ti ti-arrow-right"></i>
                </a>
            </div>
            <div class="p-5">
                @php
                    $opSelesai       = $opBulanIni['selesai'] ?? 0;
                    $opBerjalan      = $opBulanIni['berjalan'] ?? 0;
                    $opBelum         = $opBulanIni['belum'] ?? 0;
                    $opTidakLaksana  = $opBulanIni['tidak_terlaksana'] ?? 0;
                    $totalOp         = $opSelesai + $opBerjalan + $opBelum + $opTidakLaksana;
                    $pctSelesai      = $totalOp > 0 ? round($opSelesai / $totalOp * 100) : 0;
                @endphp
                <div class="grid grid-cols-4 gap-4 mb-4">
                    <div class="text-center p-3 bg-emerald-50 rounded-xl">
                        <p class="text-2xl font-semibold text-emerald-700">{{ $opSelesai }}</p>
                        <p class="text-xs text-emerald-600 mt-0.5">Selesai</p>
                    </div>
                    <div class="text-center p-3 bg-blue-50 rounded-xl">
                        <p class="text-2xl font-semibold text-blue-700">{{ $opBerjalan }}</p>
                        <p class="text-xs text-blue-600 mt-0.5">Berjalan</p>
                    </div>
                    <div class="text-center p-3 bg-slate-50 rounded-xl">
                        <p class="text-2xl font-semibold text-slate-600">{{ $opBelum }}</p>
                        <p class="text-xs text-slate-500 mt-0.5">Belum</p>
                    </div>
                    <div class="text-center p-3 bg-red-50 rounded-xl">
                        <p class="text-2xl font-semibold text-red-600">{{ $opTidakLaksana }}</p>
                        <p class="text-xs text-red-500 mt-0.5">Tidak Terlaksana</p>
                    </div>
                </div>
                <div class="flex items-center justify-between text-xs text-slate-500 mb-1.5">
                    <span>Persentase selesai</span>
                    <span class="font-medium text-slate-700">{{ $pctSelesai }}%</span>
                </div>
                <div class="h-2 bg-slate-100 rounded-full overflow-hidden">
                    <div class="h-full bg-emerald-500 rounded-full transition-all duration-500"
                         style="width: {{ $pctSelesai }}%"></div>
                </div>
            </div>
        </div>

        {{-- Aset kondisi buruk --}}
        @if($asetKondisiBuruk->isNotEmpty())
        <div class="bg-white border border-red-100 rounded-xl overflow-hidden">
            <div class="px-5 py-3.5 border-b border-red-100 bg-red-50/50 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <i class="ti ti-alert-triangle text-red-500"></i>
                    <h2 class="text-sm font-semibold text-slate-800">Aset Kondisi Buruk (C/D)</h2>
                </div>
                <a href="{{ route('assets.index', ['kondisi' => 'C']) }}"
                   class="text-xs text-red-600 hover:underline">Lihat semua →</a>
            </div>
            <div class="divide-y divide-slate-100">
                @foreach($asetKondisiBuruk as $aset)
                <div class="px-5 py-3 flex items-center justify-between gap-4">
                    <div class="min-w-0">
                        <a href="{{ route('assets.show', $aset) }}"
                           class="text-sm font-medium text-slate-800 hover:text-sky-600 truncate block">
                            {{ $aset->nama }}
                        </a>
                        <p class="text-xs text-slate-400 mt-0.5">
                            {{ $aset->assetType?->nama }} ·
                            {{ $aset->unitKerja?->singkatan }} ·
                            {{ $aset->kabupaten }}
                        </p>
                    </div>
                    <div class="flex items-center gap-2 flex-shrink-0">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold
                                     {{ $aset->badgeKondisi() }}">
                            Kondisi {{ $aset->kondisi_terakhir ?? '-' }}
                        </span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Proyek terlambat --}}
        @if($proyekTerlambat->isNotEmpty())
        <div class="bg-white border border-amber-100 rounded-xl overflow-hidden">
            <div class="px-5 py-3.5 border-b border-amber-100 bg-amber-50/50 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <i class="ti ti-clock-exclamation text-amber-500"></i>
                    <h2 class="text-sm font-semibold text-slate-800">Proyek Terlambat</h2>
                </div>
                <a href="{{ route('projects.index', ['filter' => 'terlambat']) }}"
                   class="text-xs text-amber-600 hover:underline">Lihat semua →</a>
            </div>
            <div class="divide-y divide-slate-100">
                @foreach($proyekTerlambat as $proyek)
                <div class="px-5 py-3 flex items-center justify-between gap-4">
                    <div class="min-w-0">
                        <a href="{{ route('projects.show', $proyek) }}"
                           class="text-sm font-medium text-slate-800 hover:text-sky-600 truncate block">
                            {{ $proyek->nama }}
                        </a>
                        <p class="text-xs text-slate-400 mt-0.5">
                            {{ $proyek->unitKerja?->singkatan }} ·
                            Selesai: {{ $proyek->tgl_selesai_rencana?->format('d M Y') }}
                        </p>
                    </div>
                    <div class="flex-shrink-0 text-right">
                        <p class="text-sm font-semibold text-amber-700">
                            +{{ $proyek->tgl_selesai_rencana?->diffInDays(now()) }} hari
                        </p>
                        <div class="w-16 h-1.5 bg-slate-100 rounded-full mt-1">
                            <div class="h-full bg-sky-500 rounded-full"
                                 style="width: {{ min($proyek->realisasi_fisik_pct, 100) }}%"></div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

    </div>

    {{-- Kolom kanan (1/3) --}}
    <div class="space-y-6">

        {{-- Peminjaman menunggu --}}
        @if($peminjamanMenunggu->isNotEmpty())
        <div class="bg-white border border-slate-200 rounded-xl overflow-hidden">
            <div class="px-5 py-3.5 border-b border-slate-100 flex items-center justify-between">
                <h2 class="text-sm font-semibold text-slate-800">Perlu Persetujuan</h2>
                <a href="{{ route('loans.index', ['status' => 'requested']) }}"
                   class="text-xs text-sky-600 hover:underline">Kelola →</a>
            </div>
            <div class="divide-y divide-slate-100">
                @foreach($peminjamanMenunggu as $loan)
                <div class="px-5 py-3">
                    <p class="text-xs font-medium text-slate-800 truncate">
                        {{ $loan->document?->judul }}
                    </p>
                    <p class="text-xs text-slate-400 mt-0.5">
                        {{ $loan->borrower?->name }} ·
                        {{ $loan->created_at->diffForHumans() }}
                    </p>
                    <div class="flex gap-2 mt-2">
                        <form action="{{ route('loans.approve', $loan) }}" method="POST">
                            @csrf
                            <button class="text-xs px-2.5 py-1 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700">
                                Setujui
                            </button>
                        </form>
                        <a href="{{ route('loans.show', $loan) }}"
                           class="text-xs px-2.5 py-1 bg-slate-100 text-slate-600 rounded-lg hover:bg-slate-200">
                            Detail
                        </a>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- User pending --}}
        @if(!empty($usersPending) && count($usersPending) > 0)
        <div class="bg-white border border-slate-200 rounded-xl overflow-hidden">
            <div class="px-5 py-3.5 border-b border-slate-100 flex items-center justify-between">
                <h2 class="text-sm font-semibold text-slate-800">Akun Menunggu</h2>
                <a href="{{ route('admin.approvals') }}" class="text-xs text-sky-600 hover:underline">Proses →</a>
            </div>
            <div class="divide-y divide-slate-100">
                @foreach($usersPending as $u)
                <div class="px-5 py-3 flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-slate-200 flex items-center justify-center
                                text-slate-600 text-xs font-semibold flex-shrink-0">
                        {{ $u->inisial() }}
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm font-medium text-slate-800 truncate">{{ $u->name }}</p>
                        <p class="text-xs text-slate-400">{{ $u->unitKerja?->singkatan ?? '-' }}</p>
                    </div>
                    <span class="ml-auto text-xs bg-amber-100 text-amber-700 px-2 py-0.5 rounded-md flex-shrink-0">
                        Pending
                    </span>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Dokumen hampir kadaluwarsa --}}
        @if($dokumenMauExpired->isNotEmpty())
        <div class="bg-white border border-slate-200 rounded-xl overflow-hidden">
            <div class="px-5 py-3.5 border-b border-slate-100 flex items-center justify-between">
                <h2 class="text-sm font-semibold text-slate-800">Dokumen Segera Kadaluwarsa</h2>
            </div>
            <div class="divide-y divide-slate-100">
                @foreach($dokumenMauExpired as $doc)
                <div class="px-5 py-3">
                    <a href="{{ route('documents.show', $doc) }}"
                       class="text-xs font-medium text-slate-800 hover:text-sky-600 truncate block">
                        {{ $doc->judul }}
                    </a>
                    <p class="text-xs text-red-500 mt-0.5">
                        Kadaluwarsa: {{ $doc->tgl_kedaluwarsa?->format('d M Y') }}
                        ({{ $doc->tgl_kedaluwarsa?->diffForHumans() }})
                    </p>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Aktivitas terbaru --}}
        <div class="bg-white border border-slate-200 rounded-xl overflow-hidden">
            <div class="px-5 py-3.5 border-b border-slate-100">
                <h2 class="text-sm font-semibold text-slate-800">Aktivitas Terbaru</h2>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse($aktivitasTerbaru as $log)
                <div class="px-5 py-3 flex items-start gap-3">
                    <div class="w-6 h-6 rounded-full bg-sky-100 flex items-center justify-center flex-shrink-0 mt-0.5">
                        <i class="ti ti-activity text-sky-600 text-xs"></i>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs text-slate-700 leading-snug">
                            <span class="font-medium">{{ $log->user_name ?? 'Sistem' }}</span>
                            {{ $log->action }} {{ $log->entity_type }}
                        </p>
                        @if($log->entity_label)
                        <p class="text-xs text-slate-500 mt-0.5 truncate">{{ $log->entity_label }}</p>
                        @endif
                        <p class="text-xs text-slate-400 mt-0.5">
                            {{ $log->created_at->diffForHumans() }}
                        </p>
                    </div>
                </div>
                @empty
                <p class="px-5 py-6 text-center text-xs text-slate-400">Belum ada aktivitas</p>
                @endforelse
            </div>
        </div>

    </div>
</div>
@endsection
