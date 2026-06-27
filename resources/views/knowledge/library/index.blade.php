@extends('layouts.app')
@section('title', 'Perpustakaan')
@section('breadcrumb')
    <span class="text-slate-800 font-medium text-sm">Perpustakaan</span>
@endsection
@section('content')
<x-page-header title="Perpustakaan Digital" desc="Koleksi buku, jurnal, standar, dan peraturan" icon="ti-books">
    @can('library.create')
    <a href="{{ route('library.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-sky-600 text-white text-sm font-medium rounded-xl hover:bg-sky-700">
        <i class="ti ti-plus"></i> Tambah Koleksi
    </a>
    @endcan
</x-page-header>
<div class="bg-white border border-slate-200 rounded-xl p-4 mb-4">
    <form method="GET" class="flex flex-wrap gap-2">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari judul, penulis..."
               class="flex-1 min-w-48 border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500">
        <select name="tipe" class="border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500">
            <option value="">Semua tipe</option>
            @foreach(['buku'=>'Buku','jurnal'=>'Jurnal','standar'=>'Standar','peraturan'=>'Peraturan','laporan'=>'Laporan','manual_teknis'=>'Manual Teknis'] as $v=>$l)
            <option value="{{ $v }}" @selected(request('tipe') === $v)>{{ $l }}</option>
            @endforeach
        </select>
        <button type="submit" class="px-4 py-2 bg-slate-800 text-white text-sm rounded-lg">Cari</button>
        @if(request()->hasAny(['search','tipe']))
        <a href="{{ route('library.index') }}" class="px-4 py-2 text-sm border border-slate-200 text-slate-500 rounded-lg">Reset</a>
        @endif
    </form>
</div>
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
    @forelse($query as $item)
    <div class="bg-white border border-slate-200 rounded-xl p-4 hover:border-slate-300 transition-colors">
        <div class="flex items-start gap-3">
            <div class="w-10 h-10 rounded-lg bg-amber-50 border border-amber-100 flex items-center justify-center flex-shrink-0">
                <i class="ti {{ $item->tipe === 'buku' ? 'ti-book' : ($item->tipe === 'jurnal' ? 'ti-news' : ($item->tipe === 'standar' ? 'ti-certificate' : 'ti-file-text')) }} text-amber-600"></i>
            </div>
            <div class="min-w-0 flex-1">
                <p class="text-xs font-mono text-slate-400 mb-0.5">{{ $item->kode_item }}</p>
                <a href="{{ route('library.show', $item) }}" class="text-sm font-semibold text-slate-800 hover:text-sky-600 line-clamp-2">
                    {{ $item->judul }}
                </a>
                <p class="text-xs text-slate-400 mt-1">{{ $item->penulis ?? '—' }} · {{ $item->tahun_terbit ?? '—' }}</p>
            </div>
        </div>
        <div class="mt-3 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="text-xs px-2 py-0.5 rounded bg-amber-50 text-amber-700 border border-amber-100">{{ ucfirst($item->tipe) }}</span>
                @if($item->stokTersedia() > 0)
                <span class="text-xs text-emerald-600">{{ $item->stokTersedia() }} tersedia</span>
                @else
                <span class="text-xs text-red-500">Habis</span>
                @endif
            </div>
            @can('library_loan.create')
            @if($item->stokTersedia() > 0)
            <a href="{{ route('library.show', $item) }}" class="text-xs text-sky-600 hover:underline">Pinjam →</a>
            @endif
            @endcan
        </div>
    </div>
    @empty
    <div class="col-span-3 bg-white border border-slate-200 rounded-xl py-12 text-center">
        <i class="ti ti-books text-4xl text-slate-200 block mb-3"></i>
        <p class="text-slate-400">Belum ada koleksi perpustakaan</p>
    </div>
    @endforelse
</div>
@if($query->hasPages())
<div class="mt-4">{{ $query->links() }}</div>
@endif
@endsection
