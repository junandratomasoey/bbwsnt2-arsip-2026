@extends('layouts.app')
@section('title', 'Persetujuan Akun')

@section('breadcrumb')
    <span class="text-slate-500">Administrasi</span>
    <i class="ti ti-chevron-right text-slate-300 text-xs"></i>
    <span class="text-slate-800 font-medium">Persetujuan Akun</span>
@endsection

@section('content')
<x-page-header title="Persetujuan Pendaftaran"
    desc="{{ $pending->total() }} akun menunggu persetujuan" />

@if($pending->isEmpty())
<div class="bg-white border border-slate-200 rounded-xl p-16 text-center">
    <div class="w-16 h-16 bg-emerald-100 rounded-full flex items-center justify-center mx-auto mb-4">
        <i class="ti ti-circle-check text-emerald-600 text-3xl"></i>
    </div>
    <p class="text-slate-700 font-medium">Semua akun sudah diproses</p>
    <p class="text-slate-400 text-sm mt-1">Tidak ada pendaftaran yang menunggu persetujuan.</p>
</div>
@else
<div class="space-y-3">
    @foreach($pending as $user)
    <div class="bg-white border border-amber-200 rounded-xl p-5 flex flex-col sm:flex-row sm:items-center gap-5">

        {{-- Info user --}}
        <div class="flex items-start gap-4 flex-1 min-w-0">
            <div class="w-12 h-12 rounded-full bg-amber-100 flex items-center justify-center text-amber-700 font-semibold text-lg flex-shrink-0">
                {{ strtoupper(substr($user->name, 0, 1)) }}
            </div>
            <div class="min-w-0">
                <p class="font-semibold text-slate-800">{{ $user->name }}</p>
                <p class="text-sm text-slate-500">{{ $user->email }}</p>
                <div class="flex flex-wrap gap-3 mt-1.5 text-xs text-slate-400">
                    @if($user->nip)
                    <span><i class="ti ti-id-badge"></i> {{ $user->nip }}</span>
                    @endif
                    @if($user->jabatan)
                    <span><i class="ti ti-briefcase"></i> {{ $user->jabatan }}</span>
                    @endif
                    @if($user->unitKerja)
                    <span><i class="ti ti-building"></i> {{ $user->unitKerja->namaLengkap() }}</span>
                    @endif
                    <span><i class="ti ti-clock"></i> Daftar {{ $user->created_at->diffForHumans() }}</span>
                </div>
            </div>
        </div>

        {{-- Aksi --}}
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 flex-shrink-0">

            {{-- Form approve --}}
            <form action="{{ route('admin.users.approve', $user) }}" method="POST" class="flex gap-2">
                @csrf
                <select name="role" required
                        class="border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500 bg-white">
                    @foreach($roles->where('name','!=','superadmin') as $r)
                    <option value="{{ $r->name }}">{{ ucfirst(str_replace('_',' ',$r->name)) }}</option>
                    @endforeach
                </select>
                <button type="submit"
                        class="px-4 py-2 bg-emerald-600 text-white text-sm rounded-lg hover:bg-emerald-700 flex items-center gap-1.5 whitespace-nowrap">
                    <i class="ti ti-check"></i> Setujui
                </button>
            </form>

            {{-- Form tolak --}}
            <button onclick="openTolak({{ $user->id }}, '{{ $user->name }}')"
                    class="px-4 py-2 border border-red-200 text-red-600 text-sm rounded-lg hover:bg-red-50 flex items-center gap-1.5 whitespace-nowrap">
                <i class="ti ti-x"></i> Tolak
            </button>
        </div>
    </div>
    @endforeach

    @if($pending->hasPages())
    <div class="mt-4">{{ $pending->links() }}</div>
    @endif
</div>
@endif

{{-- Modal tolak --}}
<div id="modal-tolak" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6">
        <h3 class="text-base font-semibold text-slate-800 mb-1">Tolak Pendaftaran</h3>
        <p class="text-sm text-slate-500 mb-4">Berikan alasan penolakan untuk <strong id="tolak-nama"></strong>:</p>
        <form id="form-tolak" method="POST">
            @csrf
            <textarea name="alasan_tolak" rows="3" required placeholder="Tulis alasan penolakan..."
                      class="w-full border border-slate-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-red-500 resize-none mb-4"></textarea>
            <div class="flex gap-3 justify-end">
                <button type="button" onclick="closeTolak()"
                        class="px-4 py-2 text-sm text-slate-600 border border-slate-200 rounded-xl hover:bg-slate-50">Batal</button>
                <button type="submit"
                        class="px-4 py-2 text-sm text-white bg-red-600 rounded-xl hover:bg-red-700">Tolak Pendaftaran</button>
            </div>
        </form>
    </div>
</div>

<script>
function openTolak(id, nama) {
    document.getElementById('tolak-nama').textContent = nama;
    document.getElementById('form-tolak').action = `/admin/users/${id}/tolak`;
    document.getElementById('modal-tolak').classList.remove('hidden');
}
function closeTolak() {
    document.getElementById('modal-tolak').classList.add('hidden');
}
</script>
@endsection
