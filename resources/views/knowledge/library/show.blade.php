@extends('layouts.app')
@section('title', $item->judul)

@section('breadcrumb')
    <a href="{{ route('library.index') }}" class="text-slate-500 hover:text-slate-700 text-sm">Perpustakaan</a>
    <i class="ti ti-chevron-right text-slate-300 text-xs"></i>
    <span class="text-slate-800 font-medium text-sm truncate">{{ $item->judul }}</span>
@endsection

@section('content')
<x-page-header :title="$item->judul" icon="ti-book">
    @can('library.edit')
    <a href="{{ route('library.edit', $item) }}"
       class="inline-flex items-center gap-1.5 px-4 py-2 bg-white border border-slate-200
              text-sm text-slate-700 rounded-xl hover:bg-slate-50">
        <i class="ti ti-edit text-slate-400"></i> Edit
    </a>
    @endcan
</x-page-header>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Detail koleksi --}}
    <div class="lg:col-span-2 space-y-5">
        <div class="bg-white border border-slate-200 rounded-xl p-5">
            <div class="flex flex-wrap gap-2 mb-4">
                <span class="font-mono text-xs bg-slate-100 text-slate-600 px-2.5 py-1 rounded-lg">
                    {{ $item->kode_item }}
                </span>
                <span class="text-xs px-2.5 py-1 rounded-lg bg-amber-50 text-amber-700 border border-amber-100">
                    {{ ucfirst(str_replace('_', ' ', $item->tipe)) }}
                </span>
                @if($item->ada_digital)
                <span class="text-xs px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-700">
                    <i class="ti ti-file-check"></i> Ada Digital
                </span>
                @endif
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="text-xs text-slate-400 mb-0.5">Penulis</p>
                    <p class="text-sm text-slate-700">{{ $item->penulis ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-400 mb-0.5">Penerbit</p>
                    <p class="text-sm text-slate-700">{{ $item->penerbit ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-400 mb-0.5">Tahun Terbit</p>
                    <p class="text-sm text-slate-700">{{ $item->tahun_terbit ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-400 mb-0.5">ISBN</p>
                    <p class="text-sm font-mono text-slate-700">{{ $item->isbn ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-400 mb-0.5">Lokasi Rak</p>
                    <p class="text-sm text-slate-700">{{ $item->physicalLocation?->kode_lokasi ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-400 mb-0.5">Stok Tersedia</p>
                    <p class="text-sm font-semibold {{ $item->stokTersedia() > 0 ? 'text-emerald-600' : 'text-red-600' }}">
                        {{ $item->stokTersedia() }} dari {{ $item->stok_fisik }} eksemplar
                    </p>
                </div>
            </div>

            @if($item->tags && count($item->tags) > 0)
            <div class="mt-4 pt-4 border-t border-slate-100">
                <p class="text-xs text-slate-400 mb-2">Tags</p>
                <div class="flex flex-wrap gap-1.5">
                    @foreach($item->tags as $tag)
                    <span class="text-xs px-2 py-0.5 rounded-full bg-sky-50 text-sky-700 border border-sky-100">
                        # {{ $tag }}
                    </span>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        {{-- Riwayat peminjaman --}}
        @if($item->loans->isNotEmpty())
        <div class="bg-white border border-slate-200 rounded-xl overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100">
                <h3 class="text-sm font-semibold text-slate-700">Riwayat Peminjaman</h3>
            </div>
            <div class="divide-y divide-slate-100">
                @foreach($item->loans as $loan)
                <div class="px-5 py-3 flex items-center justify-between">
                    <div>
                        <p class="text-sm text-slate-700">{{ $loan->borrower?->name }}</p>
                        <p class="text-xs text-slate-400">
                            {{ $loan->tgl_pinjam_rencana?->format('d M Y') }} →
                            {{ $loan->tgl_kembali_rencana?->format('d M Y') }}
                        </p>
                    </div>
                    <span class="text-xs px-2 py-0.5 rounded
                                 {{ $loan->status === 'returned' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
                        {{ ucfirst($loan->status) }}
                    </span>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    {{-- Sidebar aksi --}}
    <div class="space-y-5">
        <div class="bg-white border border-slate-200 rounded-xl p-5">
            <h3 class="text-sm font-semibold text-slate-700 mb-4">Pinjam Koleksi</h3>

            @if($item->stokTersedia() > 0)
            <form action="{{ route('library.pinjam', $item) }}" method="POST" class="space-y-3">
                @csrf
                <div>
                    <label class="text-xs font-medium text-slate-600 block mb-1">Tgl Pinjam</label>
                    <input type="date" name="tgl_pinjam_rencana" required
                           value="{{ today()->format('Y-m-d') }}"
                           min="{{ today()->format('Y-m-d') }}"
                           class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm
                                  focus:outline-none focus:ring-2 focus:ring-sky-500">
                </div>
                <div>
                    <label class="text-xs font-medium text-slate-600 block mb-1">Tgl Kembali</label>
                    <input type="date" name="tgl_kembali_rencana" required
                           value="{{ today()->addDays(14)->format('Y-m-d') }}"
                           min="{{ today()->addDays(1)->format('Y-m-d') }}"
                           class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm
                                  focus:outline-none focus:ring-2 focus:ring-sky-500">
                </div>
                <div>
                    <label class="text-xs font-medium text-slate-600 block mb-1">Keperluan</label>
                    <textarea name="keperluan" rows="2" required
                              placeholder="Tujuan meminjam..."
                              class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm
                                     resize-none focus:outline-none focus:ring-2 focus:ring-sky-500"></textarea>
                </div>
                <button type="submit"
                        class="w-full py-2.5 bg-sky-600 text-white text-sm font-medium rounded-xl hover:bg-sky-700">
                    <i class="ti ti-book-download mr-1"></i> Ajukan Peminjaman
                </button>
            </form>
            @else
            <div class="text-center py-4">
                <i class="ti ti-book-off text-3xl text-slate-200 block mb-2"></i>
                <p class="text-sm text-slate-400">Stok sedang tidak tersedia</p>
            </div>
            @endif
        </div>

        @if($item->ada_digital)
        <div class="bg-white border border-slate-200 rounded-xl p-5">
            <h3 class="text-sm font-semibold text-slate-700 mb-3">File Digital</h3>
            <a href="#" class="w-full flex items-center justify-center gap-2 py-2.5
                               border border-slate-200 text-sm text-slate-600 rounded-xl hover:bg-slate-50">
                <i class="ti ti-download text-slate-400"></i> Download PDF
            </a>
        </div>
        @endif
    </div>
</div>
@endsection
