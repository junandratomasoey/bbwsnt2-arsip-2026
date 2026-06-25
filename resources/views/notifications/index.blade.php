{{-- resources/views/notifications/index.blade.php --}}
@extends('layouts.app')
@section('title', 'Notifikasi')

@section('breadcrumb')
    <span class="text-slate-800 font-medium text-sm">Notifikasi</span>
@endsection

@section('content')
<x-page-header title="Notifikasi" icon="ti-bell">
    @if(auth()->user()->unreadNotifications()->count() > 0)
    <form action="{{ route('notifications.read-all') }}" method="POST">
        @csrf
        <button class="px-4 py-2 text-sm border border-slate-200 text-slate-600 rounded-xl hover:bg-slate-50">
            Tandai semua dibaca
        </button>
    </form>
    @endif
</x-page-header>

<div class="bg-white border border-slate-200 rounded-xl overflow-hidden">
    @forelse($notifications as $notif)
    <div class="flex items-start gap-4 px-5 py-4 border-b border-slate-100
                {{ !$notif->is_read ? 'bg-sky-50/40' : '' }} hover:bg-slate-50 transition-colors">
        <div class="w-9 h-9 rounded-full bg-sky-100 flex items-center justify-center flex-shrink-0 mt-0.5">
            <i class="ti {{ $notif->icon ?? 'ti-bell' }} text-sky-600 text-base"></i>
        </div>
        <div class="flex-1 min-w-0">
            <div class="flex items-start justify-between gap-2">
                <p class="text-sm font-medium text-slate-800">{{ $notif->title }}</p>
                <span class="text-xs text-slate-400 flex-shrink-0">{{ $notif->created_at->diffForHumans() }}</span>
            </div>
            <p class="text-sm text-slate-600 mt-0.5">{{ $notif->message }}</p>
            <div class="flex items-center gap-3 mt-2">
                @if($notif->action_url)
                <a href="{{ $notif->action_url }}" class="text-xs text-sky-600 hover:underline">Lihat detail →</a>
                @endif
                @if(!$notif->is_read)
                <form action="{{ route('notifications.read', $notif->id) }}" method="POST">
                    @csrf
                    <button class="text-xs text-slate-400 hover:text-slate-600">Tandai dibaca</button>
                </form>
                @endif
            </div>
        </div>
        @if(!$notif->is_read)
        <div class="w-2 h-2 rounded-full bg-sky-500 flex-shrink-0 mt-2"></div>
        @endif
    </div>
    @empty
    <div class="py-16 text-center">
        <i class="ti ti-bell-off text-4xl text-slate-200 block mb-3"></i>
        <p class="text-slate-400">Tidak ada notifikasi</p>
    </div>
    @endforelse
</div>

@if($notifications->hasPages())
<div class="mt-4">{{ $notifications->links() }}</div>
@endif
@endsection
