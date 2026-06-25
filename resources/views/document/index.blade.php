@extends('layouts.app')
@section('title', 'Dokumen & Arsip')

@section('breadcrumb')
    <i class="ti ti-files text-slate-400"></i>
    <span class="text-slate-800 font-medium text-sm">Dokumen & Arsip</span>
@endsection

@section('content')
<x-page-header title="Dokumen & Arsip" desc="Manajemen dokumen infrastruktur BBWS NT II" icon="ti-files">
    @can('document.create')
    <a href="{{ route('documents.create') }}"
       class="inline-flex items-center gap-2 px-4 py-2 bg-sky-600 text-white text-sm font-medium rounded-xl hover:bg-sky-700">
        <i class="ti ti-upload"></i> Upload Dokumen
    </a>
    @endcan
</x-page-header>

{{-- Stats --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-5">
    <x-stat-card label="Total"        value="{{ $stats['total'] }}"       icon="ti-files"         color="sky" />
    <x-stat-card label="Approved"     value="{{ $stats['approved'] }}"    icon="ti-circle-check"  color="green" />
    <x-stat-card label="Draft/Review" value="{{ $stats['draft'] }}"       icon="ti-edit"          color="amber" />
    <x-stat-card label="Kadaluwarsa"  value="{{ $stats['kadaluwarsa'] }}" icon="ti-alert-triangle" color="red" />
</div>

{{-- Filter --}}
<div class="bg-white border border-slate-200 rounded-xl p-4 mb-4">
    <form method="GET" class="flex flex-wrap gap-2">
        <input type="text" name="search" value="{{ request('search') }}"
               placeholder="Cari judul, nomor dokumen..."
               class="flex-1 min-w-48 border border-slate-200 rounded-lg px-3 py-2 text-sm
                      focus:outline-none focus:ring-2 focus:ring-sky-500">
        <select name="document_type_id" class="border border-slate-200 rounded-lg px-3 py-2 text-sm
                                               focus:outline-none focus:ring-2 focus:ring-sky-500">
            <option value="">Semua jenis</option>
            @foreach($docTypes as $dt)
            <option value="{{ $dt->id }}" @selected(request('document_type_id') === $dt->id)>{{ $dt->nama }}</option>
            @endforeach
        </select>
        <select name="status" class="border border-slate-200 rounded-lg px-3 py-2 text-sm
                                     focus:outline-none focus:ring-2 focus:ring-sky-500">
            <option value="">Semua status</option>
            @foreach(['draft'=>'Draft','review'=>'Review','approved'=>'Approved','archived'=>'Archived'] as $v=>$l)
            <option value="{{ $v }}" @selected(request('status') === $v)>{{ $l }}</option>
            @endforeach
        </select>
        <select name="fase" class="border border-slate-200 rounded-lg px-3 py-2 text-sm
                                   focus:outline-none focus:ring-2 focus:ring-sky-500">
            <option value="">Semua fase</option>
            @foreach(['before'=>'Before','during'=>'During','after'=>'After','op'=>'OP','umum'=>'Umum'] as $v=>$l)
            <option value="{{ $v }}" @selected(request('fase') === $v)>{{ $l }}</option>
            @endforeach
        </select>
        <a href="{{ route('documents.index', ['filter'=>'kadaluwarsa']) }}"
           class="px-3 py-2 text-xs border border-red-200 text-red-600 rounded-lg hover:bg-red-50 flex items-center gap-1">
            <i class="ti ti-alert-circle"></i> Kadaluwarsa
        </a>
        <button type="submit" class="px-4 py-2 bg-slate-800 text-white text-sm rounded-lg hover:bg-slate-700">
            <i class="ti ti-search"></i>
        </button>
        @if(request()->hasAny(['search','document_type_id','status','fase','filter']))
        <a href="{{ route('documents.index') }}" class="px-4 py-2 text-sm text-slate-500 border border-slate-200 rounded-lg hover:bg-slate-50">Reset</a>
        @endif
    </form>
</div>

{{-- Tabel --}}
<div class="bg-white border border-slate-200 rounded-xl overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 border-b border-slate-200 text-xs font-medium text-slate-500 uppercase tracking-wide">
            <tr>
                <th class="px-5 py-3 text-left">Dokumen</th>
                <th class="px-5 py-3 text-left hidden md:table-cell">Jenis</th>
                <th class="px-5 py-3 text-left hidden lg:table-cell">Fase</th>
                <th class="px-5 py-3 text-center">Klasifikasi</th>
                <th class="px-5 py-3 text-center">Status</th>
                <th class="px-5 py-3 text-center">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse($query as $doc)
            <tr class="hover:bg-slate-50 transition-colors {{ $doc->isKadaluwarsa() ? 'bg-red-50/30' : '' }}">
                <td class="px-5 py-3">
                    <a href="{{ route('documents.show', $doc) }}"
                       class="font-medium text-slate-800 hover:text-sky-600 block">{{ $doc->judul }}</a>
                    <p class="text-xs text-slate-400 mt-0.5 font-mono">
                        {{ $doc->doc_number ?? '—' }} · {{ $doc->versiLabel() }}
                    </p>
                    @if($doc->isKadaluwarsa())
                    <span class="text-xs text-red-500">
                        <i class="ti ti-alert-circle"></i>
                        Kadaluwarsa {{ $doc->tgl_kedaluwarsa->format('d M Y') }}
                    </span>
                    @endif
                </td>
                <td class="px-5 py-3 hidden md:table-cell text-xs text-slate-600">
                    {{ $doc->documentType?->nama ?? '—' }}
                </td>
                <td class="px-5 py-3 hidden lg:table-cell">
                    <span class="text-xs px-2 py-0.5 rounded bg-slate-100 text-slate-600">
                        {{ ucfirst($doc->entity_fase ?? 'umum') }}
                    </span>
                </td>
                <td class="px-5 py-3 text-center">
                    <span class="inline-flex px-2 py-0.5 rounded text-xs {{ $doc->badgeKlasifikasi() }}">
                        {{ ucfirst($doc->klasifikasi) }}
                    </span>
                </td>
                <td class="px-5 py-3 text-center">
                    <span class="inline-flex px-2 py-0.5 rounded text-xs {{ $doc->badgeStatus() }}">
                        {{ ucfirst($doc->status) }}
                    </span>
                </td>
                <td class="px-5 py-3">
                    <div class="flex items-center justify-center gap-1">
                        <a href="{{ route('documents.show', $doc) }}"
                           class="p-1.5 text-slate-400 hover:text-sky-600 hover:bg-sky-50 rounded-lg" title="Detail">
                            <i class="ti ti-eye text-sm"></i>
                        </a>
                        @can('document.download')
                        @if($doc->ada_digital)
                        <a href="{{ route('documents.download', $doc) }}"
                           class="p-1.5 text-slate-400 hover:text-emerald-600 hover:bg-emerald-50 rounded-lg" title="Download">
                            <i class="ti ti-download text-sm"></i>
                        </a>
                        @endif
                        @endcan
                        @can('document.edit')
                        <a href="{{ route('documents.edit', $doc) }}"
                           class="p-1.5 text-slate-400 hover:text-amber-600 hover:bg-amber-50 rounded-lg" title="Edit">
                            <i class="ti ti-edit text-sm"></i>
                        </a>
                        @endcan
                        @can('document.approve')
                        @if($doc->status === 'review')
                        <form action="{{ route('documents.approve', $doc) }}" method="POST">
                            @csrf
                            <button class="p-1.5 text-slate-400 hover:text-green-600 hover:bg-green-50 rounded-lg" title="Approve">
                                <i class="ti ti-check text-sm"></i>
                            </button>
                        </form>
                        @endif
                        @endcan
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="px-5 py-12 text-center">
                    <i class="ti ti-files text-4xl text-slate-200 block mb-3"></i>
                    <p class="text-slate-400">Tidak ada dokumen ditemukan</p>
                    @can('document.create')
                    <a href="{{ route('documents.create') }}" class="mt-3 inline-flex items-center gap-1.5 text-sm text-sky-600 hover:underline">
                        <i class="ti ti-upload"></i> Upload dokumen pertama
                    </a>
                    @endcan
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
    @if($query->hasPages())
    <div class="px-5 py-4 border-t border-slate-100">{{ $query->links() }}</div>
    @endif
</div>
@endsection
