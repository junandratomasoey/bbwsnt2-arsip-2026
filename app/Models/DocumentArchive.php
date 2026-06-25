<?php namespace App\Models;
use App\Models\Concerns\{HasUuid, Auditable};
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

// ============================================================
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

// ============================================================
class PhysicalLocation extends Model
{
    use HasUuid;
    protected $table = 'physical_locations';
    protected $fillable = [
        'gedung','lantai','ruang','lemari','rak','laci',
        'kode_lokasi','qr_code_path',
        'kapasitas_item','terisi_item','keterangan','is_aktif',
    ];
    protected function casts(): array
    {
        return ['is_aktif' => 'boolean'];
    }
    public function documents() { return $this->hasMany(Document::class); }
    public function libraryItems() { return $this->hasMany(LibraryItem::class); }

    public function labelLengkap(): string
    {
        return implode(' › ', array_filter([
            $this->gedung,
            $this->lantai  ? 'Lt. ' . $this->lantai   : null,
            $this->ruang   ? 'Ruang ' . $this->ruang  : null,
            $this->lemari  ? 'Lmr. ' . $this->lemari  : null,
            $this->rak     ? 'Rak ' . $this->rak       : null,
            $this->laci    ? 'Laci ' . $this->laci     : null,
        ]));
    }

    public function sisaKapasitas(): ?int
    {
        if (!$this->kapasitas_item) return null;
        return $this->kapasitas_item - $this->terisi_item;
    }
}

// ============================================================
class Document extends Model
{
    use HasUuid, Auditable, SoftDeletes;

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

    protected function casts(): array
    {
        return [
            'tgl_dokumen'       => 'date',
            'tgl_diterima'      => 'date',
            'tgl_kedaluwarsa'   => 'date',
            'tgl_musnah_rencana'=> 'date',
            'approved_at'       => 'datetime',
            'ada_fisik'         => 'boolean',
            'ada_digital'       => 'boolean',
            'metadata'          => 'array',
            'tags'              => 'array',
        ];
    }

    protected array $auditInclude = [
        'judul','status','klasifikasi','versi_mayor','versi_minor',
    ];

    public function auditLabel(): string
    {
        return "[{$this->doc_number}] {$this->judul}";
    }

    // ── Relasi ───────────────────────────────────────────────────────
    public function documentType()
    {
        return $this->belongsTo(DocumentType::class);
    }

    public function unitKerja()
    {
        return $this->belongsTo(UnitKerja::class);
    }

    public function physicalLocation()
    {
        return $this->belongsTo(PhysicalLocation::class);
    }

    public function uploadedBy()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function parentDoc()
    {
        return $this->belongsTo(Document::class, 'parent_doc_id');
    }

    public function versions()
    {
        return $this->hasMany(Document::class, 'parent_doc_id');
    }

    public function files()
    {
        return $this->hasMany(DocumentFile::class)->orderByDesc('is_primary');
    }

    public function primaryFile()
    {
        return $this->hasOne(DocumentFile::class)->where('is_primary', true);
    }

    public function loans()
    {
        return $this->hasMany(Loan::class);
    }

    // Polymorphic entity (asset, project, op_record, knowledge_article)
    public function entity()
    {
        return $this->morphTo();
    }

    // ── Scope ────────────────────────────────────────────────────────
    public function scopeApproved($q)  { return $q->where('status', 'approved'); }
    public function scopeRahasia($q)   { return $q->where('klasifikasi', 'rahasia'); }
    public function scopePublik($q)    { return $q->where('klasifikasi', 'biasa'); }

    public function scopeAksesibel($q, User $user)
    {
        if ($user->isSuperAdmin() || $user->can('dokumen.view_rahasia')) {
            return $q;
        }
        if ($user->can('dokumen.view_terbatas')) {
            return $q->where('klasifikasi', '!=', 'rahasia');
        }
        return $q->where('klasifikasi', 'biasa');
    }

    public function scopeSearch($q, string $keyword)
    {
        return $q->whereRaw(
            "search_vector @@ plainto_tsquery('simple', ?)", [$keyword]
        )->orderByRaw(
            "ts_rank(search_vector, plainto_tsquery('simple', ?)) DESC", [$keyword]
        );
    }

    public function scopeKadaluwarsa($q)
    {
        return $q->whereNotNull('tgl_kedaluwarsa')
                 ->where('tgl_kedaluwarsa', '<', now());
    }

    // ── Helper ───────────────────────────────────────────────────────
    public function versiLabel(): string
    {
        return "v{$this->versi_mayor}.{$this->versi_minor}";
    }

    public function isKadaluwarsa(): bool
    {
        return $this->tgl_kedaluwarsa?->isPast() ?? false;
    }

    public function isSedangDipinjam(): bool
    {
        return $this->loans()
            ->whereIn('status', ['approved', 'borrowed'])
            ->where('jenis', 'fisik')
            ->exists();
    }

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
            'rahasia'   => 'bg-red-100 text-red-800',
            'terbatas'  => 'bg-yellow-100 text-yellow-800',
            default     => 'bg-green-100 text-green-800',
        };
    }

    public static function generateNomor(string $kategori, int $tahun): string
    {
        $prefix = 'BBW/' . strtoupper(substr($kategori, 0, 2)) . "/{$tahun}/";
        $last   = static::where('doc_number', 'like', $prefix . '%')
                        ->orderByDesc('doc_number')
                        ->value('doc_number');
        $num    = $last ? (int) substr($last, -3) + 1 : 1;
        return $prefix . str_pad($num, 3, '0', STR_PAD_LEFT);
    }
}

// ============================================================
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

// ============================================================
class Loan extends Model
{
    use HasUuid;
    protected $table = 'loans';
    protected $fillable = [
        'document_id','borrower_id','jenis','status',
        'tgl_pinjam_rencana','tgl_kembali_rencana',
        'tgl_diambil','tgl_dikembalikan',
        'keperluan','catatan_peminjam','catatan_petugas','alasan_ditolak',
        'approved_by','approved_at',
        'download_count','max_download',
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
    public function document()    { return $this->belongsTo(Document::class); }
    public function borrower()    { return $this->belongsTo(User::class, 'borrower_id'); }
    public function approvedBy()  { return $this->belongsTo(User::class, 'approved_by'); }

    public function scopeMenunggu($q)  { return $q->where('status', 'requested'); }
    public function scopeAktif($q)     { return $q->whereIn('status', ['requested','borrowed']); }

    public function isTerlambat(): bool
    {
        return $this->status === 'borrowed'
            && $this->tgl_kembali_rencana?->isPast();
    }

    public function labelStatus(): string
    {
        return match($this->status) {
            'requested' => 'Menunggu Persetujuan',
            'approved'  => 'Disetujui',
            'borrowed'  => 'Dipinjam',
            'returned'  => 'Dikembalikan',
            'rejected'  => 'Ditolak',
            'overdue'   => 'Terlambat',
            default     => $this->status,
        };
    }

    public function badgeStatus(): string
    {
        return match($this->status) {
            'requested' => 'bg-yellow-100 text-yellow-800',
            'approved'  => 'bg-blue-100 text-blue-800',
            'borrowed'  => 'bg-purple-100 text-purple-800',
            'returned'  => 'bg-green-100 text-green-800',
            'rejected'  => 'bg-red-100 text-red-800',
            'overdue'   => 'bg-orange-100 text-orange-800',
            default     => 'bg-gray-100 text-gray-600',
        };
    }
}
