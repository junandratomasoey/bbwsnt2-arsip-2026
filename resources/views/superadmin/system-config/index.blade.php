{{-- resources/views/superadmin/system-config/index.blade.php --}}
@extends('layouts.app')
@section('title', 'Konfigurasi Sistem')

@section('breadcrumb')
    <span class="text-slate-500 text-sm">Superadmin</span>
    <i class="ti ti-chevron-right text-slate-300 text-xs"></i>
    <span class="text-slate-800 font-medium text-sm">Konfigurasi Sistem</span>
@endsection

@section('content')
<x-page-header title="Konfigurasi Sistem" desc="Pengaturan global aplikasi WIAKMS" icon="ti-settings" />

<div class="space-y-5">
    @foreach($configs as $group => $items)
    <div class="bg-white border border-slate-200 rounded-xl overflow-hidden">
        <div class="px-5 py-3.5 bg-slate-50 border-b border-slate-100">
            <h3 class="text-sm font-semibold text-slate-700 capitalize flex items-center gap-2">
                @php
                $groupIcon = match($group) {
                    'auth'         => 'ti-lock',
                    'storage'      => 'ti-database',
                    'loan'         => 'ti-book-download',
                    'notification' => 'ti-bell',
                    'gis'          => 'ti-map-2',
                    'org'          => 'ti-building',
                    default        => 'ti-settings',
                };
                @endphp
                <i class="ti {{ $groupIcon }} text-slate-400"></i>
                {{ ucfirst($group) }}
            </h3>
        </div>
        <div class="divide-y divide-slate-100">
            @foreach($items as $config)
            <div class="px-5 py-4 flex items-center justify-between gap-4">
                <div class="flex-1">
                    <p class="text-sm font-medium text-slate-700">{{ $config->label }}</p>
                    @if($config->deskripsi)
                    <p class="text-xs text-slate-400 mt-0.5">{{ $config->deskripsi }}</p>
                    @endif
                    <p class="text-xs font-mono text-slate-400 mt-0.5">{{ $config->group }}.{{ $config->key }}</p>
                </div>
                <div class="flex items-center gap-3 flex-shrink-0">
                    @if($config->tipe === 'boolean')
                    <span class="text-sm font-medium {{ $config->value === '1' || $config->value === 'true' ? 'text-emerald-600' : 'text-slate-500' }}">
                        {{ $config->value === '1' || $config->value === 'true' ? 'Ya' : 'Tidak' }}
                    </span>
                    @else
                    <span class="text-sm text-slate-700 font-medium">{{ $config->value ?? '—' }}</span>
                    @endif
                    <a href="{{ route('superadmin.system-config.edit', $config) }}"
                       class="p-1.5 text-slate-400 hover:text-amber-600 hover:bg-amber-50 rounded-lg">
                        <i class="ti ti-edit text-sm"></i>
                    </a>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endforeach
</div>
@endsection
