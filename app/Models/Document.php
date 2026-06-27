<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use App\Models\Concerns\Auditable;
use App\Models\Concerns\HasPostgresArrays;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Document extends Model
{
    use HasUuid, Auditable, SoftDeletes, HasPostgresArrays;

    protected $fillable = [
        'doc_number','judul','deskripsi',
        'entity_type','entity_id','entity_fase',
        'document_type_id','unit_kerja_id',
        'klasifikasi',
        'versi_mayor','versi_minor','parent_doc_id',
        'status',
        'tgl_dokumen','tgl_diterima','tgl_kedaluwarsa',
        'retensi_aktif_tahun','retensi_inaktif_tahun',
        'nasib_akhir','tgl_musnah_rencana',
        'physical_location_id','ada_fisik','ada_digital',
        'qr_code','qr_code_path',
        'tags','metadata',
        'download_count','view_count',
        'uploaded_by','approved_by','approved_at',
    ];

    // Kolom PostgreSQL text[] — dihandle oleh HasPostgresArrays trait
    protected array $pgArrayColumns = ['tags'];

    protected function casts(): array
    {
        return [
            'tgl_dokumen'        => 'date',
            'tgl_diterima'       => 'date',
            'tgl_kedaluwarsa'    => 'date',
            'tgl_musnah_rencana' => 'date',
            'approved_at'        => 'datetime',
            'ada_fisik'          => 'boolean',
            'ada_digital'        => 'boolean',
            'metadata'           => 'array',  // jsonb — aman pakai cast array
            // 'tags' TIDAK dicast di sini, dihandle trait
        ];
    }

    protected array $auditInclude = ['judul','status','klasifikasi','versi_mayor','versi_minor'];
    public function auditLabel(): string { return "[{$this->doc_number}] {$this->judul}"; }

    // ── Relasi ───────────────────────────────────────────────────────
    public function documentType()     { return $this->belongsTo(DocumentType::class); }
    public function unitKerja()        { return $this->belongsTo(UnitKerja::class); }
    public function physicalLocation() { return $this->belongsTo(PhysicalLocation::class); }
    public function uploadedBy()       { return $this->belongsTo(User::class, 'uploaded_by'); }
    public function approvedBy()       { return $this->belongsTo(User::class, 'approved_by'); }
    public function parentDoc()        { return $this->belongsTo(Document::class, 'parent_doc_id'); }
    public function versions()         { return $this->hasMany(Document::class, 'parent_doc_id'); }
    public function files()            { return $this->hasMany(DocumentFile::class)->orderByDesc('is_primary'); }
    public function primaryFile()      { return $this->hasOne(DocumentFile::class)->where('is_primary', true); }
    public function loans()            { return $this->hasMany(Loan::class); }
    public function entity()           { return $this->morphTo(); }

    // ── Scopes ───────────────────────────────────────────────────────
    public function scopeApproved($q)  { return $q->where('status', 'approved'); }

    public function scopeKadaluwarsa($q)
    {
        return $q->whereNotNull('tgl_kedaluwarsa')->where('tgl_kedaluwarsa', '<', now());
    }

    public function scopeAksesibel($q, $user)
    {
        if (!$user) return $q->where('klasifikasi', 'biasa');
        if (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) return $q;
        if ($user->can('document.view_rahasia')) return $q;
        if ($user->can('document.view_terbatas')) return $q->where('klasifikasi', '!=', 'rahasia');
        return $q->where('klasifikasi', 'biasa');
    }

    public function scopeSearch($q, string $keyword)
    {
        return $q->whereRaw("search_vector @@ plainto_tsquery('simple', ?)", [$keyword])
                 ->orderByRaw("ts_rank(search_vector, plainto_tsquery('simple', ?)) DESC", [$keyword]);
    }

    // ── Helpers ──────────────────────────────────────────────────────
    public function versiLabel(): string { return "v{$this->versi_mayor}.{$this->versi_minor}"; }
    public function isKadaluwarsa(): bool { return $this->tgl_kedaluwarsa?->isPast() ?? false; }

    public function badgeStatus(): string
    {
        return match($this->status) {
            'draft'      => 'bg-gray-100 text-gray-700',
            'review'     => 'bg-yellow-100 text-yellow-800',
            'approved'   => 'bg-green-100 text-green-800',
            'superseded' => 'bg-orange-100 text-orange-700',
            'archived'   => 'bg-slate-100 text-slate-600',
            default      => 'bg-gray-100 text-gray-600',
        };
    }

    public function badgeKlasifikasi(): string
    {
        return match($this->klasifikasi) {
            'rahasia'  => 'bg-red-100 text-red-800',
            'terbatas' => 'bg-yellow-100 text-yellow-800',
            default    => 'bg-green-100 text-green-800',
        };
    }

    public static function generateNomor(string $kode, int $tahun): string
    {
        $prefix = 'BBW/' . strtoupper(substr($kode, 0, 3)) . "/{$tahun}/";
        $last   = static::where('doc_number', 'like', $prefix . '%')
                        ->orderByDesc('doc_number')->value('doc_number');
        $num    = $last ? (int) substr($last, -3) + 1 : 1;
        return $prefix . str_pad($num, 3, '0', STR_PAD_LEFT);
    }
}
