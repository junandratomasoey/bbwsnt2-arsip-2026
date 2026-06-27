@extends('layouts.app')
@section('title', 'Form Jenis Aset')
@section('content')
<x-page-header title="Form Jenis Aset" icon="ti-building-bridge" />
<div class="bg-white border border-slate-200 rounded-xl p-8 text-center">
    <i class="ti ti-building-bridge text-4xl text-slate-200 block mb-3"></i>
    <p class="text-slate-400 mb-4">Halaman Form Jenis Aset</p>
    <a href="{{ route('dashboard') }}" class="text-sm text-sky-600 hover:underline">← Kembali</a>
</div>
@endsection
