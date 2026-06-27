@extends('layouts.app')
@section('title', 'Peminjaman Dokumen')
@section('breadcrumb')
    <span class="text-slate-800 font-medium text-sm">Peminjaman Dokumen</span>
@endsection
@section('content')
<x-page-header title="Peminjaman Dokumen" icon="ti-book-download">
    @can('loan.create')
    <a href="{{ route('loans.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-sky-600 text-white text-sm font-medium rounded-xl hover:bg-sky-700">
        <i class="ti ti-plus"></i> Ajukan Peminjaman
    </a>
    @endcan
</x-page-header>
<div class="grid grid-cols-3 gap-3 mb-5">
    <x-stat-card label="Menunggu"  value="{{ $stats['menunggu'] }}"  icon="ti-clock"       color="amber" />
    <x-stat-card label="Dipinjam"  value="{{ $stats['dipinjam'] }}"  icon="ti-book"        color="sky" />
    <x-stat-card label="Terlambat" value="{{ $stats['terlambat'] }}" icon="ti-alert-circle" color="red" />
</div>
<div class="bg-white border border-slate-200 rounded-xl p-4 mb-4">
    <form method="GET" class="flex flex-wrap gap-2">
        <select name="status" class="border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500">
            <option value="">Semua status</option>
            @foreach(['requested'=>'Menunggu','approved'=>'Disetujui','borrowed'=>'Dipinjam','returned'=>'Dikembalikan','rejected'=>'Ditolak'] as $v=>$l)
            <option value="{{ $v }}" @selected(request('status') === $v)>{{ $l }}</option>
            @endforeach
        </select>
        <select name="jenis" class="border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500">
            <option value="">Semua jenis</option>
            <option value="fisik" @selected(request('jenis') === 'fisik')>Fisik</option>
            <option value="digital" @selected(request('jenis') === 'digital')>Digital</option>
        </select>
        <button type="submit" class="px-4 py-2 bg-slate-800 text-white text-sm rounded-lg">Filter</button>
    </form>
</div>
<div class="bg-white border border-slate-200 rounded-xl overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 border-b border-slate-200 text-xs font-medium text-slate-500 uppercase tracking-wide">
            <tr>
                <th class="px-5 py-3 text-left">Dokumen</th>
                <th class="px-5 py-3 text-left hidden md:table-cell">Peminjam</th>
                <th class="px-5 py-3 text-center">Jenis</th>
                <th class="px-5 py-3 text-center">Kembali</th>
                <th class="px-5 py-3 text-center">Status</th>
                <th class="px-5 py-3 text-center">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse($query as $loan)
            <tr class="hover:bg-slate-50 {{ $loan->isTerlambat() ? 'bg-red-50/30' : '' }}">
                <td class="px-5 py-3">
                    <a href="{{ route('documents.show', $loan->document) }}" class="font-medium text-slate-800 hover:text-sky-600 block truncate max-w-xs">
                        {{ $loan->document?->judul }}
                    </a>
                    <p class="text-xs text-slate-400 mt-0.5">{{ $loan->created_at->format('d M Y') }}</p>
                </td>
                <td class="px-5 py-3 hidden md:table-cell text-xs text-slate-600">{{ $loan->borrower?->name }}</td>
                <td class="px-5 py-3 text-center">
                    <span class="text-xs px-2 py-0.5 rounded bg-slate-100 text-slate-600">{{ ucfirst($loan->jenis) }}</span>
                </td>
                <td class="px-5 py-3 text-center text-xs {{ $loan->isTerlambat() ? 'text-red-600 font-semibold' : 'text-slate-600' }}">
                    {{ $loan->tgl_kembali_rencana?->format('d M Y') }}
                </td>
                <td class="px-5 py-3 text-center">
                    <span class="inline-flex px-2 py-0.5 rounded text-xs {{ $loan->badgeStatus() }}">{{ $loan->labelStatus() }}</span>
                </td>
                <td class="px-5 py-3">
                    <div class="flex items-center justify-center gap-1">
                        <a href="{{ route('loans.show', $loan) }}" class="p-1.5 text-slate-400 hover:text-sky-600 hover:bg-sky-50 rounded-lg">
                            <i class="ti ti-eye text-sm"></i>
                        </a>
                        @can('loan.approve')
                        @if($loan->status === 'requested')
                        <form action="{{ route('loans.approve', $loan) }}" method="POST">
                            @csrf
                            <button class="p-1.5 text-slate-400 hover:text-emerald-600 hover:bg-emerald-50 rounded-lg" title="Setujui">
                                <i class="ti ti-check text-sm"></i>
                            </button>
                        </form>
                        @endif
                        @if($loan->status === 'borrowed')
                        <form action="{{ route('loans.kembalikan', $loan) }}" method="POST">
                            @csrf
                            <button class="p-1.5 text-slate-400 hover:text-sky-600 hover:bg-sky-50 rounded-lg" title="Kembalikan">
                                <i class="ti ti-book-upload text-sm"></i>
                            </button>
                        </form>
                        @endif
                        @endcan
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="6" class="px-5 py-10 text-center text-slate-400">Tidak ada data peminjaman</td></tr>
            @endforelse
        </tbody>
    </table>
    @if($query->hasPages())
    <div class="px-5 py-4 border-t border-slate-100">{{ $query->links() }}</div>
    @endif
</div>
@endsection
