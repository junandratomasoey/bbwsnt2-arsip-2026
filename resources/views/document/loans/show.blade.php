@extends('layouts.app')
@section('title', 'Detail Peminjaman')

@section('breadcrumb')
    <a href="{{ route('loans.index') }}" class="text-slate-500 hover:text-slate-700 text-sm">Peminjaman</a>
    <i class="ti ti-chevron-right text-slate-300 text-xs"></i>
    <span class="text-slate-800 font-medium text-sm">Detail</span>
@endsection

@section('content')
<x-page-header title="Detail Peminjaman" icon="ti-book-download">
    @can('loan.approve')
    @if($loan->status === 'requested')
    <form action="{{ route('loans.approve', $loan) }}" method="POST" class="inline">
        @csrf
        <button class="inline-flex items-center gap-1.5 px-4 py-2 bg-emerald-600 text-white text-sm
                       font-medium rounded-xl hover:bg-emerald-700">
            <i class="ti ti-check"></i> Setujui
        </button>
    </form>
    <button onclick="document.getElementById('form-tolak').classList.toggle('hidden')"
            class="inline-flex items-center gap-1.5 px-4 py-2 border border-red-200 text-red-600
                   text-sm rounded-xl hover:bg-red-50">
        <i class="ti ti-x"></i> Tolak
    </button>
    @endif
    @if($loan->status === 'borrowed')
    <form action="{{ route('loans.kembalikan', $loan) }}" method="POST">
        @csrf
        <button onclick="return confirm('Konfirmasi dokumen sudah dikembalikan?')"
                class="inline-flex items-center gap-1.5 px-4 py-2 bg-sky-600 text-white text-sm
                       font-medium rounded-xl hover:bg-sky-700">
            <i class="ti ti-book-upload"></i> Tandai Dikembalikan
        </button>
    </form>
    @endif
    @endcan
</x-page-header>

{{-- Form tolak --}}
@can('loan.approve')
<div id="form-tolak" class="hidden mb-5">
    <form action="{{ route('loans.tolak', $loan) }}" method="POST"
          class="bg-red-50 border border-red-200 rounded-xl p-4 flex gap-3">
        @csrf
        <input type="text" name="alasan_ditolak" required placeholder="Alasan penolakan..."
               class="flex-1 border border-red-200 rounded-lg px-3 py-2 text-sm
                      focus:outline-none focus:ring-2 focus:ring-red-400 bg-white">
        <button class="px-4 py-2 bg-red-600 text-white text-sm rounded-lg hover:bg-red-700">
            Konfirmasi Tolak
        </button>
    </form>
