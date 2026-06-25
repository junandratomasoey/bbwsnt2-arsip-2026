<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;

class KnowledgeRelation extends Model
{
    use HasUuid;
    protected $table = 'knowledge_relations';
    protected $fillable = ['article_id','related_article_id','tipe_relasi'];
    public function article()        { return $this->belongsTo(KnowledgeArticle::class, 'article_id'); }
    public function relatedArticle() { return $this->belongsTo(KnowledgeArticle::class, 'related_article_id'); }
}
