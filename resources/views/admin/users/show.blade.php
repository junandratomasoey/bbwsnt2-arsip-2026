{{-- resources/views/admin/users/show.blade.php --}}
@extends('layouts.app')
@section('title', $user->name)

@section('breadcrumb')
    <a href="{{ route('superadmin.users.index') }}" class="text-slate-500 hover:text-slate-700 text-sm">Pengguna</a>
    <i class="ti ti-chevron-right text-slate-300 text-xs"></i>
    <span class="text-slate-800 font-medium text-sm">{{ $user->name }}</span>
@endsection

@section('content')
<x-page-header :title="$user->name" icon="ti-user">
    <a href="{{ route('superadmin.users.edit', $user) }}"
       class="inline-flex items-center gap-1.5 px-4 py-2 bg-white border border-slate-200 text-sm text-slate-700 rounded-xl hover:bg-slate-50">
        <i class="ti ti-edit text-slate-400"></i> Edit
    </a>
    @if($user->status === 'aktif')
    <form action="{{ route('superadmin.users.nonaktifkan', $user) }}" method="POST">
        @csrf
        <button onclick="return confirm('Nonaktifkan akun ini?')"
                class="inline-flex items-center gap-1.5 px-4 py-2 border border-red-200 text-red-600 text-sm rounded-xl hover:bg-red-50">
            <i class="ti ti-user-off"></i> Nonaktifkan
        </button>
    </form>
    @elseif($user->status === 'nonaktif')
    <form action="{{ route('superadmin.users.aktifkan', $user) }}" method="POST">
        @csrf
        <button class="inline-flex items-center gap-1.5 px-4 py-2 bg-emerald-600 text-white text-sm rounded-xl hover:bg-emerald-700">
            <i class="ti ti-user-check"></i> Aktifkan
        </button>
    </form>
    @endif
</x-page-header>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-5">
        <div class="bg-white border border-slate-200 rounded-xl p-5">
            <div class="flex items-center gap-4 mb-5">
                <div class="w-14 h-14 rounded-full bg-gradient-to-br from-sky-500 to-blue-600
                            flex items-center justify-center text-white text-xl font-bold">
                    {{ $user->inisial() }}
                </div>
                <div>
                    <h2 class="font-semibold text-slate-800">{{ $user->name }}</h2>
                    <p class="text-sm text-slate-500">{{ $user->email }}</p>
                    <div class="flex gap-2 mt-1">
                        @php $sc = match($user->status){ 'aktif'=>'bg-emerald-100 text-emerald-700','pending'=>'bg-amber-100 text-amber-700',default=>'bg-red-100 text-red-700' }; @endphp
                        <span class="text-xs px-2 py-0.5 rounded {{ $sc }}">{{ ucfirst($user->status) }}</span>
                        @if($user->roles->isNotEmpty())
                        <span class="text-xs px-2 py-0.5 rounded bg-sky-100 text-sky-700">{{ $user->namaRole() }}</span>
                        @endif
                    </div>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                @foreach([
                    ['NIP', $user->nip ?? '—'],
                    ['Jabatan', $user->jabatan_struktural ?? '—'],
                    ['Unit Kerja', $user->unitKerja?->namaLengkap() ?? '—'],
                    ['No. HP', $user->no_hp ?? '—'],
                    ['Login Terakhir', $user->last_login_at?->format('d M Y H:i') ?? 'Belum pernah'],
                    ['Bergabung', $user->created_at->format('d M Y')],
                ] as [$l, $v])
                <div><p class="text-xs text-slate-400 mb-0.5">{{ $l }}</p>
                    <p class="text-sm text-slate-700">{{ $v }}</p>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Form assign role --}}
        @can('user.approve')
        <div class="bg-white border border-slate-200 rounded-xl p-5">
            <h3 class="text-sm font-semibold text-slate-700 mb-3">Ubah Role</h3>
            <form action="{{ route('superadmin.users.assign-role', $user) }}" method="POST" class="flex gap-3">
                @csrf
                <select name="role" class="flex-1 border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500">
                    @foreach(\Spatie\Permission\Models\Role::where('name','!=','superadmin')->get() as $r)
                    <option value="{{ $r->name }}" @selected($user->hasRole($r->name))>
                        {{ ucfirst(str_replace('_',' ',$r->name)) }}
                    </option>
                    @endforeach
                </select>
                <button type="submit" class="px-4 py-2 bg-sky-600 text-white text-sm rounded-lg hover:bg-sky-700">
                    Simpan Role
                </button>
            </form>
        </div>
        @endcan
    </div>

    {{-- Audit log user --}}
    <div class="bg-white border border-slate-200 rounded-xl overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100">
            <h3 class="text-sm font-semibold text-slate-700">Aktivitas Terakhir</h3>
        </div>
        @if($auditLogs->isEmpty())
        <p class="px-5 py-6 text-center text-xs text-slate-400">Belum ada aktivitas</p>
        @else
        <div class="divide-y divide-slate-100">
            @foreach($auditLogs->take(12) as $log)
            <div class="px-5 py-2.5">
                <p class="text-xs font-medium text-slate-700">{{ ucfirst($log->action) }} {{ $log->entity_type }}</p>
                @if($log->entity_label)
                <p class="text-xs text-slate-400 truncate">{{ $log->entity_label }}</p>
                @endif
                <p class="text-xs text-slate-400 mt-0.5">{{ $log->created_at->diffForHumans() }}</p>
            </div>
            @endforeach
        </div>
        @endif
    </div>
</div>
@endsection
