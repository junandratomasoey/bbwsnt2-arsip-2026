@extends('layouts.app')
@section('title', 'Ajukan Peminjaman')

@section('breadcrumb')
    <a href="{{ route('loans.index') }}" class="text-slate-500 hover:text-slate-700 text-sm">Peminjaman</a>
    <i class="ti ti-chevron-right text-slate-300 text-xs"></i>
    <span class="text-slate-800 font-medium text-sm">Ajukan Peminjaman</span>
@endsection

@section('content')
<div class="max-w-xl">
<x-page-header title="Ajukan Peminjaman Dokumen" icon="ti-book-download" />

<form method="POST" action="{{ route('loans.store') }}" class="space-y-5">
    @csrf

    {{-- Info dokumen jika sudah dipilih --}}
    @if($dokumen)
    <div class="bg-sky-50 border border-sky-100 rounded-xl px-4 py-3 flex items-center gap-3">
        <i class="ti ti-file-description text-sky-500 text-lg flex-shrink-0"></i>
        <div class="min-w-0">
            <p class="text-sm font-semibold text-sky-800 truncate">{{ $dokumen->judul }}</p>
            <p class="text-xs text-sky-600 mt-0.5">{{ $dokumen->doc_number }} · {{ $dokumen->documentType?->nama }}</p>
        </div>
        <input type="hidden" name="document_id" value="{{ $dokumen->id }}">
    </div>
    @endif

    <div class="bg-white border border-slate-200 rounded-xl p-5 space-y-4">

        {{-- Pilih dokumen jika belum dipilih --}}
        @if(!$dokumen)
        <div>
            <label class="block text-xs font-medium text-slate-600 mb-1">
                Dokumen <span class="text-red-500">*</span>
            </label>
            <select name="document_id" required
                    class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm
                           focus:outline-none focus:ring-2 focus:ring-sky-500
                           @error('document_id') border-red-400 @enderror">
                <option value="">Pilih dokumen yang akan dipinjam...</option>
                @foreach($documents as $doc)
                <option value="{{ $doc->id }}" @selected(old('document_id') === $doc->id)>
                    [{{ $doc->documentType?->kode ?? '—' }}] {{ $doc->judul }}
                    @if($doc->doc_number)({{ $doc->doc_number }})@endif
                    — {{ ucfirst($doc->status) }}
                </option>
                @endforeach
            </select>
            @error('document_id')
            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
            @enderror
        </div>
        @endif

        {{-- Jenis peminjaman --}}
        <div>
            <label class="block text-xs font-medium text-slate-600 mb-1">
                Jenis Peminjaman <span class="text-red-500">*</span>
            </label>
            <div class="grid grid-cols-2 gap-3">
                <label class="relative flex items-center gap-3 p-3 border rounded-xl cursor-pointer
                              hover:bg-slate-50 transition-colors has-[:checked]:border-sky-400
                              has-[:checked]:bg-sky-50">
                    <input type="radio" name="jenis" value="digital" required
                           @checked(old('jenis', 'digital') === 'digital')
                           class="text-sky-600 focus:ring-sky-500">
                    <div>
                        <p class="text-sm font-medium text-slate-700">Digital</p>
                        <p class="text-xs text-slate-400">Akses file softcopy</p>
                    </div>
                    <i class="ti ti-file-download text-sky-400 ml-auto"></i>
                </label>
                <label class="relative flex items-center gap-3 p-3 border rounded-xl cursor-pointer
                              hover:bg-slate-50 transition-colors has-[:checked]:border-sky-400
                              has-[:checked]:bg-sky-50">
                    <input type="radio" name="jenis" value="fisik"
                           @checked(old('jenis') === 'fisik')
                           class="text-sky-600 focus:ring-sky-500">
                    <div>
                        <p class="text-sm font-medium text-slate-700">Fisik</p>
                        <p class="text-xs text-slate-400">Pinjam dokumen asli</p>
                    </div>
                    <i class="ti ti-books text-sky-400 ml-auto"></i>
                </label>
            </div>
        </div>

        {{-- Tanggal --}}
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">
                    Tanggal Pinjam <span class="text-red-500">*</span>
                </label>
                <input type="date" name="tgl_pinjam_rencana" required
                       value="{{ old('tgl_pinjam_rencana', today()->format('Y-m-d')) }}"
                       min="{{ today()->format('Y-m-d') }}"
                       class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm
                              focus:outline-none focus:ring-2 focus:ring-sky-500
                              @error('tgl_pinjam_rencana') border-red-400 @enderror">
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">
                    Tanggal Kembali <span class="text-red-500">*</span>
                </label>
                <input type="date" name="tgl_kembali_rencana" required
                       value="{{ old('tgl_kembali_rencana', today()->addDays(7)->format('Y-m-d')) }}"
                       min="{{ today()->addDays(1)->format('Y-m-d') }}"
                       class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm
                              focus:outline-none focus:ring-2 focus:ring-sky-500
                              @error('tgl_kembali_rencana') border-red-400 @enderror">
            </div>
        </div>

        {{-- Keperluan --}}
        <div>
            <label class="block text-xs font-medium text-slate-600 mb-1">
                Keperluan / Alasan Peminjaman <span class="text-red-500">*</span>
            </label>
            <textarea name="keperluan" rows="3" required
                      placeholder="Jelaskan keperluan meminjam dokumen ini..."
                      class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm resize-none
                             focus:outline-none focus:ring-2 focus:ring-sky-500
                             @error('keperluan') border-red-400 @enderror">{{ old('keperluan') }}</textarea>
            @error('keperluan')
            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
            @enderror
        </div>
    </div>

    {{-- Info prosedur --}}
    <div class="bg-amber-50 border border-amber-200 rounded-xl px-4 py-3 flex items-start gap-2.5">
        <i class="ti ti-alert-triangle text-amber-500 text-lg flex-shrink-0 mt-0.5"></i>
        <div class="text-xs text-amber-700">
            <p class="font-semibold mb-1">Prosedur Peminjaman</p>
            <ul class="space-y-0.5 list-disc list-inside">
                <li>Permohonan akan diverifikasi oleh arsiparis/admin</li>
                <li>Peminjaman fisik maksimal 14 hari kerja</li>
                <li>Dokumen dikembalikan dalam kondisi baik</li>
                <li>Keterlambatan pengembalian akan dicatat</li>
            </ul>
        </div>
    </div>

    <div class="flex items-center justify-between">
        <a href="{{ route('loans.index') }}" class="text-sm text-slate-500 hover:text-slate-700">← Kembali</a>
        <button type="submit"
                class="px-5 py-2.5 bg-sky-600 text-white text-sm font-medium rounded-xl hover:bg-sky-700">
            <i class="ti ti-send mr-1.5"></i> Ajukan Peminjaman
        </button>
    </div>
</form>
</div>
@endsection
