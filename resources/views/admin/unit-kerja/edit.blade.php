@extends('layouts.app')
@section('title', 'Edit Unit Kerja')
@section('breadcrumb')
    <a href="{{ route('superadmin.unit-kerja.index') }}" class="text-slate-500 hover:text-slate-700">Unit Kerja</a>
    <i class="ti ti-chevron-right text-slate-300 text-xs"></i>
    <span class="text-slate-800 font-medium">Edit {{ $unitKerja->namaLengkap() }}</span>
@endsection
@section('content')
@include('admin.unit-kerja.create')
@endsection
