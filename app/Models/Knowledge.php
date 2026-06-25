<?php namespace App\Models;
use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

// ============================================================
class KnowledgeCategory extends Model
{
    use HasUuid;
    protected $table = 'knowledge_categories';
    protected $fillable = [
        'parent_id','nama','slug','ikon','warna',
        'deskripsi','urutan','is_aktif',
    ];
    protected function casts(): array
    {
        return ['is_aktif' => 'boolean'];
    }
    public function parent()   { return $this->belongsTo(KnowledgeCategory::class, 'parent_id'); }
    public function children() { return $this->hasMany(KnowledgeCategory::class, 'parent_id')->orderBy('urutan'); }
    public function articles() { return $this->hasMany(KnowledgeArticle::class, 'category_id'); }
    public function scopeAktif($q) { return $q->where('is_aktif', true); }
}

// ============================================================
class KnowledgeArticle extends Model
{
    use HasUuid, SoftDeletes;
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
    protected function casts(): array
    {
        return [
            'published_at'  => 'datetime',
            'reviewed_at'   => 'datetime',
            'is_featured'   => 'boolean',
            'tags'          => 'array',
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

// ============================================================
class KnowledgeRelation extends Model
{
    use HasUuid;
    protected $table = 'knowledge_relations';
    protected $fillable = ['article_id','related_article_id','tipe_relasi'];
    public function article()        { return $this->belongsTo(KnowledgeArticle::class, 'article_id'); }
    public function relatedArticle() { return $this->belongsTo(KnowledgeArticle::class, 'related_article_id'); }
}

// ============================================================
class LibraryItem extends Model
{
    use HasUuid, SoftDeletes;
    protected $table = 'library_items';
    protected $fillable = [
        'kode_item','judul','judul_singkat','tipe',
        'penulis','penerbit','tahun_terbit','edisi',
        'isbn','no_seri','bahasa','ddc','subjek',
        'stok_fisik','stok_dipinjam','ada_digital','file_digital_path',
        'physical_location_id','tags','metadata','is_aktif',
    ];
    protected function casts(): array
    {
        return [
            'ada_digital' => 'boolean',
            'is_aktif'    => 'boolean',
            'tags'        => 'array',
            'metadata'    => 'array',
        ];
    }
    public function physicalLocation() { return $this->belongsTo(PhysicalLocation::class); }
    public function loans()            { return $this->hasMany(LibraryLoan::class); }
    public function scopeAktif($q)     { return $q->where('is_aktif', true); }
    public function scopeTersedia($q)  { return $q->whereRaw('stok_fisik > stok_dipinjam'); }
    public function stokTersedia(): int { return max(0, $this->stok_fisik - $this->stok_dipinjam); }

    public function scopeSearch($q, string $keyword)
    {
        return $q->whereRaw(
            "search_vector @@ plainto_tsquery('simple', ?)", [$keyword]
        )->orderByRaw(
            "ts_rank(search_vector, plainto_tsquery('simple', ?)) DESC", [$keyword]
        );
    }
}

// ============================================================
class LibraryLoan extends Model
{
    use HasUuid;
    protected $table = 'library_loans';
    protected $fillable = [
        'library_item_id','borrower_id','status',
        'tgl_pinjam_rencana','tgl_kembali_rencana',
        'tgl_diambil','tgl_dikembalikan',
        'keperluan','catatan','approved_by','approved_at',
    ];
    protected function casts(): array
    {
        return [
            'tgl_pinjam_rencana'  => 'date',
            'tgl_kembali_rencana' => 'date',
            'tgl_diambil'         => 'datetime',
            'tgl_dikembalikan'    => 'datetime',
            'approved_at'         => 'datetime',
        ];
    }
    public function libraryItem() { return $this->belongsTo(LibraryItem::class); }
    public function borrower()    { return $this->belongsTo(User::class, 'borrower_id'); }
    public function approvedBy()  { return $this->belongsTo(User::class, 'approved_by'); }
    public function isTerlambat(): bool
    {
        return $this->status === 'borrowed' && $this->tgl_kembali_rencana?->isPast();
    }
}
