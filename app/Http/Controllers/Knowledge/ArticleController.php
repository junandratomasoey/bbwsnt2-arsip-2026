<?php

namespace App\Http\Controllers\Knowledge;

use App\Http\Controllers\Controller;
use App\Models\KnowledgeArticle;
use App\Models\KnowledgeCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ArticleController extends Controller
{
    public function index(Request $request)
    {
        $query = KnowledgeArticle::with(['author','category'])
            ->when($request->search,      fn($q) => $q->search($request->search))
            ->when($request->tipe,        fn($q) => $q->tipe($request->tipe))
            ->when($request->category_id, fn($q) => $q->where('category_id', $request->category_id))
            ->when($request->status,      fn($q) => $q->where('status', $request->status))
            ->when(!auth()->user()->can('knowledge.edit'), fn($q) => $q->published())
            ->orderByDesc('published_at')->orderByDesc('created_at')
            ->paginate(20)->withQueryString();

        $categories = KnowledgeCategory::aktif()->orderBy('urutan')->get();
        $tipeList   = ['sop','wiki','lesson_learned','faq','best_practice','regulasi','panduan'];
        $featured   = KnowledgeArticle::published()->featured()->limit(3)->get();

        return view('knowledge.articles.index', compact('query','categories','tipeList','featured'));
    }

    public function create()
    {
        $categories = KnowledgeCategory::aktif()->orderBy('urutan')->get();
        $tipeList   = ['sop','wiki','lesson_learned','faq','best_practice','regulasi','panduan'];
        return view('knowledge.articles.form', compact('categories','tipeList'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'judul'       => 'required|string|max:255',
            'tipe'        => 'required|in:sop,wiki,lesson_learned,faq,best_practice,regulasi,panduan',
            'category_id' => 'nullable|exists:knowledge_categories,id',
            'ringkasan'   => 'nullable|string|max:500',
            'konten'      => 'required|string',
            'tags'        => 'nullable|string',
            'is_featured' => 'boolean',
            'status'      => 'required|in:draft,review',
        ]);

        $slug = Str::slug($validated['judul']);
        $slug = KnowledgeArticle::where('slug', $slug)->exists()
            ? $slug . '-' . now()->timestamp
            : $slug;

        $tags = !empty($validated['tags'])
            ? array_map('trim', explode(',', $validated['tags'])) : [];

        $article = KnowledgeArticle::create([
            ...$validated,
            'slug'        => $slug,
            'tags'        => $tags,
            'author_id'   => auth()->id(),
            'is_featured' => $request->boolean('is_featured'),
        ]);

        return redirect()->route('knowledge.show', $article->slug)
            ->with('success', "Artikel <strong>{$article->judul}</strong> berhasil disimpan.");
    }

    public function show(KnowledgeArticle $article)
    {
        if ($article->status !== 'published' && !auth()->user()->can('knowledge.edit')) {
            abort(404);
        }
        $article->incrementViews();
        $article->load(['author','reviewer','category','relations.relatedArticle']);
        $related = KnowledgeArticle::published()
            ->where('id','!=',$article->id)
            ->where('category_id', $article->category_id)
            ->limit(4)->get();
        return view('knowledge.articles.show', compact('article','related'));
    }

    public function edit(KnowledgeArticle $article)
    {
        $categories = KnowledgeCategory::aktif()->orderBy('urutan')->get();
        $tipeList   = ['sop','wiki','lesson_learned','faq','best_practice','regulasi','panduan'];
        return view('knowledge.articles.form', compact('article','categories','tipeList'));
    }

    public function update(Request $request, KnowledgeArticle $article)
    {
        $validated = $request->validate([
            'judul'       => 'required|string|max:255',
            'tipe'        => 'required|in:sop,wiki,lesson_learned,faq,best_practice,regulasi,panduan',
            'category_id' => 'nullable|exists:knowledge_categories,id',
            'ringkasan'   => 'nullable|string|max:500',
            'konten'      => 'required|string',
            'tags'        => 'nullable|string',
            'is_featured' => 'boolean',
        ]);

        $tags = !empty($validated['tags'])
            ? array_map('trim', explode(',', $validated['tags'])) : [];

        $article->update([...$validated, 'tags' => $tags, 'is_featured' => $request->boolean('is_featured')]);

        return redirect()->route('knowledge.show', $article->slug)
            ->with('success', 'Artikel berhasil diperbarui.');
    }

    public function destroy(KnowledgeArticle $article)
    {
        $article->delete();
        return redirect()->route('knowledge.index')->with('success', 'Artikel berhasil dihapus.');
    }

    public function publish(KnowledgeArticle $article)
    {
        $article->update([
            'status'       => 'published',
            'published_at' => now(),
            'reviewer_id'  => auth()->id(),
            'reviewed_at'  => now(),
        ]);
        return back()->with('success', "Artikel <strong>{$article->judul}</strong> berhasil dipublikasikan.");
    }

    public function helpful(KnowledgeArticle $article)
    {
        $article->increment('helpful_count');
        return response()->json(['helpful_count' => $article->helpful_count]);
    }
}
