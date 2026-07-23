@extends('layouts.app')
@section('title', 'Jenis Dokumen')
@section('breadcrumb')
    <span class="text-slate-800 font-medium text-sm">Jenis Dokumen</span>
@endsection
@section('content')
<x-page-header title="Jenis Dokumen" desc="Kelola tipe dokumen beserta retensi dan nasib akhir" icon="ti-file-description">
    @can('document_type.create')
    <a href="{{ route('superadmin.document-types.create') }}"
       class="inline-flex items-center gap-2 px-4 py-2 text-white text-sm font-medium rounded-xl"
       style="background:#003366">
        <i class="ti ti-plus"></i> Tambah Jenis Dokumen
    </a>
    @endcan
</x-page-header>

{{-- Filter kategori --}}
<div class="mb-4 flex flex-wrap gap-2">
    @php
    $kategoriList = $types->pluck('kategori')->unique()->sort()->values();
    @endphp
    <a href="{{ route('superadmin.document-types.index') }}"
       class="text-xs px-3 py-1.5 rounded-lg border {{ !request('kategori') ? 'border-slate-800 bg-slate-800 text-white' : 'border-slate-200 text-slate-600 hover:bg-slate-50' }}">
        Semua
    </a>
    @foreach($kategoriList as $kat)
    <a href="{{ route('superadmin.document-types.index', ['kategori' => $kat]) }}"
       class="text-xs px-3 py-1.5 rounded-lg border {{ request('kategori') === $kat ? 'border-slate-800 bg-slate-800 text-white' : 'border-slate-200 text-slate-600 hover:bg-slate-50' }}">
        {{ ucfirst($kat) }}
    </a>
    @endforeach
</div>

<div class="bg-white border border-slate-200 rounded-xl overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 border-b border-slate-200 text-xs font-medium text-slate-500 uppercase">
            <tr>
                <th class="px-4 py-3 text-left">Kode</th>
                <th class="px-4 py-3 text-left">Nama</th>
                <th class="px-4 py-3 text-left hidden md:table-cell">Kategori</th>
                <th class="px-4 py-3 text-center hidden lg:table-cell">Retensi Aktif</th>
                <th class="px-4 py-3 text-center hidden lg:table-cell">Retensi Inaktif</th>
                <th class="px-4 py-3 text-center hidden md:table-cell">Nasib Akhir</th>
                <th class="px-4 py-3 text-center">Dokumen</th>
                <th class="px-4 py-3 text-center">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse($types->when(request('kategori'), fn($c) => $c->where('kategori', request('kategori'))) as $type)
            <tr class="hover:bg-slate-50">
                <td class="px-4 py-3">
                    <span class="font-mono font-bold text-xs px-2 py-0.5 rounded bg-slate-100">{{ $type->kode }}</span>
                </td>
                <td class="px-4 py-3 font-medium text-slate-800">{{ $type->nama }}</td>
                <td class="px-4 py-3 hidden md:table-cell text-xs text-slate-500">{{ $type->kategori }}</td>
                <td class="px-4 py-3 hidden lg:table-cell text-center text-xs text-slate-600">
                    {{ $type->retensi_aktif_tahun }} thn
                </td>
                <td class="px-4 py-3 hidden lg:table-cell text-center text-xs text-slate-600">
                    {{ $type->retensi_inaktif_tahun }} thn
                </td>
                <td class="px-4 py-3 hidden md:table-cell text-center">
                    @php $nb = match($type->nasib_akhir){ 'permanen'=>'bg-blue-100 text-blue-700','musnah'=>'bg-red-100 text-red-700',default=>'bg-amber-100 text-amber-700' }; @endphp
                    <span class="text-xs px-2 py-0.5 rounded {{ $nb }}">{{ ucfirst($type->nasib_akhir) }}</span>
                </td>
                <td class="px-4 py-3 text-center font-semibold {{ $type->documents_count ? 'text-slate-700' : 'text-slate-300' }}">
                    {{ $type->documents_count }}
                </td>
                <td class="px-4 py-3">
                    <div class="flex items-center justify-center gap-1">
                        <a href="{{ route('superadmin.document-types.edit', $type) }}"
                           class="p-1.5 text-slate-400 hover:text-amber-600 hover:bg-amber-50 rounded-lg">
                            <i class="ti ti-edit text-sm"></i>
                        </a>
                        @if($type->documents_count === 0)
                        <form action="{{ route('superadmin.document-types.destroy', $type) }}" method="POST"
                              onsubmit="return confirm('Hapus {{ $type->nama }}?')">
                            @csrf @method('DELETE')
                            <button class="p-1.5 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg">
                                <i class="ti ti-trash text-sm"></i>
                            </button>
                        </form>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="8" class="px-5 py-10 text-center text-slate-400">Belum ada jenis dokumen</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
