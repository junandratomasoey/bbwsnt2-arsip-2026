@extends('layouts.app')
@section('title', 'Pengguna')

@section('breadcrumb')
    <span class="text-slate-500 text-sm">Admin</span>
    <i class="ti ti-chevron-right text-slate-300 text-xs"></i>
    <span class="text-slate-800 font-medium text-sm">Pengguna</span>
@endsection

@section('content')
<x-page-header title="Pengguna" desc="Kelola akun dan hak akses pengguna sistem" icon="ti-users">
    @can('user.create')
    <a href="{{ route('superadmin.users.create') }}"
       class="inline-flex items-center gap-2 px-4 py-2 bg-sky-600 text-white text-sm font-medium rounded-xl hover:bg-sky-700">
        <i class="ti ti-plus"></i> Tambah Pengguna
    </a>
    @endcan
</x-page-header>

<div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-5">
    <x-stat-card label="Total"    value="{{ $stats['total'] }}"    icon="ti-users"      color="slate" />
    <x-stat-card label="Aktif"    value="{{ $stats['aktif'] }}"    icon="ti-user-check" color="green" />
    <x-stat-card label="Pending"  value="{{ $stats['pending'] }}"  icon="ti-user-clock" color="amber"
        :href="route('admin.approvals')" />
    <x-stat-card label="Nonaktif" value="{{ $stats['nonaktif'] }}" icon="ti-user-off"   color="red" />
</div>

<div class="bg-white border border-slate-200 rounded-xl p-4 mb-4">
    <form method="GET" class="flex flex-wrap gap-2">
        <input type="text" name="search" value="{{ request('search') }}"
               placeholder="Nama, email, NIP..."
               class="flex-1 min-w-48 border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500">
        <select name="status" class="border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500">
            <option value="">Semua status</option>
            @foreach(['aktif'=>'Aktif','pending'=>'Pending','nonaktif'=>'Nonaktif','ditolak'=>'Ditolak'] as $v=>$l)
            <option value="{{ $v }}" @selected(request('status') === $v)>{{ $l }}</option>
            @endforeach
        </select>
        <select name="unit_kerja_id" class="border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500">
            <option value="">Semua unit kerja</option>
            @foreach($unitKerjas as $uk)
            <option value="{{ $uk->id }}" @selected(request('unit_kerja_id') === $uk->id)>
                [{{ strtoupper($uk->tipe) }}] {{ $uk->singkatan ?? $uk->nama }}
            </option>
            @endforeach
        </select>
        <select name="role" class="border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500">
            <option value="">Semua role</option>
            @foreach($roles as $r)
            <option value="{{ $r->name }}" @selected(request('role') === $r->name)>{{ ucfirst(str_replace('_',' ',$r->name)) }}</option>
            @endforeach
        </select>
        <button type="submit" class="px-4 py-2 bg-slate-800 text-white text-sm rounded-lg hover:bg-slate-700">
            <i class="ti ti-search"></i>
        </button>
        @if(request()->hasAny(['search','status','unit_kerja_id','role']))
        <a href="{{ route('superadmin.users.index') }}" class="px-4 py-2 text-sm text-slate-500 border border-slate-200 rounded-lg hover:bg-slate-50">Reset</a>
        @endif
    </form>
</div>

