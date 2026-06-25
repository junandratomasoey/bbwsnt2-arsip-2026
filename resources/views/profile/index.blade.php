{{-- resources/views/profile/index.blade.php --}}
@extends('layouts.app')
@section('title', 'Profil Saya')

@section('breadcrumb')
    <span class="text-slate-800 font-medium text-sm">Profil Saya</span>
@endsection

@section('content')
<div class="max-w-2xl">
<x-page-header title="Profil Saya" icon="ti-user" />

<div class="bg-white border border-slate-200 rounded-xl overflow-hidden">
    {{-- Header profil --}}
    <div class="p-6 flex items-center gap-4 bg-gradient-to-r from-sky-50 to-blue-50 border-b border-slate-100">
        <div class="w-16 h-16 rounded-full bg-gradient-to-br from-sky-500 to-blue-600
                    flex items-center justify-center text-white text-2xl font-bold flex-shrink-0">
            {{ $user->inisial() }}
        </div>
        <div>
            <h2 class="text-lg font-semibold text-slate-800">{{ $user->name }}</h2>
            <p class="text-sm text-slate-500">{{ $user->email }}</p>
            <div class="flex items-center gap-2 mt-1">
                <span class="text-xs px-2 py-0.5 rounded-full bg-sky-100 text-sky-700">
                    {{ $user->namaRole() }}
                </span>
                <span class="text-xs px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-700">
                    {{ ucfirst($user->status) }}
                </span>
            </div>
        </div>
    </div>

    {{-- Info --}}
    <div class="p-6 grid grid-cols-2 gap-4">
        @foreach([
            ['NIP', $user->nip ?? '—'],
            ['Jabatan', $user->jabatan_struktural ?? '—'],
            ['Unit Kerja', $user->unitKerja?->namaLengkap() ?? '—'],
            ['No. HP', $user->no_hp ?? '—'],
            ['Login Terakhir', $user->last_login_at?->format('d M Y H:i') ?? '—'],
            ['Bergabung', $user->created_at->format('d M Y')],
        ] as [$label, $val])
        <div>
            <p class="text-xs text-slate-400 mb-0.5">{{ $label }}</p>
            <p class="text-sm font-medium text-slate-700">{{ $val }}</p>
        </div>
        @endforeach
    </div>

    {{-- Form edit --}}
    <div class="px-6 pb-6 pt-4 border-t border-slate-100">
        <h3 class="text-sm font-semibold text-slate-700 mb-4">Perbarui Profil</h3>
        <form method="POST" action="{{ route('profile.update') }}" class="space-y-4">
            @csrf @method('PUT')
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Nama Lengkap</label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                       class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm
                              focus:outline-none focus:ring-2 focus:ring-sky-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">No. HP</label>
                <input type="text" name="no_hp" value="{{ old('no_hp', $user->no_hp) }}"
                       class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm
                              focus:outline-none focus:ring-2 focus:ring-sky-500">
            </div>
            <div class="pt-2 border-t border-slate-100">
                <p class="text-xs font-medium text-slate-600 mb-3">Ganti Password (kosongkan jika tidak ingin ganti)</p>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs text-slate-500 mb-1">Password Baru</label>
                        <input type="password" name="password"
                               class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm
                                      focus:outline-none focus:ring-2 focus:ring-sky-500">
                    </div>
                    <div>
                        <label class="block text-xs text-slate-500 mb-1">Konfirmasi Password</label>
                        <input type="password" name="password_confirmation"
                               class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm
                                      focus:outline-none focus:ring-2 focus:ring-sky-500">
                    </div>
                </div>
            </div>
            <div class="flex justify-end">
                <button type="submit"
                        class="px-5 py-2.5 bg-sky-600 text-white text-sm font-medium rounded-xl hover:bg-sky-700">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>
</div>
@endsection
