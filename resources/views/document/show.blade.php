{{-- resources/views/document/show.blade.php --}}
@extends('layouts.app')
@section('title', $document->judul)

@section('breadcrumb')
    <a href="{{ route('documents.index') }}" class="text-slate-500 hover:text-slate-700 text-sm">Dokumen</a>
    <i class="ti ti-chevron-right text-slate-300 text-xs"></i>
    <span class="text-slate-800 font-medium text-sm truncate">{{ $document->judul }}</span>
@endsection

@section('content')
<div class="flex flex-col lg:flex-row gap-6">
    <div class="flex-1 space-y-5">

        {{-- Header --}}
        <div class="bg-white border border-slate-200 rounded-xl p-5">
            <div class="flex flex-wrap gap-2 mb-2">
                <span class="font-mono text-xs text-slate-400 bg-slate-100 px-2 py-0.5 rounded">
                    {{ $document->doc_number ?? '—' }}
                </span>
                <span class="text-xs px-2 py-0.5 rounded {{ $document->badgeStatus() }}">{{ ucfirst($document->status) }}</span>
                <span class="text-xs px-2 py-0.5 rounded {{ $document->badgeKlasifikasi() }}">{{ ucfirst($document->klasifikasi) }}</span>
                <span class="text-xs px-2 py-0.5 rounded bg-slate-100 text-slate-600">{{ $document->versiLabel() }}</span>
            </div>
            <h1 class="text-xl font-semibold text-slate-800">{{ $document->judul }}</h1>
            <p class="text-sm text-slate-500 mt-1">
                {{ $document->documentType?->nama }} ·
                {{ $document->uploadedBy?->name }} ·
                {{ $document->tgl_dokumen?->format('d M Y') ?? $document->created_at->format('d M Y') }}
            </p>
            @if($document->deskripsi)
            <p class="text-sm text-slate-600 mt-2">{{ $document->deskripsi }}</p>
            @endif

            <div class="flex flex-wrap gap-2 mt-4">
                @can('document.download')
                @if($document->ada_digital)
                <a href="{{ route('documents.download', $document) }}"
                   class="inline-flex items-center gap-1.5 px-4 py-2 bg-sky-600 text-white text-sm font-medium rounded-xl hover:bg-sky-700">
                    <i class="ti ti-download"></i> Download
                </a>
                @endif
                @endcan
                @can('document.approve')
                @if($document->status === 'review')
                <form action="{{ route('documents.approve', $document) }}" method="POST">
                    @csrf
                    <button class="inline-flex items-center gap-1.5 px-4 py-2 bg-emerald-600 text-white text-sm rounded-xl hover:bg-emerald-700">
                        <i class="ti ti-check"></i> Approve
                    </button>
                </form>
                @endif
                @endcan
                @can('document.edit')
                <a href="{{ route('documents.edit', $document) }}"
                   class="inline-flex items-center gap-1.5 px-4 py-2 bg-white border border-slate-200 text-sm text-slate-700 rounded-xl hover:bg-slate-50">
                    <i class="ti ti-edit text-slate-400"></i> Edit
                </a>
                <a href="{{ route('documents.new-version', $document) }}"
                   class="inline-flex items-center gap-1.5 px-4 py-2 bg-white border border-slate-200 text-sm text-slate-700 rounded-xl hover:bg-slate-50"
                   onclick="return confirm('Upload versi baru akan mengubah status dokumen ini menjadi Superseded.')">
                    <i class="ti ti-file-plus text-slate-400"></i> Versi Baru
                </a>
                @endcan
            </div>
        </div>

        {{-- Detail --}}
        <div class="bg-white border border-slate-200 rounded-xl p-5">
            <h3 class="text-sm font-semibold text-slate-700 mb-4 pb-2 border-b border-slate-100">Detail Dokumen</h3>
            <div class="grid grid-cols-2 gap-4">
                <div><p class="text-xs text-slate-400 mb-0.5">Jenis Dokumen</p>
                    <p class="text-sm text-slate-700">{{ $document->documentType?->nama ?? '—' }}</p>
                </div>
                <div><p class="text-xs text-slate-400 mb-0.5">Unit Kerja</p>
                    <p class="text-sm text-slate-700">{{ $document->unitKerja?->singkatan ?? '—' }}</p>
                </div>
                <div><p class="text-xs text-slate-400 mb-0.5">Fase</p>
                    <p class="text-sm text-slate-700">{{ ucfirst($document->entity_fase ?? 'umum') }}</p>
                </div>
                <div><p class="text-xs text-slate-400 mb-0.5">Tanggal Dokumen</p>
                    <p class="text-sm text-slate-700">{{ $document->tgl_dokumen?->format('d M Y') ?? '—' }}</p>
                </div>
                <div><p class="text-xs text-slate-400 mb-0.5">Kadaluwarsa</p>
                    <p class="text-sm {{ $document->isKadaluwarsa() ? 'text-red-600 font-semibold' : 'text-slate-700' }}">
                        {{ $document->tgl_kedaluwarsa?->format('d M Y') ?? '—' }}
                    </p>
                </div>
                <div><p class="text-xs text-slate-400 mb-0.5">Approved oleh</p>
                    <p class="text-sm text-slate-700">{{ $document->approvedBy?->name ?? '—' }}</p>
                </div>
                <div><p class="text-xs text-slate-400 mb-0.5">Ada Fisik</p>
                    <p class="text-sm text-slate-700">{{ $document->ada_fisik ? 'Ya' : 'Tidak' }}</p>
                </div>
                <div><p class="text-xs text-slate-400 mb-0.5">Lokasi Fisik</p>
                    <p class="text-sm text-slate-700">{{ $document->physicalLocation?->kode_lokasi ?? '—' }}</p>
                </div>
            </div>
            @if($document->tags && count($document->tags) > 0)
            <div class="mt-4 pt-4 border-t border-slate-100">
                <p class="text-xs text-slate-400 mb-2">Tags</p>
                <div class="flex flex-wrap gap-1.5">
                    @foreach($document->tags as $tag)
                    <span class="text-xs px-2 py-0.5 rounded-full bg-sky-50 text-sky-700 border border-sky-100">
                        {{ $tag }}
                    </span>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        {{-- File --}}
        @if($document->files->isNotEmpty())
        <div class="bg-white border border-slate-200 rounded-xl overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100">
                <h3 class="text-sm font-semibold text-slate-700">File ({{ $document->files->count() }})</h3>
            </div>
            <div class="divide-y divide-slate-100">
                @foreach($document->files as $file)
                <div class="px-5 py-3 flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3 min-w-0">
                        <i class="ti {{ str_contains($file->mime_type,'pdf') ? 'ti-file-type-pdf text-red-500' : 'ti-file text-slate-400' }} text-lg flex-shrink-0"></i>
                        <div class="min-w-0">
                            <p class="text-sm text-slate-700 truncate">{{ $file->file_name }}</p>
                            <p class="text-xs text-slate-400">{{ $file->fileSizeLabel() }} · {{ $file->mime_type }}</p>
                        </div>
                    </div>
                    @can('document.download')
                    <a href="{{ route('documents.download', $document) }}"
                       class="flex-shrink-0 p-1.5 text-slate-400 hover:text-sky-600 hover:bg-sky-50 rounded-lg">
                        <i class="ti ti-download text-sm"></i>
                    </a>
                    @endcan
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Riwayat versi --}}
        @if($riwayatVersi->isNotEmpty())
        <div class="bg-white border border-slate-200 rounded-xl p-5">
            <h3 class="text-sm font-semibold text-slate-700 mb-3">Riwayat Versi</h3>
            <div class="space-y-2">
                @foreach($riwayatVersi as $ver)
                <div class="flex items-center gap-3">
                    <span class="text-xs font-mono bg-slate-100 px-2 py-0.5 rounded text-slate-600">{{ $ver->versiLabel() }}</span>
                    <a href="{{ route('documents.show', $ver) }}" class="text-xs text-sky-600 hover:underline">{{ $ver->judul }}</a>
                    <span class="text-xs text-slate-400 ml-auto">{{ $ver->created_at->format('d M Y') }}</span>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    {{-- Sidebar: QR & stats --}}
    <div class="lg:w-64 space-y-5 flex-shrink-0">
        @if($document->qr_code_path)
        <div class="bg-white border border-slate-200 rounded-xl p-5 text-center">
            <p class="text-xs text-slate-500 mb-3">QR Code Dokumen</p>
            <img src="{{ Storage::url($document->qr_code_path) }}"
                 class="w-40 h-40 mx-auto" alt="QR Code">
            <p class="text-xs font-mono text-slate-400 mt-2">{{ $document->qr_code }}</p>
        </div>
        @endif
        <div class="bg-white border border-slate-200 rounded-xl p-5">
            <h3 class="text-sm font-semibold text-slate-700 mb-3">Statistik</h3>
            <div class="space-y-2">
                <div class="flex justify-between text-sm">
                    <span class="text-slate-500">Dilihat</span>
                    <span class="font-medium text-slate-700">{{ number_format($document->view_count) }}x</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-slate-500">Diunduh</span>
                    <span class="font-medium text-slate-700">{{ number_format($document->download_count) }}x</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-slate-500">Peminjaman</span>
                    <span class="font-medium text-slate-700">{{ $document->loans->count() }}x</span>
                </div>
            </div>
            @can('loan.create')
            <a href="{{ route('loans.create', ['document_id' => $document->id]) }}"
               class="mt-4 w-full inline-flex items-center justify-center gap-2 px-3 py-2
                      border border-slate-200 text-sm text-slate-600 rounded-lg hover:bg-slate-50">
                <i class="ti ti-book-download"></i> Pinjam Dokumen
            </a>
            @endcan
        </div>
    </div>
</div>
@endsection