</div>
@endcan

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Info peminjaman --}}
    <div class="lg:col-span-2 space-y-5">
        <div class="bg-white border border-slate-200 rounded-xl p-5">

            {{-- Status badge --}}
            @php
            $statusConfig = [
                'requested' => ['bg-amber-100 text-amber-800', 'ti-clock', 'Menunggu Persetujuan'],
                'approved'  => ['bg-sky-100 text-sky-800',     'ti-check', 'Disetujui'],
                'borrowed'  => ['bg-blue-100 text-blue-800',   'ti-book',  'Sedang Dipinjam'],
                'returned'  => ['bg-emerald-100 text-emerald-800', 'ti-book-upload', 'Dikembalikan'],
                'rejected'  => ['bg-red-100 text-red-800',    'ti-x',     'Ditolak'],
                'overdue'   => ['bg-red-200 text-red-900',    'ti-alert-triangle', 'Terlambat'],
            ];
            [$badgeClass, $badgeIcon, $badgeLabel] = $statusConfig[$loan->status] ?? ['bg-gray-100 text-gray-700', 'ti-circle', ucfirst($loan->status)];
            @endphp

            <div class="flex items-center gap-3 mb-5">
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-sm font-semibold {{ $badgeClass }}">
                    <i class="ti {{ $badgeIcon }}"></i> {{ $badgeLabel }}
                </span>
                <span class="text-xs text-slate-400">{{ $loan->created_at->format('d M Y H:i') }}</span>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="text-xs text-slate-400 mb-0.5">Dokumen</p>
                    <a href="{{ route('documents.show', $loan->document) }}"
                       class="text-sm font-medium text-sky-600 hover:underline">
                        {{ $loan->document?->judul }}
                    </a>
                    <p class="text-xs text-slate-400 mt-0.5">{{ $loan->document?->doc_number }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-400 mb-0.5">Jenis</p>
                    <span class="inline-flex items-center gap-1 text-sm font-medium text-slate-700">
                        <i class="ti {{ $loan->jenis === 'fisik' ? 'ti-books' : 'ti-file-download' }} text-slate-400"></i>
                        {{ ucfirst($loan->jenis) }}
                    </span>
                </div>
                <div>
                    <p class="text-xs text-slate-400 mb-0.5">Peminjam</p>
                    <p class="text-sm font-medium text-slate-700">{{ $loan->borrower?->name }}</p>
                    <p class="text-xs text-slate-400">{{ $loan->borrower?->unitKerja?->singkatan }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-400 mb-0.5">Disetujui oleh</p>
                    <p class="text-sm text-slate-700">{{ $loan->approvedBy?->name ?? '—' }}</p>
                    @if($loan->approved_at)
                    <p class="text-xs text-slate-400">{{ $loan->approved_at->format('d M Y') }}</p>
                    @endif
                </div>
                <div>
                    <p class="text-xs text-slate-400 mb-0.5">Tgl Pinjam Rencana</p>
                    <p class="text-sm text-slate-700">{{ $loan->tgl_pinjam_rencana?->format('d M Y') ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-400 mb-0.5">Tgl Kembali Rencana</p>
                    <p class="text-sm font-medium {{ $loan->isTerlambat() ? 'text-red-600' : 'text-slate-700' }}">
                        {{ $loan->tgl_kembali_rencana?->format('d M Y') ?? '—' }}
                        @if($loan->isTerlambat())
                        <span class="text-xs">(terlambat)</span>
                        @endif
                    </p>
                </div>
                @if($loan->tgl_dikembalikan)
                <div class="col-span-2">
                    <p class="text-xs text-slate-400 mb-0.5">Tgl Dikembalikan</p>
                    <p class="text-sm text-emerald-600 font-medium">
                        <i class="ti ti-check"></i> {{ $loan->tgl_dikembalikan->format('d M Y H:i') }}
                    </p>
                </div>
                @endif
            </div>

            @if($loan->keperluan)
            <div class="mt-4 pt-4 border-t border-slate-100">
                <p class="text-xs text-slate-400 mb-1">Keperluan</p>
                <p class="text-sm text-slate-700">{{ $loan->keperluan }}</p>
            </div>
            @endif

            @if($loan->alasan_ditolak)
            <div class="mt-4 bg-red-50 border border-red-200 rounded-lg p-3">
                <p class="text-xs font-semibold text-red-700 mb-1">Alasan Penolakan</p>
                <p class="text-sm text-red-600">{{ $loan->alasan_ditolak }}</p>
            </div>
            @endif
        </div>
    </div>

    {{-- Sidebar lokasi fisik --}}
    <div class="space-y-5">
        @if($loan->jenis === 'fisik' && $loan->document?->physicalLocation)
        <div class="bg-white border border-slate-200 rounded-xl p-5">
            <h3 class="text-sm font-semibold text-slate-700 mb-3">Lokasi Fisik Dokumen</h3>
            @php $loc = $loan->document->physicalLocation; @endphp
            <div class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="text-slate-500">Kode</span>
                    <span class="font-mono font-semibold text-slate-700">{{ $loc->kode_lokasi }}</span>
                </div>
                @foreach(['Gedung'=>$loc->gedung,'Lantai'=>$loc->lantai,'Ruang'=>$loc->ruang,'Lemari'=>$loc->lemari,'Rak'=>$loc->rak] as $l=>$v)
                @if($v)
                <div class="flex justify-between">
                    <span class="text-slate-500">{{ $l }}</span>
                    <span class="text-slate-700">{{ $v }}</span>
                </div>
                @endif
                @endforeach
            </div>
        </div>
        @endif

        <div class="bg-white border border-slate-200 rounded-xl p-5">
            <h3 class="text-sm font-semibold text-slate-700 mb-3">Aksi</h3>
            <div class="space-y-2">
                @if($loan->borrower_id === auth()->id() && $loan->status === 'requested')
                <form action="{{ route('loans.destroy', $loan) }}" method="POST"
                      onsubmit="return confirm('Batalkan permohonan ini?')">
                    @csrf @method('DELETE')
                    <button class="w-full py-2 text-sm border border-red-200 text-red-600 rounded-lg hover:bg-red-50">
                        <i class="ti ti-x mr-1"></i> Batalkan Permohonan
                    </button>
                </form>
                @endif
                <a href="{{ route('loans.index') }}"
                   class="w-full py-2 text-sm border border-slate-200 text-slate-600 rounded-lg
                          hover:bg-slate-50 flex items-center justify-center gap-1.5">
                    <i class="ti ti-list"></i> Semua Peminjaman
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
