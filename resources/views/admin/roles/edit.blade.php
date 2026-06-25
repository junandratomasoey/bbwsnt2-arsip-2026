@extends('layouts.app')
@section('title', 'Edit Role')
@section('breadcrumb')
    <a href="{{ route('superadmin.roles.index') }}" class="text-slate-500 hover:text-slate-700">Role & Akses</a>
    <i class="ti ti-chevron-right text-slate-300 text-xs"></i>
    <span class="text-slate-800 font-medium">Edit: {{ $role->name }}</span>
@endsection
@section('content')
@include('admin.roles.create')
@endsection
