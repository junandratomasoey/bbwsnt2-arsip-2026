@extends('layouts.app')
@section('title', '404 — Halaman Tidak Ditemukan')
@section('content')
<div class="min-h-[60vh] flex flex-col items-center justify-center text-center px-4">
    <div class="w-20 h-20 bg-slate-100 border border-slate-200 rounded-full flex items-center justify-center mb-6">
        <i class="ti ti-file-off text-slate-400 text-4xl"></i>
    </div>
    <h1 class="text-5xl font-bold text-slate-800 mb-3">404</h1>
    <p class="text-lg font-medium text-slate-700 mb-2">Halaman Tidak Ditemukan</p>
    <p class="text-slate-500 max-w-sm mb-8">Halaman yang Anda cari tidak ada atau telah dipindahkan.</p>
    <div class="flex gap-3">
        <button onclick="history.back()"
                class="px-5 py-2.5 border border-slate-200 text-slate-600 text-sm rounded-xl hover:bg-slate-50">
            ← Kembali
        </button>
        <a href="{{ route('dashboard') }}"
           class="px-5 py-2.5 bg-slate-800 text-white text-sm rounded-xl hover:bg-slate-700">
            <i class="ti ti-home"></i> Dashboard
        </a>
    </div>
</div>
@endsection
