@extends('layouts.app')
@section('title', 'Peminjaman Buku')

@section('breadcrumb')
    <a href="{{ route('library.index') }}" class="text-slate-500 hover:text-slate-700 text-sm">Perpustakaan</a>
    <i class="ti ti-chevron-right text-slate-300 text-xs"></i>
    <span class="text-slate-800 font-medium text-sm">Peminjaman Buku</span>
@endsection

@section('content')
<x-page-header title="Peminjaman Koleksi Perpustakaan" icon="ti-book-download" />

<div class="bg-white border border-slate-200 rounded-xl overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 border-b border-slate-200 text-xs font-medium text-slate-500 uppercase tracking-wide">
            <tr>
                <th class="px-5 py-3 text-left">Koleksi</th>
                <th class="px-5 py-3 text-left hidden md:table-cell">Peminjam</th>
                <th class="px-5 py-3 text-center">Tgl Kembali</th>
                <th class="px-5 py-3 text-center">Status</th>
                <th class="px-5 py-3 text-center">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse($loans as $loan)
            <tr class="hover:bg-slate-50">
                <td class="px-5 py-3">
                    <p class="font-medium text-slate-800">{{ $loan->libraryItem?->judul }}</p>
                    <p class="text-xs text-slate-400 font-mono">{{ $loan->libraryItem?->kode_item }}</p>
                </td>
                <td class="px-5 py-3 hidden md:table-cell text-sm text-slate-600">
                    {{ $loan->borrower?->name }}
                </td>
                <td class="px-5 py-3 text-center text-xs {{ $loan->status === 'borrowed' && $loan->tgl_kembali_rencana?->isPast() ? 'text-red-600 font-semibold' : 'text-slate-600' }}">
                    {{ $loan->tgl_kembali_rencana?->format('d M Y') }}
                </td>
                <td class="px-5 py-3 text-center">
                    @php $badge = match($loan->status){ 'returned'=>'bg-emerald-100 text-emerald-700','borrowed'=>'bg-blue-100 text-blue-700','requested'=>'bg-amber-100 text-amber-700',default=>'bg-slate-100 text-slate-600' }; @endphp
                    <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium {{ $badge }}">
                        {{ ucfirst($loan->status) }}
                    </span>
                </td>
                <td class="px-5 py-3 text-center">
                    @can('library_loan.approve')
                    @if($loan->status === 'borrowed')
                    <form action="{{ route('library.kembalikan', $loan) }}" method="POST">
                        @csrf
                        <button class="text-xs px-3 py-1 bg-emerald-50 text-emerald-700 rounded-lg hover:bg-emerald-100">
                            Kembalikan
                        </button>
                    </form>
                    @endif
                    @endcan
                </td>
            </tr>
            @empty
            <tr><td colspan="5" class="px-5 py-10 text-center text-slate-400">Belum ada data peminjaman</td></tr>
            @endforelse
        </tbody>
    </table>
    @if($loans->hasPages())
    <div class="px-5 py-4 border-t border-slate-100">{{ $loans->links() }}</div>
    @endif
</div>
@endsection
