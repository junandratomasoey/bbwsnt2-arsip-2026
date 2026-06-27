@extends('layouts.app')
@section('title', 'Progress Proyek')
@section('content')
<x-page-header title="Progress Proyek" icon="ti-chart-line" />
<div class="bg-white border border-slate-200 rounded-xl p-8 text-center">
    <i class="ti ti-chart-line text-4xl text-slate-200 block mb-3"></i>
    <p class="text-slate-400 mb-4">Halaman Progress Proyek</p>
    <a href="{{ route('projects.index') }}" class="text-sm text-sky-600 hover:underline">← Kembali</a>
</div>
@endsection
