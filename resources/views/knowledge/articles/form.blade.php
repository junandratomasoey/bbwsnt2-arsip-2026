@extends('layouts.app')
@section('title', isset($article) ? 'Edit Artikel' : 'Tulis Artikel')
@section('breadcrumb')
    <a href="{{ route('knowledge.index') }}" class="text-slate-500 hover:text-slate-700 text-sm">Knowledge</a>
    <i class="ti ti-chevron-right text-slate-300 text-xs"></i>
    <span class="text-slate-800 font-medium text-sm">{{ isset($article) ? 'Edit' : 'Tulis' }}</span>
@endsection
@section('content')
@php $isEdit  = isset($article);
    $article = $article ?? null; @endphp
<div class="max-w-3xl">
<x-page-header :title="$isEdit ? 'Edit: ' . Str::limit($article->judul, 40) : 'Tulis Artikel Knowledge'" icon="ti-brain" />
<form method="POST"
      action="{{ $isEdit ? route('knowledge.update', $article) : route('knowledge.store') }}"
      class="space-y-5">
    @csrf @if($isEdit) @method('PUT') @endif
    <div class="bg-white border border-slate-200 rounded-xl p-5 space-y-4">
        <div><label class="block text-xs font-medium text-slate-600 mb-1">Judul <span class="text-red-500">*</span></label>
            <input type="text" name="judul" required value="{{ old('judul', $article->judul ?? '') }}"
                   class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500">
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div><label class="block text-xs font-medium text-slate-600 mb-1">Tipe <span class="text-red-500">*</span></label>
                <select name="tipe" required class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500">
                    @foreach(['sop'=>'SOP','wiki'=>'Wiki','lesson_learned'=>'Lesson Learned','faq'=>'FAQ','best_practice'=>'Best Practice','regulasi'=>'Regulasi','panduan'=>'Panduan'] as $v=>$l)
                    <option value="{{ $v }}" @selected(old('tipe', $article->tipe ?? '') === $v)>{{ $l }}</option>
                    @endforeach
                </select>
            </div>
            <div><label class="block text-xs font-medium text-slate-600 mb-1">Kategori</label>
                <select name="category_id" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500">
                    <option value="">Tanpa kategori</option>
                    @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" @selected(old('category_id', $article->category_id ?? '') === $cat->id)>{{ $cat->nama }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div><label class="block text-xs font-medium text-slate-600 mb-1">Ringkasan (maks 500 karakter)</label>
            <textarea name="ringkasan" rows="2" maxlength="500"
                      class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm resize-none focus:outline-none focus:ring-2 focus:ring-sky-500">{{ old('ringkasan', $article->ringkasan ?? '') }}</textarea>
        </div>
        <div><label class="block text-xs font-medium text-slate-600 mb-1">Konten <span class="text-red-500">*</span></label>
            <p class="text-xs text-slate-400 mb-1">Mendukung format teks biasa. Gunakan baris baru untuk paragraf.</p>
            <textarea name="konten" required rows="15"
                      class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm font-mono resize-y focus:outline-none focus:ring-2 focus:ring-sky-500">{{ old('konten', $article->konten ?? '') }}</textarea>
        </div>
        <div><label class="block text-xs font-medium text-slate-600 mb-1">Tags</label>
            <input type="text" name="tags" placeholder="irigasi, bendung, OP — pisahkan dengan koma"
                   value="{{ old('tags', $isEdit && $article->tags ? implode(', ', $article->tags) : '') }}"
                   class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500">
        </div>
        <div class="flex items-center gap-4">
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" name="is_featured" value="1"
                       @checked(old('is_featured', $article->is_featured ?? false))
                       class="rounded border-slate-300 text-sky-600">
                <span class="text-sm text-slate-700">Tandai sebagai Featured</span>
            </label>
        </div>
        <div><label class="block text-xs font-medium text-slate-600 mb-1">Status</label>
            <select name="status" class="border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500">
                <option value="draft" @selected(old('status', $article->status ?? 'draft') === 'draft')>Draft</option>
                <option value="review" @selected(old('status', $article->status ?? '') === 'review')>Ajukan Review</option>
            </select>
        </div>
    </div>
    <div class="flex items-center justify-between">
        <a href="{{ route('knowledge.index') }}" class="text-sm text-slate-500 hover:text-slate-700">← Kembali</a>
        <button type="submit" class="px-5 py-2.5 bg-sky-600 text-white text-sm font-medium rounded-xl hover:bg-sky-700">
            {{ $isEdit ? 'Simpan Perubahan' : 'Simpan Artikel' }}
        </button>
    </div>
</form>
</div>
@endsection
