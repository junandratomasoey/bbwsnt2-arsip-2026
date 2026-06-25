<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;

class DocumentFile extends Model
{
    use HasUuid;
    protected $table = 'document_files';
    protected $fillable = [
        'document_id','file_path','file_name','file_disk',
        'mime_type','file_size','file_hash',
        'is_primary','label','page_count','uploaded_by',
    ];
    protected function casts(): array
    {
        return ['is_primary' => 'boolean'];
    }
    public function document()   { return $this->belongsTo(Document::class); }
    public function uploadedBy() { return $this->belongsTo(User::class, 'uploaded_by'); }

    public function fileSizeLabel(): string
    {
        $kb = $this->file_size / 1024;
        if ($kb < 1024) return round($kb, 1) . ' KB';
        return round($kb / 1024, 1) . ' MB';
    }
}
