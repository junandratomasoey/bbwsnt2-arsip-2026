<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;

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
