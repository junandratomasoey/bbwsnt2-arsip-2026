@extends('layouts.app')
@section('title', 'Laporan Peminjaman')
@section('content')
<x-page-header title="Laporan Peminjaman" icon="ti-book-download" />
<div class="bg-white border border-slate-200 rounded-xl p-8 text-center">
    <i class="ti ti-book-download text-4xl text-slate-200 block mb-3"></i>
    <p class="text-slate-400 mb-4">Halaman Laporan Peminjaman</p>
    <a href="{{ route('reports.index') }}" class="text-sm text-sky-600 hover:underline">← Kembali</a>
</div>
@endsection
