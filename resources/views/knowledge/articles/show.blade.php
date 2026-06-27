@extends('layouts.app')
@section('title', $article->judul)
@section('breadcrumb')
    <a href="{{ route('knowledge.index') }}" class="text-slate-500 hover:text-slate-700 text-sm">Knowledge</a>
    <i class="ti ti-chevron-right text-slate-300 text-xs"></i>
    <span class="text-slate-800 font-medium text-sm truncate">{{ $article->judul }}</span>
@endsection
@push('styles')
<style>
.prose h2{font-size:1.1rem;font-weight:600;margin-top:1.5rem;margin-bottom:.5rem;color:#1e293b}
.prose h3{font-size:1rem;font-weight:600;margin-top:1.25rem;color:#334155}
.prose p{margin-bottom:.75rem;color:#475569;font-size:.875rem;line-height:1.6}
.prose ul,.prose ol{margin-bottom:.75rem;padding-left:1.5rem;color:#475569;font-size:.875rem}
.prose li{margin-bottom:.25rem}
.prose code{background:#f1f5f9;padding:.1rem .3rem;border-radius:.25rem;font-size:.8rem}
.prose pre{background:#1e293b;color:#e2e8f0;padding:1rem;border-radius:.5rem;overflow-x:auto;margin-bottom:.75rem;font-size:.8rem}
.prose blockquote{border-left:3px solid #0ea5e9;padding-left:1rem;color:#64748b;margin:.75rem 0}
</style>
@endpush
@section('content')
<div class="max-w-3xl mx-auto">
    <div class="mb-5 flex flex-wrap gap-2 items-center">
        <span class="inline-flex px-2.5 py-1 rounded-lg text-xs font-medium {{ $article->badgeTipe() }}">{{ $article->labelTipe() }}</span>
        @if($article->category)
        <span class="text-xs text-slate-500">{{ $article->category->nama }}</span>
        @endif
        @if($article->is_featured)
        <span class="inline-flex items-center gap-1 text-xs text-amber-600"><i class="ti ti-star-filled"></i> Featured</span>
        @endif
    </div>
    <h1 class="text-2xl font-bold text-slate-800 mb-3">{{ $article->judul }}</h1>
    <div class="flex items-center gap-4 text-xs text-slate-400 mb-5 pb-5 border-b border-slate-200">
        <span><i class="ti ti-user"></i> {{ $article->author?->name }}</span>
        <span><i class="ti ti-calendar"></i> {{ $article->published_at?->format('d M Y') }}</span>
        <span><i class="ti ti-eye"></i> {{ number_format($article->views_count) }} views</span>
        @can('knowledge.edit')
        <a href="{{ route('knowledge.edit', $article->slug) }}" class="ml-auto text-sky-600 hover:underline">Edit →</a>
        @endcan
    </div>
    @if($article->ringkasan)
    <div class="bg-sky-50 border border-sky-100 rounded-xl p-4 mb-5 text-sm text-sky-700">
        {{ $article->ringkasan }}
    </div>
    @endif
    <div class="prose bg-white border border-slate-200 rounded-xl p-6 mb-5">
        {!! nl2br(e($article->konten)) !!}
    </div>
    @if($article->tags && count($article->tags) > 0)
    <div class="flex flex-wrap gap-2 mb-5">
        @foreach($article->tags as $tag)
        <span class="text-xs px-2.5 py-1 rounded-full bg-slate-100 text-slate-600"># {{ $tag }}</span>
        @endforeach
    </div>
    @endif
    {{-- Helpful --}}
    <div class="bg-white border border-slate-200 rounded-xl p-4 flex items-center justify-between mb-5">
        <p class="text-sm text-slate-600">Apakah artikel ini membantu?</p>
        <form action="{{ route('knowledge.helpful', $article) }}" method="POST">
            @csrf
            <button class="inline-flex items-center gap-2 px-4 py-2 border border-slate-200 rounded-lg text-sm text-slate-600 hover:bg-slate-50">
                <i class="ti ti-thumb-up"></i> Ya, membantu ({{ $article->helpful_count }})
            </button>
        </form>
    </div>
    {{-- Artikel terkait --}}
    @if($related->isNotEmpty())
    <div class="bg-white border border-slate-200 rounded-xl p-5">
        <h3 class="text-sm font-semibold text-slate-700 mb-3">Artikel Terkait</h3>
        <div class="space-y-2">
            @foreach($related as $rel)
            <a href="{{ route('knowledge.show', $rel->slug) }}" class="flex items-center gap-2 text-sm text-sky-600 hover:underline">
                <i class="ti ti-article text-slate-400"></i> {{ $rel->judul }}
            </a>
            @endforeach
        </div>
    </div>
    @endif
</div>
@endsection
