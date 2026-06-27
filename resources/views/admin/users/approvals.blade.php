{{-- resources/views/admin/users/approvals.blade.php --}}
@extends('layouts.app')
@section('title', 'Persetujuan Akun')

@section('breadcrumb')
    <a href="{{ route('superadmin.users.index') }}" class="text-slate-500 hover:text-slate-700 text-sm">Pengguna</a>
    <i class="ti ti-chevron-right text-slate-300 text-xs"></i>
    <span class="text-slate-800 font-medium text-sm">Persetujuan Akun</span>
@endsection

@section('content')
<x-page-header title="Persetujuan Akun" icon="ti-user-check"
    :desc="$pending->total() . ' akun menunggu persetujuan'" />

@if($pending->isEmpty())
<div class="bg-white border border-slate-200 rounded-xl py-16 text-center">
    <i class="ti ti-circle-check text-4xl text-emerald-300 block mb-3"></i>
    <p class="text-slate-500">Semua akun sudah diproses</p>
</div>
@else
<div class="space-y-3">
    @foreach($pending as $user)
    <div class="bg-white border border-slate-200 rounded-xl p-5" x-data="{ showTolak: false }">
        <div class="flex flex-col sm:flex-row sm:items-center gap-4">
            <div class="flex items-center gap-3 flex-1">
                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-sky-400 to-blue-600
                            flex items-center justify-center text-white font-bold text-sm flex-shrink-0">
                    {{ $user->inisial() }}
                </div>
                <div>
                    <p class="font-semibold text-slate-800">{{ $user->name }}</p>
                    <p class="text-sm text-slate-500">{{ $user->email }}</p>
                    @if($user->nip)
                    <p class="text-xs text-slate-400 font-mono">NIP: {{ $user->nip }}</p>
                    @endif
                    <p class="text-xs text-slate-400 mt-0.5">
                        Unit: {{ $user->unitKerja?->namaLengkap() ?? 'Belum diset' }} ·
                        Daftar: {{ $user->created_at->diffForHumans() }}
                    </p>
                </div>
            </div>

            <div class="flex flex-wrap gap-2 flex-shrink-0">
                {{-- Form approve --}}
                <form action="{{ route('admin.users.approve', $user) }}" method="POST" class="flex items-center gap-2">
                    @csrf
                    <select name="role" required
                            class="border border-slate-200 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500">
                        @foreach($roles->where('name','!=','superadmin') as $r)
                        <option value="{{ $r->name }}">{{ ucfirst(str_replace('_',' ',$r->name)) }}</option>
                        @endforeach
                    </select>
                    <button type="submit"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-600 text-white text-sm rounded-lg hover:bg-emerald-700">
                        <i class="ti ti-check"></i> Setujui
                    </button>
                </form>

                <button @click="showTolak = !showTolak"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 border border-red-200 text-red-600 text-sm rounded-lg hover:bg-red-50">
                    <i class="ti ti-x"></i> Tolak
                </button>
            </div>
        </div>

        {{-- Form tolak --}}
        <div x-show="showTolak" x-cloak class="mt-4 pt-4 border-t border-slate-100">
            <form action="{{ route('admin.users.tolak', $user) }}" method="POST" class="flex gap-2">
                @csrf
                <input type="text" name="alasan_tolak" required placeholder="Alasan penolakan (wajib diisi)..."
                       class="flex-1 border border-slate-200 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-red-400">
                <button type="submit"
                        class="px-4 py-1.5 bg-red-600 text-white text-sm rounded-lg hover:bg-red-700">
                    Konfirmasi Tolak
                </button>
            </form>
        </div>
    </div>
    @endforeach
</div>
@if($pending->hasPages())
<div class="mt-4">{{ $pending->links() }}</div>
@endif
@endif
@endsection
