@extends('layouts.app')
@section('title', 'Jenis Dokumen')
@section('content')
<x-page-header title="Jenis Dokumen" icon="ti-files" />
<div class="bg-white border border-slate-200 rounded-xl p-8 text-center">
    <i class="ti ti-files text-4xl text-slate-200 block mb-3"></i>
    <p class="text-slate-400 mb-4">Halaman Jenis Dokumen</p>
    <a href="{{ route('dashboard') }}" class="text-sm text-sky-600 hover:underline">← Kembali</a>
</div>
@endsection
