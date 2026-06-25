@extends('layouts.app')
@section('title', 'Unit Kerja')

@section('breadcrumb')
    <span class="text-slate-500">Administrasi</span>
    <i class="ti ti-chevron-right text-slate-300 text-xs"></i>
    <span class="text-slate-800 font-medium">Unit Kerja</span>
@endsection

@section('content')
<x-page-header title="Unit Kerja" desc="Struktur organisasi BBWS Nusa Tenggara II">
    @can('unit_kerja.create')
    <a href="{{ route('superadmin.unit-kerja.create') }}"
       class="inline-flex items-center gap-2 px-4 py-2 bg-sky-600 text-white text-sm font-medium rounded-xl hover:bg-sky-700 transition-colors">
        <i class="ti ti-plus"></i> Tambah Unit
    </a>
    @endcan
</x-page-header>

{{-- Filter / toggle --}}
<div class="bg-white border border-slate-200 rounded-xl p-4 mb-4 flex flex-wrap items-center gap-3">
    <form method="GET" class="flex flex-wrap gap-2 flex-1">
        <input type="text" name="search" value="{{ request('search') }}"
               placeholder="Cari nama, kode, singkatan..."
               class="flex-1 min-w-48 border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500">
        <select name="tipe" class="border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500">
            <option value="">Semua tipe</option>
            @foreach(['balai','bagian','bidang','satker','ppk'] as $t)
            <option value="{{ $t }}" @selected(request('tipe') === $t)>{{ ucfirst($t) }}</option>
            @endforeach
        </select>
        <button type="submit" class="px-4 py-2 bg-slate-800 text-white text-sm rounded-lg hover:bg-slate-700">
            <i class="ti ti-search"></i>
        </button>
        @if(request()->hasAny(['search','tipe']))
        <a href="{{ route('superadmin.unit-kerja.index') }}" class="px-4 py-2 text-sm text-slate-500 hover:text-slate-700 border border-slate-200 rounded-lg">
            Reset
        </a>
        @endif
    </form>
</div>

