{{-- resources/views/superadmin/activity-log.blade.php --}}
@extends('layouts.app')
@section('title', 'Audit Log')

@section('breadcrumb')
    <span class="text-slate-500 text-sm">Superadmin</span>
    <i class="ti ti-chevron-right text-slate-300 text-xs"></i>
    <span class="text-slate-800 font-medium text-sm">Audit Log</span>
@endsection

@section('content')
<x-page-header title="Audit Log" desc="Riwayat aktivitas seluruh pengguna sistem" icon="ti-history" />

<div class="bg-white border border-slate-200 rounded-xl overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 border-b border-slate-200 text-xs font-medium text-slate-500 uppercase tracking-wide">
                <tr>
                    <th class="px-5 py-3 text-left">Waktu</th>
                    <th class="px-5 py-3 text-left">Pengguna</th>
                    <th class="px-5 py-3 text-left">Aksi</th>
                    <th class="px-5 py-3 text-left">Entitas</th>
                    <th class="px-5 py-3 text-left hidden lg:table-cell">IP Address</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($logs as $log)
                <tr class="hover:bg-slate-50">
                    <td class="px-5 py-3 text-xs text-slate-500 whitespace-nowrap">
                        {{ $log->created_at->format('d M Y H:i:s') }}
                    </td>
                    <td class="px-5 py-3">
                        <p class="text-sm font-medium text-slate-700">{{ $log->user_name ?? 'Sistem' }}</p>
                        <p class="text-xs text-slate-400">{{ $log->user_email }}</p>
                    </td>
                    <td class="px-5 py-3">
                        @php
                        $actionBadge = match($log->action) {
                            'create'   => 'bg-emerald-100 text-emerald-700',
                            'update'   => 'bg-amber-100 text-amber-700',
                            'delete'   => 'bg-red-100 text-red-700',
                            'login'    => 'bg-blue-100 text-blue-700',
                            'logout'   => 'bg-slate-100 text-slate-600',
                            'download' => 'bg-purple-100 text-purple-700',
                            'approve'  => 'bg-teal-100 text-teal-700',
                            default    => 'bg-slate-100 text-slate-600',
                        };
                        @endphp
                        <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium {{ $actionBadge }}">
                            {{ ucfirst($log->action) }}
                        </span>
                    </td>
                    <td class="px-5 py-3">
                        @if($log->entity_type)
                        <p class="text-xs font-medium text-slate-700">{{ class_basename($log->entity_type) }}</p>
                        @endif
                        @if($log->entity_label)
                        <p class="text-xs text-slate-400 truncate max-w-48">{{ $log->entity_label }}</p>
                        @endif
                    </td>
                    <td class="px-5 py-3 hidden lg:table-cell text-xs font-mono text-slate-400">
                        {{ $log->ip_address ?? '—' }}
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="px-5 py-10 text-center text-slate-400">Belum ada log</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($logs->hasPages())
    <div class="px-5 py-4 border-t border-slate-100">{{ $logs->links() }}</div>
    @endif
</div>
@endsection
