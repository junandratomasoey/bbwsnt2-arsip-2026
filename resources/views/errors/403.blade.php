{{-- resources/views/errors/403.blade.php --}}
@extends('layouts.app')
@section('title', '403 — Akses Ditolak')
@section('content')
<div class="min-h-[60vh] flex flex-col items-center justify-center text-center px-4">
    <div class="w-20 h-20 rounded-2xl flex items-center justify-center mb-6"
         style="background: rgba(0,51,102,0.08); border: 2px solid rgba(0,51,102,0.15)">
        <i class="ti ti-shield-x text-4xl" style="color:#003366"></i>
    </div>
    <div class="w-12 h-1 rounded-full mb-4" style="background:#F4A81D"></div>
    <h1 class="text-5xl font-black mb-3" style="color:#003366">403</h1>
    <p class="text-lg font-semibold text-slate-700 mb-2">Akses Ditolak</p>
    <p class="text-slate-500 max-w-sm mb-8">
        {{ $message ?? 'Anda tidak memiliki izin untuk mengakses halaman ini.' }}
        Hubungi administrator jika membutuhkan akses.
    </p>
    <div class="flex gap-3">
        <button onclick="history.back()"
                class="px-5 py-2.5 border-2 rounded-xl text-sm font-semibold transition-all hover:bg-slate-50"
                style="border-color:#003366; color:#003366">
            ← Kembali
        </button>
        <a href="{{ route('dashboard') }}"
           class="px-5 py-2.5 text-white text-sm font-semibold rounded-xl transition-all hover:opacity-90"
           style="background:#003366">
            <i class="ti ti-home mr-1"></i> Dashboard
        </a>
    </div>
</div>
@endsection