{{-- MODE POHON --}}
@if(($viewMode ?? 'tree') === 'tree')
<div class="space-y-3">
    @foreach($tree as $balai)
    <div class="bg-white border border-slate-200 rounded-xl overflow-hidden">
        {{-- Root: Balai --}}
        <div class="px-5 py-4 bg-purple-50 border-b border-purple-100 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-lg bg-purple-100 flex items-center justify-center">
                    <i class="ti ti-droplet-filled text-purple-600"></i>
                </div>
                <div>
                    <p class="font-semibold text-slate-800">{{ $balai->nama }}</p>
                    <p class="text-xs text-slate-500">{{ $balai->kode }} @if($balai->kepala) · {{ $balai->kepala }} @endif</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <x-badge-tipe tipe="balai" />
                @can('unit_kerja.edit')
                <a href="{{ route('superadmin.unit-kerja.edit', $balai) }}"
                   class="text-slate-400 hover:text-sky-600 p-1.5 rounded-lg hover:bg-sky-50">
                    <i class="ti ti-edit text-sm"></i>
                </a>
                @endcan
            </div>
        </div>

        {{-- Children: Bagian, Bidang --}}
        <div class="divide-y divide-slate-100">
            @foreach($balai->children->sortBy('urutan') as $child)
            <div x-data="{ open: true }">
                {{-- Bagian / Bidang --}}
                <div class="px-5 py-3 flex items-center justify-between hover:bg-slate-50 cursor-pointer"
                     @click="open = !open">
                    <div class="flex items-center gap-3">
                        <i :class="open ? 'ti-chevron-down' : 'ti-chevron-right'"
                           class="ti text-slate-400 text-xs w-4 flex-shrink-0"></i>
                        <div class="w-7 h-7 rounded-lg
                            @if($child->tipe === 'bagian') bg-blue-100 @else bg-teal-100 @endif
                            flex items-center justify-center">
                            <i class="ti @if($child->tipe === 'bagian') ti-briefcase text-blue-600 @else ti-wave-saw-tool text-teal-600 @endif text-sm"></i>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-slate-700">{{ $child->namaLengkap() }}</p>
                            <p class="text-xs text-slate-400">{{ $child->kode }}
                                @if($child->kepala) · {{ $child->kepala }} @endif
                                @if($child->users_count ?? $child->users->count()) · {{ $child->users->count() }} pengguna @endif
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <x-badge-tipe :tipe="$child->tipe" />
                        @can('unit_kerja.edit')
                        <a href="{{ route('superadmin.unit-kerja.edit', $child) }}"
                           class="text-slate-400 hover:text-sky-600 p-1.5 rounded-lg hover:bg-sky-50"
                           @click.stop>
                            <i class="ti ti-edit text-sm"></i>
                        </a>
                        @endcan
                    </div>
                </div>

                {{-- Satker di bawah bagian/bidang ini --}}
                <div x-show="open" x-cloak class="bg-slate-50/60 border-t border-slate-100">
                    @forelse($child->children->sortBy('urutan') as $satker)
                    <div x-data="{ openSatker: false }">
                        <div class="pl-14 pr-5 py-2.5 flex items-center justify-between hover:bg-slate-100 cursor-pointer"
                             @click="openSatker = !openSatker">
                            <div class="flex items-center gap-3">
                                <i :class="openSatker ? 'ti-chevron-down' : 'ti-chevron-right'"
                                   class="ti text-slate-400 text-xs w-4 flex-shrink-0"></i>
                                <div class="w-6 h-6 rounded-md bg-amber-100 flex items-center justify-center">
                                    <i class="ti ti-building text-amber-600 text-xs"></i>
                                </div>
                                <div>
                                    <p class="text-sm text-slate-700">{{ $satker->namaLengkap() }}</p>
                                    <p class="text-xs text-slate-400">{{ $satker->kode }}
                                        @if($satker->kepala) · {{ $satker->kepala }} @endif
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <x-badge-tipe tipe="satker" />
                                @can('unit_kerja.create')
                                <a href="{{ route('superadmin.unit-kerja.ppk.create', $satker) }}"
                                   title="Tambah PPK"
                                   class="text-slate-400 hover:text-emerald-600 p-1.5 rounded-lg hover:bg-emerald-50"
                                   @click.stop>
                                    <i class="ti ti-plus text-sm"></i>
                                </a>
                                @endcan
                                @can('unit_kerja.edit')
                                <a href="{{ route('superadmin.unit-kerja.edit', $satker) }}"
                                   class="text-slate-400 hover:text-sky-600 p-1.5 rounded-lg hover:bg-sky-50"
                                   @click.stop>
                                    <i class="ti ti-edit text-sm"></i>
                                </a>
                                @endcan
                            </div>
                        </div>

                        {{-- PPK di bawah satker --}}
                        <div x-show="openSatker" x-cloak class="border-t border-slate-200/60">
                            @forelse($satker->children->sortBy('urutan') as $ppk)
                            <div class="pl-28 pr-5 py-2 flex items-center justify-between hover:bg-white/80">
                                <div class="flex items-center gap-3">
                                    <div class="w-5 h-5 rounded bg-red-100 flex items-center justify-center">
                                        <i class="ti ti-user-check text-red-600 text-xs"></i>
                                    </div>
                                    <div>
                                        <p class="text-xs text-slate-700">{{ $ppk->namaLengkap() }}</p>
                                        <p class="text-xs text-slate-400">{{ $ppk->kode }}</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <x-badge-tipe tipe="ppk" />
                                    @can('unit_kerja.edit')
                                    <a href="{{ route('superadmin.unit-kerja.edit', $ppk) }}"
                                       class="text-slate-400 hover:text-sky-600 p-1 rounded-lg hover:bg-sky-50">
                                        <i class="ti ti-edit text-xs"></i>
                                    </a>
                                    @endcan
                                    @can('unit_kerja.delete')
                                    <form action="{{ route('superadmin.unit-kerja.destroy', $ppk) }}" method="POST"
                                          onsubmit="return confirm('Hapus {{ $ppk->nama }}?')">
                                        @csrf @method('DELETE')
                                        <button class="text-slate-300 hover:text-red-500 p-1 rounded-lg hover:bg-red-50">
                                            <i class="ti ti-trash text-xs"></i>
                                        </button>
                                    </form>
                                    @endcan
                                </div>
                            </div>
                            @empty
                            <p class="pl-28 pr-5 py-2 text-xs text-slate-400 italic">Belum ada PPK</p>
                            @endforelse
                        </div>
                    </div>
                    @empty
                    <p class="pl-14 pr-5 py-2 text-xs text-slate-400 italic">Belum ada satker</p>
                    @endforelse
                </div>
            </div>
            @endforeach

            {{-- Satker langsung di bawah balai (tanpa bidang/bagian) --}}
            @foreach($balai->children->where('tipe', 'satker')->sortBy('urutan') as $satker)
            <div x-data="{ open: false }">
                <div class="px-5 py-3 flex items-center justify-between hover:bg-slate-50 cursor-pointer"
                     @click="open = !open">
                    <div class="flex items-center gap-3">
                        <i :class="open ? 'ti-chevron-down' : 'ti-chevron-right'"
                           class="ti text-slate-400 text-xs w-4 flex-shrink-0"></i>
                        <div class="w-7 h-7 rounded-lg bg-amber-100 flex items-center justify-center">
                            <i class="ti ti-building text-amber-600 text-sm"></i>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-slate-700">{{ $satker->namaLengkap() }}</p>
                            <p class="text-xs text-slate-400">{{ $satker->kode }}
                                @if($satker->kepala) · {{ $satker->kepala }} @endif
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <x-badge-tipe tipe="satker" />
                        @can('unit_kerja.create')
                        <a href="{{ route('superadmin.unit-kerja.ppk.create', $satker) }}"
                           title="Tambah PPK"
                           class="text-slate-400 hover:text-emerald-600 p-1.5 rounded-lg hover:bg-emerald-50"
                           @click.stop>
                            <i class="ti ti-plus text-sm"></i>
                        </a>
                        @endcan
                        @can('unit_kerja.edit')
                        <a href="{{ route('superadmin.unit-kerja.edit', $satker) }}"
                           class="text-slate-400 hover:text-sky-600 p-1.5 rounded-lg hover:bg-sky-50"
                           @click.stop>
                            <i class="ti ti-edit text-sm"></i>
                        </a>
                        @endcan
                    </div>
                </div>
                <div x-show="open" x-cloak class="bg-slate-50 border-t border-slate-100">
                    @forelse($satker->children->sortBy('urutan') as $ppk)
                    <div class="pl-16 pr-5 py-2 flex items-center justify-between hover:bg-slate-100">
                        <div class="flex items-center gap-3">
                            <div class="w-5 h-5 rounded bg-red-100 flex items-center justify-center">
                                <i class="ti ti-user-check text-red-600 text-xs"></i>
                            </div>
                            <p class="text-xs text-slate-700">{{ $ppk->namaLengkap() }}</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <x-badge-tipe tipe="ppk" />
                            @can('unit_kerja.edit')
                            <a href="{{ route('superadmin.unit-kerja.edit', $ppk) }}"
                               class="text-slate-400 hover:text-sky-600 p-1 rounded-lg hover:bg-sky-50">
                                <i class="ti ti-edit text-xs"></i>
                            </a>
                            @endcan
                        </div>
                    </div>
                    @empty
                    <p class="pl-16 pr-5 py-2 text-xs text-slate-400 italic">Belum ada PPK</p>
                    @endforelse
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endforeach
</div>