<div class="bg-white border border-slate-200 rounded-xl overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 border-b border-slate-200 text-xs font-medium text-slate-500 uppercase tracking-wide">
            <tr>
                <th class="px-5 py-3 text-left">Pengguna</th>
                <th class="px-5 py-3 text-left hidden lg:table-cell">Unit Kerja</th>
                <th class="px-5 py-3 text-left">Role</th>
                <th class="px-5 py-3 text-left">Status</th>
                <th class="px-5 py-3 text-center">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse($query as $user)
            <tr class="hover:bg-slate-50 transition-colors">
                <td class="px-5 py-3">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-gradient-to-br from-sky-400 to-blue-600
                                    flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
                            {{ $user->inisial() }}
                        </div>
                        <div>
                            <a href="{{ route('superadmin.users.show', $user) }}"
                               class="font-medium text-slate-800 hover:text-sky-600">{{ $user->name }}</a>
                            <p class="text-xs text-slate-400">{{ $user->email }}</p>
                        </div>
                    </div>
                </td>
                <td class="px-5 py-3 hidden lg:table-cell">
                    <span class="text-xs text-slate-600">{{ $user->unitKerja?->singkatan ?? '—' }}</span>
                </td>
                <td class="px-5 py-3">
                    @if($user->roles->isNotEmpty())
                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium bg-slate-100 text-slate-700">
                        {{ ucfirst(str_replace('_',' ',$user->roles->first()->name)) }}
                    </span>
                    @else
                    <span class="text-xs text-slate-400">—</span>
                    @endif
                </td>
                <td class="px-5 py-3">
                    @php $sc = match($user->status){ 'aktif'=>'bg-emerald-100 text-emerald-700','pending'=>'bg-amber-100 text-amber-700','nonaktif'=>'bg-slate-100 text-slate-600','ditolak'=>'bg-red-100 text-red-700',default=>'bg-gray-100 text-gray-600' }; @endphp
                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium {{ $sc }}">
                        {{ ucfirst($user->status) }}
                    </span>
                </td>
                <td class="px-5 py-3">
                    <div class="flex items-center justify-center gap-1">
                        <a href="{{ route('superadmin.users.show', $user) }}"
                           class="p-1.5 text-slate-400 hover:text-sky-600 hover:bg-sky-50 rounded-lg">
                            <i class="ti ti-eye text-sm"></i>
                        </a>
                        @can('user.edit')
                        <a href="{{ route('superadmin.users.edit', $user) }}"
                           class="p-1.5 text-slate-400 hover:text-amber-600 hover:bg-amber-50 rounded-lg">
                            <i class="ti ti-edit text-sm"></i>
                        </a>
                        @endcan
                        @if($user->status === 'pending')
                        <button onclick="openApprove('{{ $user->id }}','{{ $user->name }}')"
                                class="p-1.5 text-slate-400 hover:text-emerald-600 hover:bg-emerald-50 rounded-lg" title="Setujui">
                            <i class="ti ti-user-check text-sm"></i>
                        </button>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="5" class="px-5 py-10 text-center text-slate-400">Tidak ada pengguna.</td></tr>
            @endforelse
        </tbody>
    </table>
    @if($query->hasPages())
    <div class="px-5 py-4 border-t border-slate-100">{{ $query->links() }}</div>
    @endif
</div>

{{-- Modal approve --}}
<div id="modal-approve" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6">
        <h3 class="text-base font-semibold text-slate-800 mb-4">
            Setujui Pendaftaran: <span id="modal-nama" class="text-sky-600"></span>
        </h3>
        <form id="form-approve" method="POST">
            @csrf
            <label class="block text-sm font-medium text-slate-700 mb-1.5">Pilih Role</label>
            <select name="role" required class="w-full border border-slate-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500 mb-4">
                @foreach($roles->where('name','!=','superadmin') as $r)
                <option value="{{ $r->name }}">{{ ucfirst(str_replace('_',' ',$r->name)) }}</option>
                @endforeach
            </select>
            <div class="flex gap-3 justify-end">
                <button type="button" onclick="document.getElementById('modal-approve').classList.add('hidden')"
                        class="px-4 py-2 text-sm text-slate-600 border border-slate-200 rounded-xl hover:bg-slate-50">Batal</button>
                <button type="submit" class="px-4 py-2 text-sm text-white bg-emerald-600 rounded-xl hover:bg-emerald-700">
                    Setujui & Aktifkan
                </button>
            </div>
        </form>
    </div>
</div>
<script>
function openApprove(id, nama) {
    document.getElementById('modal-nama').textContent = nama;
    document.getElementById('form-approve').action = `/superadmin/users/${id}/approve`;
    document.getElementById('modal-approve').classList.remove('hidden');
}
</script>
@endsection
