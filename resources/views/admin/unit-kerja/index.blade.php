@extends('layouts.app')
@section('title', 'Unit Kerja')
@section('breadcrumb')
    <span class="text-slate-800 font-medium text-sm">Unit Kerja</span>
@endsection
@section('content')
<x-page-header title="Struktur Unit Kerja" desc="Balai, Bagian, Bidang, Satker, dan PPK" icon="ti-sitemap">
    @can('unit_kerja.create')
    <div class="flex gap-2">
        <a href="{{ route('superadmin.unit-kerja.create', ['tipe'=>'bidang']) }}"
           class="inline-flex items-center gap-1.5 px-3 py-2 text-sm font-medium text-white rounded-xl"
           style="background:#003366">
            <i class="ti ti-plus"></i> Tambah Unit
        </a>
    </div>
    @endcan
</x-page-header>

{{-- Toggle mode --}}
<div class="flex gap-2 mb-4">
    <a href="{{ route('superadmin.unit-kerja.index') }}"
       class="text-xs px-3 py-1.5 rounded-lg border {{ !request()->hasAny(['tipe','search']) ? 'bg-slate-800 border-slate-800 text-white' : 'border-slate-200 text-slate-600 hover:bg-slate-50' }}">
        <i class="ti ti-hierarchy"></i> Pohon
    </a>
    <a href="{{ route('superadmin.unit-kerja.index', ['tipe'=>'satker']) }}"
       class="text-xs px-3 py-1.5 rounded-lg border {{ request('tipe')==='satker' ? 'bg-slate-800 border-slate-800 text-white' : 'border-slate-200 text-slate-600 hover:bg-slate-50' }}">
        Satker
    </a>
    <a href="{{ route('superadmin.unit-kerja.index', ['tipe'=>'ppk']) }}"
       class="text-xs px-3 py-1.5 rounded-lg border {{ request('tipe')==='ppk' ? 'bg-slate-800 border-slate-800 text-white' : 'border-slate-200 text-slate-600 hover:bg-slate-50' }}">
        PPK
    </a>
    <form method="GET" class="flex-1 flex gap-2">
        <input type="text" name="search" value="{{ request('search') }}"
               placeholder="Cari nama atau kode unit..."
               class="flex-1 border border-slate-200 rounded-lg px-3 py-1.5 text-sm
                      focus:outline-none focus:ring-2 focus:ring-sky-500">
        <button class="px-3 py-1.5 bg-slate-100 text-slate-600 text-sm rounded-lg hover:bg-slate-200">
            <i class="ti ti-search"></i>
        </button>
    </form>
</div>

@if(isset($tree))
{{-- Mode pohon --}}
<div class="space-y-3">
    @foreach($tree as $balai)
    <div class="bg-white border border-slate-200 rounded-xl overflow-hidden">
        {{-- Balai/Root --}}
        <div class="px-5 py-3 flex items-center gap-3" style="background: rgba(0,51,102,0.05)">
            <i class="ti ti-building text-slate-400"></i>
            <div class="flex-1">
                <span class="font-bold text-slate-800">{{ $balai->nama }}</span>
                <span class="ml-2 text-xs text-slate-400 font-mono">{{ $balai->kode }}</span>
                <span class="ml-2 text-xs px-1.5 py-0.5 rounded" style="background:rgba(0,51,102,0.1);color:#003366">
                    {{ ucfirst($balai->tipe) }}
                </span>
            </div>
            <div class="flex gap-1">
                <a href="{{ route('superadmin.unit-kerja.create', ['tipe'=>'bidang','parent_id'=>$balai->id]) }}"
                   class="text-xs px-2 py-1 rounded text-sky-600 hover:bg-sky-50 border border-sky-200">
                    + Bidang/Bagian
                </a>
                <a href="{{ route('superadmin.unit-kerja.edit', $balai) }}"
                   class="p-1 text-slate-400 hover:text-amber-600 rounded">
                    <i class="ti ti-edit text-sm"></i>
                </a>
            </div>
        </div>

        @if($balai->allChildren->isNotEmpty())
        <div class="divide-y divide-slate-100">
            @foreach($balai->allChildren->whereNull('parent_id')->merge($balai->children) as $bidang)
            {{-- Bidang/Bagian --}}
            <div class="px-5 py-2.5 flex items-center gap-3 pl-8 bg-slate-50/50">
                <i class="ti ti-folder text-slate-300 text-sm"></i>
                <div class="flex-1 text-sm">
                    <span class="font-medium text-slate-700">{{ $bidang->nama }}</span>
                    <span class="ml-2 text-xs text-slate-400 font-mono">{{ $bidang->kode }}</span>
                    <span class="ml-2 text-xs text-slate-400">{{ ucfirst($bidang->tipe) }}</span>
                </div>
                <div class="flex gap-1 items-center">
                    <a href="{{ route('superadmin.unit-kerja.create', ['tipe'=>'satker','parent_id'=>$bidang->id]) }}"
                       class="text-xs px-2 py-0.5 rounded text-emerald-600 hover:bg-emerald-50 border border-emerald-200">
                        + Satker
                    </a>
                    <a href="{{ route('superadmin.unit-kerja.edit', $bidang) }}"
                       class="p-1 text-slate-400 hover:text-amber-600 rounded">
                        <i class="ti ti-edit text-xs"></i>
                    </a>
                </div>
            </div>

            @foreach($bidang->children ?? [] as $satker)
            {{-- Satker --}}
            <div class="px-5 py-2.5 flex items-center gap-3 pl-14">
                <i class="ti ti-building-estate text-slate-300 text-sm"></i>
                <div class="flex-1 text-sm">
                    <span class="font-medium text-slate-700">{{ $satker->nama }}</span>
                    <span class="ml-2 text-xs font-mono text-slate-400">{{ $satker->kode }}</span>
                    <span class="ml-2 text-xs px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700">Satker</span>
                    @if($satker->children->count())
                    <span class="ml-1 text-xs text-slate-400">— {{ $satker->children->count() }} PPK</span>
                    @endif
                </div>
                <div class="flex gap-1 items-center">
                    <a href="{{ route('superadmin.unit-kerja.ppk.create', $satker) }}"
                       class="text-xs px-2 py-0.5 rounded text-amber-600 hover:bg-amber-50 border border-amber-200">
                        + PPK
                    </a>
                    <a href="{{ route('superadmin.unit-kerja.show', $satker) }}"
                       class="p-1 text-slate-400 hover:text-sky-600 rounded">
                        <i class="ti ti-eye text-xs"></i>
                    </a>
                    <a href="{{ route('superadmin.unit-kerja.edit', $satker) }}"
                       class="p-1 text-slate-400 hover:text-amber-600 rounded">
                        <i class="ti ti-edit text-xs"></i>
                    </a>
                </div>
            </div>

            @foreach($satker->children ?? [] as $ppk)
            {{-- PPK --}}
            <div class="px-5 py-2 flex items-center gap-3 pl-20 bg-amber-50/30">
                <i class="ti ti-user-star text-amber-300 text-sm"></i>
                <div class="flex-1 text-sm">
                    <span class="text-slate-700">{{ $ppk->nama }}</span>
                    <span class="ml-2 text-xs font-mono text-slate-400">{{ $ppk->kode }}</span>
                    <span class="ml-2 text-xs px-1.5 py-0.5 rounded" style="background:#FEF3C7;color:#92400E">PPK</span>
                </div>
                <div class="flex gap-1 items-center">
                    <a href="{{ route('superadmin.unit-kerja.show', $ppk) }}"
                       class="p-1 text-slate-400 hover:text-sky-600 rounded">
                        <i class="ti ti-eye text-xs"></i>
                    </a>
                    <a href="{{ route('superadmin.unit-kerja.edit', $ppk) }}"
                       class="p-1 text-slate-400 hover:text-amber-600 rounded">
                        <i class="ti ti-edit text-xs"></i>
                    </a>
                    <form action="{{ route('superadmin.unit-kerja.destroy', $ppk) }}" method="POST"
                          onsubmit="return confirm('Hapus PPK {{ $ppk->nama }}?')">
                        @csrf @method('DELETE')
                        <button class="p-1 text-slate-400 hover:text-red-600 rounded">
                            <i class="ti ti-trash text-xs"></i>
                        </button>
                    </form>
                </div>
            </div>
            @endforeach
            @endforeach
            @endforeach
        </div>
        @endif
    </div>
    @endforeach
