<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use App\Models\Concerns\HasPostgresArrays;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;

class KnowledgeArticle extends Model
{
    use HasUuid, SoftDeletes, HasPostgresArrays;
    protected $table = 'knowledge_articles';
    protected $fillable = [
        'judul','slug','tipe','category_id',
        'konten','ringkasan',
        'entity_type','entity_id',
        'status','published_at','reviewed_at',
        'versi','parent_article_id',
        'views_count','helpful_count','is_featured',
        'tags','metadata',
        'author_id','reviewer_id',
    ];
    protected array $pgArrayColumns = ['tags'];

    protected function casts(): array
    {
        return [
            'published_at'  => 'datetime',
            'reviewed_at'   => 'datetime',
            'is_featured'   => 'boolean',
            'metadata'      => 'array',
        ];
    }
    public function category()      { return $this->belongsTo(KnowledgeCategory::class, 'category_id'); }
    public function author()        { return $this->belongsTo(User::class, 'author_id'); }
    public function reviewer()      { return $this->belongsTo(User::class, 'reviewer_id'); }
    public function parentArticle() { return $this->belongsTo(KnowledgeArticle::class, 'parent_article_id'); }
    public function versions()      { return $this->hasMany(KnowledgeArticle::class, 'parent_article_id'); }
    public function relations()     { return $this->hasMany(KnowledgeRelation::class, 'article_id'); }
    public function documents()     { return $this->morphMany(Document::class, 'entity'); }

    public function scopePublished($q) { return $q->where('status', 'published'); }
    public function scopeFeatured($q)  { return $q->where('is_featured', true); }
    public function scopeTipe($q, string $tipe) { return $q->where('tipe', $tipe); }

    public function scopeSearch($q, string $keyword)
    {
        return $q->whereRaw(
            "search_vector @@ plainto_tsquery('simple', ?)", [$keyword]
        )->orderByRaw(
            "ts_rank(search_vector, plainto_tsquery('simple', ?)) DESC", [$keyword]
        );
    }

    public function labelTipe(): string
    {
        return match($this->tipe) {
            'sop'           => 'SOP',
            'wiki'          => 'Wiki',
            'lesson_learned'=> 'Lesson Learned',
            'faq'           => 'FAQ',
            'best_practice' => 'Best Practice',
            'regulasi'      => 'Regulasi',
            'panduan'       => 'Panduan',
            default         => ucfirst($this->tipe),
        };
    }

    public function badgeTipe(): string
    {
        return match($this->tipe) {
            'sop'           => 'bg-blue-100 text-blue-800',
            'lesson_learned'=> 'bg-purple-100 text-purple-800',
            'faq'           => 'bg-teal-100 text-teal-800',
            'best_practice' => 'bg-green-100 text-green-800',
            'regulasi'      => 'bg-red-100 text-red-800',
            default         => 'bg-gray-100 text-gray-700',
        };
    }

    public function incrementViews(): void
    {
        $this->increment('views_count');
    }
}
