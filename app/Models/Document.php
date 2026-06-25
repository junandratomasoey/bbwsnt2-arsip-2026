<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;

class DocumentType extends Model
{
    use HasUuid;
    protected $table = 'document_types';
    protected $fillable = [
        'kode','nama','kategori','deskripsi',
        'retensi_aktif_tahun','retensi_inaktif_tahun','nasib_akhir',
        'wajib_pada_fase','is_aktif','urutan',
    ];
    protected function casts(): array
    {
        return [
            'wajib_pada_fase' => 'array',
            'is_aktif'        => 'boolean',
        ];
    }
    public function documents() { return $this->hasMany(Document::class); }
    public function scopeAktif($q) { return $q->where('is_aktif', true); }
}