{{-- MODE LIST --}}
@else
<div class="bg-white border border-slate-200 rounded-xl overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 text-slate-500 text-xs font-medium uppercase tracking-wide border-b border-slate-200">
            <tr>
                <th class="px-5 py-3 text-left">Nama</th>
                <th class="px-5 py-3 text-left">Tipe</th>
                <th class="px-5 py-3 text-left">Kode</th>
                <th class="px-5 py-3 text-left">Induk</th>
                <th class="px-5 py-3 text-left">Kepala</th>
                <th class="px-5 py-3 text-center">User</th>
                <th class="px-5 py-3 text-center">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse($rows as $uk)
            <tr class="hover:bg-slate-50 transition-colors">
                <td class="px-5 py-3 font-medium text-slate-800">{{ $uk->nama }}</td>
                <td class="px-5 py-3"><x-badge-tipe :tipe="$uk->tipe" /></td>
                <td class="px-5 py-3 text-slate-500 font-mono text-xs">{{ $uk->kode }}</td>
                <td class="px-5 py-3 text-slate-500">{{ $uk->parent?->singkatan ?? '-' }}</td>
                <td class="px-5 py-3 text-slate-500">{{ $uk->kepala ?? '-' }}</td>
                <td class="px-5 py-3 text-center text-slate-600">{{ $uk->users_count }}</td>
                <td class="px-5 py-3">
                    <div class="flex items-center justify-center gap-1">
                        <a href="{{ route('superadmin.unit-kerja.show', $uk) }}"
                           class="p-1.5 text-slate-400 hover:text-sky-600 hover:bg-sky-50 rounded-lg">
                            <i class="ti ti-eye text-sm"></i>
                        </a>
                        @can('unit_kerja.edit')
                        <a href="{{ route('superadmin.unit-kerja.edit', $uk) }}"
                           class="p-1.5 text-slate-400 hover:text-amber-600 hover:bg-amber-50 rounded-lg">
                            <i class="ti ti-edit text-sm"></i>
                        </a>
                        @endcan
                        @can('unit_kerja.delete')
                        <form action="{{ route('superadmin.unit-kerja.destroy', $uk) }}" method="POST"
                              onsubmit="return confirm('Hapus {{ $uk->nama }}?')">
                            @csrf @method('DELETE')
                            <button class="p-1.5 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg">
                                <i class="ti ti-trash text-sm"></i>
                            </button>
                        </form>
                        @endcan
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="7" class="px-5 py-10 text-center text-slate-400">Tidak ada data unit kerja.</td></tr>
            @endforelse
        </tbody>
    </table>
    @if(isset($rows) && $rows->hasPages())
    <div class="px-5 py-4 border-t border-slate-100">{{ $rows->links() }}</div>
    @endif
</div>
@endif
@endsection