</div>

@else
{{-- Mode list --}}
<div class="bg-white border border-slate-200 rounded-xl overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 border-b border-slate-200 text-xs font-medium text-slate-500 uppercase">
            <tr>
                <th class="px-4 py-3 text-left">Nama</th>
                <th class="px-4 py-3 text-left">Kode</th>
                <th class="px-4 py-3 text-left">Tipe</th>
                <th class="px-4 py-3 text-left hidden md:table-cell">Induk</th>
                <th class="px-4 py-3 text-center hidden md:table-cell">User</th>
                <th class="px-4 py-3 text-center">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse($rows as $uk)
            <tr class="hover:bg-slate-50">
                <td class="px-4 py-3 font-medium text-slate-800">{{ $uk->nama }}</td>
                <td class="px-4 py-3 font-mono text-xs text-slate-500">{{ $uk->kode }}</td>
                <td class="px-4 py-3">
                    @php $tColors=['balai'=>'blue','bagian'=>'purple','bidang'=>'indigo','satker'=>'emerald','ppk'=>'amber']; $tc=$tColors[$uk->tipe]??'slate'; @endphp
                    <span class="text-xs px-2 py-0.5 rounded bg-{{ $tc }}-100 text-{{ $tc }}-700">
                        {{ ucfirst($uk->tipe) }}
                    </span>
                </td>
                <td class="px-4 py-3 hidden md:table-cell text-xs text-slate-500">
                    {{ $uk->parent?->nama ?? '—' }}
                </td>
                <td class="px-4 py-3 hidden md:table-cell text-center text-xs text-slate-600">
                    {{ $uk->users_count }}
                </td>
                <td class="px-4 py-3">
                    <div class="flex items-center justify-center gap-1">
                        <a href="{{ route('superadmin.unit-kerja.show', $uk) }}"
                           class="p-1.5 text-slate-400 hover:text-sky-600 hover:bg-sky-50 rounded-lg">
                            <i class="ti ti-eye text-sm"></i>
                        </a>
                        <a href="{{ route('superadmin.unit-kerja.edit', $uk) }}"
                           class="p-1.5 text-slate-400 hover:text-amber-600 hover:bg-amber-50 rounded-lg">
                            <i class="ti ti-edit text-sm"></i>
                        </a>
                        @if($uk->tipe === 'satker')
                        <a href="{{ route('superadmin.unit-kerja.ppk.create', $uk) }}"
                           class="p-1.5 text-slate-400 hover:text-emerald-600 hover:bg-emerald-50 rounded-lg" title="Tambah PPK">
                            <i class="ti ti-plus text-sm"></i>
                        </a>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="6" class="px-5 py-10 text-center text-slate-400">Tidak ditemukan</td></tr>
            @endforelse
        </tbody>
    </table>
    @if(isset($rows) && $rows->hasPages())
    <div class="px-5 py-4 border-t border-slate-100">{{ $rows->links() }}</div>
    @endif
</div>
@endif
@endsection
