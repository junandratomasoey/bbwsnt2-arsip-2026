@extends('layouts.app')
@section('title', 'Jadwal OP')

@section('breadcrumb')
    <span class="text-slate-500 text-sm">OP</span>
    <i class="ti ti-chevron-right text-slate-300 text-xs"></i>
    <span class="text-slate-800 font-medium text-sm">Jadwal OP</span>
@endsection

@section('content')
<x-page-header title="Jadwal Operasi & Pemeliharaan" icon="ti-calendar-event"
    desc="Rencana OP tahunan per aset infrastruktur">
    @can('op_schedule.create')
    <a href="{{ route('op.schedules.create') }}"
       class="inline-flex items-center gap-2 px-4 py-2 bg-sky-600 text-white text-sm font-medium rounded-xl hover:bg-sky-700">
        <i class="ti ti-plus"></i> Buat Jadwal OP
    </a>
    @endcan
</x-page-header>

{{-- Filter --}}
<div class="bg-white border border-slate-200 rounded-xl p-4 mb-4">
    <form method="GET" class="flex flex-wrap gap-2">
        <select name="tahun" class="border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500">
            @foreach($tahunList as $t)
            <option value="{{ $t }}" @selected($tahun == $t)>Tahun {{ $t }}</option>
            @endforeach
        </select>
        <select name="status" class="border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500">
            <option value="">Semua status</option>
            @foreach(['draft'=>'Draft','approved'=>'Disetujui','berjalan'=>'Berjalan','selesai'=>'Selesai'] as $v=>$l)
            <option value="{{ $v }}" @selected(request('status') === $v)>{{ $l }}</option>
            @endforeach
        </select>
        <select name="unit_kerja_id" class="border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500">
            <option value="">Semua satker</option>
            @foreach($unitKerjas as $uk)
            <option value="{{ $uk->id }}" @selected(request('unit_kerja_id') === $uk->id)>{{ $uk->singkatan }}</option>
            @endforeach
        </select>
        <button type="submit" class="px-4 py-2 bg-slate-800 text-white text-sm rounded-lg hover:bg-slate-700">
            <i class="ti ti-search"></i>
        </button>
    </form>
</div>

<div class="bg-white border border-slate-200 rounded-xl overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 border-b border-slate-200 text-xs font-medium text-slate-500 uppercase tracking-wide">
            <tr>
                <th class="px-5 py-3 text-left">Aset</th>
                <th class="px-5 py-3 text-left hidden md:table-cell">Satker</th>
                <th class="px-5 py-3 text-center">Tahun</th>
                <th class="px-5 py-3 text-right hidden lg:table-cell">Anggaran Rutin</th>
                <th class="px-5 py-3 text-center">Status</th>
                <th class="px-5 py-3 text-center">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse($query as $schedule)
            <tr class="hover:bg-slate-50 transition-colors">
                <td class="px-5 py-3">
                    <a href="{{ route('assets.show', $schedule->asset) }}"
                       class="font-medium text-slate-800 hover:text-sky-600 block truncate max-w-xs">
                        {{ $schedule->asset?->nama }}
                    </a>
                    <p class="text-xs text-slate-400 font-mono mt-0.5">{{ $schedule->asset?->asset_code }}</p>
                </td>
                <td class="px-5 py-3 hidden md:table-cell text-xs text-slate-600">
                    {{ $schedule->unitKerja?->singkatan }}
                </td>
                <td class="px-5 py-3 text-center text-sm font-medium text-slate-700">
                    {{ $schedule->tahun }}
                </td>
                <td class="px-5 py-3 text-right hidden lg:table-cell text-sm text-slate-600">
                    @if($schedule->anggaran_op_rutin)
                    Rp {{ number_format($schedule->anggaran_op_rutin, 0, ',', '.') }}
                    @else
                    <span class="text-slate-400">—</span>
                    @endif
                </td>
                <td class="px-5 py-3 text-center">
                    @php
                    $badge = match($schedule->status) {
                        'approved' => 'bg-green-100 text-green-700',
                        'berjalan' => 'bg-blue-100 text-blue-700',
                        'selesai'  => 'bg-slate-100 text-slate-600',
                        default    => 'bg-amber-100 text-amber-700',
                    };
                    $label = match($schedule->status) {
                        'approved' => 'Disetujui', 'berjalan' => 'Berjalan',
                        'selesai'  => 'Selesai',   default    => 'Draft',
                    };
                    @endphp
                    <span class="inline-flex px-2 py-0.5 rounded-md text-xs font-medium {{ $badge }}">
                        {{ $label }}
                    </span>
                </td>
                <td class="px-5 py-3">
                    <div class="flex items-center justify-center gap-1">
                        <a href="{{ route('op.schedules.show', $schedule) }}"
                           class="p-1.5 text-slate-400 hover:text-sky-600 hover:bg-sky-50 rounded-lg">
                            <i class="ti ti-eye text-sm"></i>
                        </a>
                        @can('op_schedule.edit')
                        @if($schedule->status === 'draft')
                        <a href="{{ route('op.schedules.edit', $schedule) }}"
                           class="p-1.5 text-slate-400 hover:text-amber-600 hover:bg-amber-50 rounded-lg">
                            <i class="ti ti-edit text-sm"></i>
                        </a>
                        @endif
                        @endcan
                        @can('op_schedule.approve')
                        @if($schedule->status === 'draft')
                        <form action="{{ route('op.schedules.approve', $schedule) }}" method="POST">
                            @csrf
                            <button class="p-1.5 text-slate-400 hover:text-green-600 hover:bg-green-50 rounded-lg"
                                    title="Setujui" onclick="return confirm('Setujui jadwal OP ini?')">
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
                    <i class="ti ti-calendar-off text-4xl text-slate-200 block mb-3"></i>
                    <p class="text-slate-400">Belum ada jadwal OP untuk tahun {{ $tahun }}</p>
                    @can('op_schedule.create')
                    <a href="{{ route('op.schedules.create') }}"
                       class="mt-3 inline-flex items-center gap-1.5 text-sm text-sky-600 hover:underline">
                        <i class="ti ti-plus"></i> Buat jadwal OP pertama
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
