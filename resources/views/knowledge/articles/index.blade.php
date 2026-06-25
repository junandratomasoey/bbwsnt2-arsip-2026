{{-- resources/views/knowledge/articles/index.blade.php --}}
@extends('layouts.app')
@section('title', 'Knowledge Base')

@section('breadcrumb')
    <i class="ti ti-brain text-slate-400"></i>
    <span class="text-slate-800 font-medium text-sm">Knowledge Base</span>
@endsection

@section('content')
<x-page-header title="Knowledge Base" desc="Panduan teknis, SOP, lesson learned, dan best practice BBWS NT II" icon="ti-brain">
    @can('knowledge.create')
    <a href="{{ route('knowledge.create') }}"
       class="inline-flex items-center gap-2 px-4 py-2 bg-sky-600 text-white text-sm font-medium rounded-xl hover:bg-sky-700">
        <i class="ti ti-plus"></i> Tulis Artikel
    </a>
    @endcan
</x-page-header>

{{-- Featured --}}
@if($featured->isNotEmpty())
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
    @foreach($featured as $art)
    <a href="{{ route('knowledge.show', $art->slug) }}"
       class="bg-gradient-to-br from-sky-50 to-blue-50 border border-sky-100 rounded-xl p-4
              hover:border-sky-200 hover:shadow-sm transition-all">
        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium {{ $art->badgeTipe() }} mb-2">
            {{ $art->labelTipe() }}
        </span>
        <p class="text-sm font-semibold text-slate-800 line-clamp-2">{{ $art->judul }}</p>
        <p class="text-xs text-slate-500 mt-1 line-clamp-2">{{ $art->ringkasan }}</p>
        <p class="text-xs text-slate-400 mt-2">{{ $art->author?->name }} · {{ $art->published_at?->diffForHumans() }}</p>
    </a>
    @endforeach
</div>
@endif

{{-- Filter --}}
<div class="bg-white border border-slate-200 rounded-xl p-4 mb-4">
    <form method="GET" class="flex flex-wrap gap-2">
        <input type="text" name="search" value="{{ request('search') }}"
               placeholder="Cari artikel, SOP, panduan..."
               class="flex-1 min-w-48 border border-slate-200 rounded-lg px-3 py-2 text-sm
                      focus:outline-none focus:ring-2 focus:ring-sky-500">
        <select name="tipe" class="border border-slate-200 rounded-lg px-3 py-2 text-sm
                                   focus:outline-none focus:ring-2 focus:ring-sky-500">
            <option value="">Semua tipe</option>
            @foreach(['sop'=>'SOP','wiki'=>'Wiki','lesson_learned'=>'Lesson Learned','faq'=>'FAQ','best_practice'=>'Best Practice','regulasi'=>'Regulasi','panduan'=>'Panduan'] as $v=>$l)
            <option value="{{ $v }}" @selected(request('tipe') === $v)>{{ $l }}</option>
            @endforeach
        </select>
        <select name="category_id" class="border border-slate-200 rounded-lg px-3 py-2 text-sm
                                          focus:outline-none focus:ring-2 focus:ring-sky-500">
            <option value="">Semua kategori</option>
            @foreach($categories as $cat)
            <option value="{{ $cat->id }}" @selected(request('category_id') === $cat->id)>{{ $cat->nama }}</option>
            @endforeach
        </select>
        <button type="submit" class="px-4 py-2 bg-slate-800 text-white text-sm rounded-lg hover:bg-slate-700">
            <i class="ti ti-search"></i>
        </button>
        @if(request()->hasAny(['search','tipe','category_id']))
        <a href="{{ route('knowledge.index') }}" class="px-4 py-2 text-sm text-slate-500 border border-slate-200 rounded-lg">Reset</a>
        @endif
    </form>
</div>

{{-- Daftar artikel --}}
<div class="space-y-3">
    @forelse($query as $art)
    <div class="bg-white border border-slate-200 rounded-xl p-4 hover:border-slate-300 transition-colors">
        <div class="flex items-start gap-4">
            <div class="flex-1 min-w-0">
                <div class="flex flex-wrap items-center gap-2 mb-1">
                    <span class="inline-flex px-2 py-0.5 rounded-md text-xs font-medium {{ $art->badgeTipe() }}">
                        {{ $art->labelTipe() }}
                    </span>
                    @if($art->category)
                    <span class="text-xs text-slate-400">{{ $art->category->nama }}</span>
                    @endif
                    @if($art->status !== 'published')
                    <span class="text-xs px-2 py-0.5 rounded bg-amber-100 text-amber-700">
                        {{ ucfirst($art->status) }}
                    </span>
                    @endif
                </div>
                <a href="{{ route('knowledge.show', $art->slug) }}"
                   class="text-sm font-semibold text-slate-800 hover:text-sky-600">
                    {{ $art->judul }}
                </a>
                @if($art->ringkasan)
                <p class="text-xs text-slate-500 mt-1 line-clamp-2">{{ $art->ringkasan }}</p>
                @endif
                <div class="flex items-center gap-3 mt-2 text-xs text-slate-400">
                    <span>{{ $art->author?->name }}</span>
                    <span>{{ $art->published_at?->format('d M Y') ?? $art->created_at->format('d M Y') }}</span>
                    <span><i class="ti ti-eye"></i> {{ $art->views_count }}</span>
                    <span><i class="ti ti-thumb-up"></i> {{ $art->helpful_count }}</span>
                </div>
            </div>
            <div class="flex items-center gap-1 flex-shrink-0">
                <a href="{{ route('knowledge.show', $art->slug) }}"
                   class="p-1.5 text-slate-400 hover:text-sky-600 hover:bg-sky-50 rounded-lg">
                    <i class="ti ti-eye text-sm"></i>
                </a>
                @can('knowledge.edit')
                <a href="{{ route('knowledge.edit', $art->slug) }}"
                   class="p-1.5 text-slate-400 hover:text-amber-600 hover:bg-amber-50 rounded-lg">
                    <i class="ti ti-edit text-sm"></i>
                </a>
                @endcan
                @can('knowledge.publish')
                @if($art->status !== 'published')
                <form action="{{ route('knowledge.publish', $art) }}" method="POST">
                    @csrf
                    <button class="p-1.5 text-slate-400 hover:text-emerald-600 hover:bg-emerald-50 rounded-lg" title="Publish">
                        <i class="ti ti-send text-sm"></i>
                    </button>
                </form>
                @endif
                @endcan
            </div>
        </div>
    </div>
    @empty
    <div class="bg-white border border-slate-200 rounded-xl py-12 text-center">
        <i class="ti ti-brain text-4xl text-slate-200 block mb-3"></i>
        <p class="text-slate-400">Belum ada artikel knowledge</p>
    </div>
    @endforelse
</div>

@if($query->hasPages())
<div class="mt-4">{{ $query->links() }}</div>
@endif
@endsection
