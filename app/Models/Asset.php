<?php

namespace App\Models;

use App\Models\Concerns\{HasUuid, Auditable};
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Asset extends Model
{
    use HasUuid, Auditable, SoftDeletes;

    protected $fillable = [
        'asset_code', 'nama', 'deskripsi',
        'asset_type_id', 'unit_kerja_id',
        'provinsi', 'kabupaten', 'kecamatan', 'desa', 'das', 'wilayah_sungai',
        'lifecycle_status',
        'tahun_bangun', 'tahun_desain', 'umur_rencana_tahun',
        'atribut_teknis',
        'kondisi_terakhir', 'rci_score_terakhir', 'tgl_inspeksi_terakhir',
        'nilai_perolehan', 'nilai_buku', 'tahun_perolehan',
        'foto_utama_path', 'metadata', 'is_aktif',
        'created_by', 'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'atribut_teknis'        => 'array',
            'metadata'              => 'array',
            'is_aktif'              => 'boolean',
            'tgl_inspeksi_terakhir' => 'date',
            'nilai_perolehan'       => 'decimal:2',
            'nilai_buku'            => 'decimal:2',
            'rci_score_terakhir'    => 'decimal:2',
        ];
    }

    protected array $auditInclude = [
        'nama', 'lifecycle_status', 'kondisi_terakhir', 'unit_kerja_id',
    ];

    public function auditLabel(): string
    {
        return "[{$this->asset_code}] {$this->nama}";
    }

    // ── Relasi ───────────────────────────────────────────────────────
    public function assetType()
    {
        return $this->belongsTo(AssetType::class);
    }

    public function unitKerja()
    {
        return $this->belongsTo(UnitKerja::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function geometries()
    {
        return $this->hasMany(AssetGeometry::class);
    }

    public function geometriUtama()
    {
        return $this->hasOne(AssetGeometry::class)->where('is_primary', true);
    }

    public function conditions()
    {
        return $this->hasMany(AssetCondition::class)->orderByDesc('tgl_inspeksi');
    }

    public function kondisiTerbaru()
    {
        return $this->hasOne(AssetCondition::class)->latestOfMany('tgl_inspeksi');
    }

    public function components()
    {
        return $this->hasMany(AssetComponent::class);
    }

    public function projects()
    {
        return $this->hasMany(Project::class);
    }

    public function opRecords()
    {
        return $this->hasMany(OpRecord::class);
    }

    public function opSchedules()
    {
        return $this->hasMany(OpSchedule::class);
    }

    public function documents()
    {
        return $this->morphMany(Document::class, 'entity');
    }

    // ── Scope ────────────────────────────────────────────────────────
    public function scopeAktif($q)       { return $q->where('is_aktif', true); }
    public function scopeOperating($q)   { return $q->where('lifecycle_status', 'operating'); }
    public function scopeKondisiBuruk($q){ return $q->whereIn('kondisi_terakhir', ['C', 'D']); }

    public function scopeSearch($q, string $keyword)
    {
        return $q->whereRaw(
            "search_vector @@ plainto_tsquery('simple', ?)",
            [$keyword]
        )->orderByRaw(
            "ts_rank(search_vector, plainto_tsquery('simple', ?)) DESC",
            [$keyword]
        );
    }

    // ── Helper ───────────────────────────────────────────────────────
    public function umurTahun(): int
    {
        return $this->tahun_bangun
            ? (int) now()->format('Y') - $this->tahun_bangun
            : 0;
    }

    public function sisaUmurTahun(): int
    {
        $umurRencana = $this->umur_rencana_tahun ?? 50;
        return $umurRencana - $this->umurTahun();
    }

    public function labelLifecycle(): string
    {
        return match($this->lifecycle_status) {
            'planning'        => 'Perencanaan',
            'construction'    => 'Konstruksi',
            'commissioning'   => 'Uji Coba',
            'operating'       => 'Operasional',
            'rehabilitating'  => 'Rehabilitasi',
            'decommissioned'  => 'Nonaktif',
            default           => $this->lifecycle_status,
        };
    }

    public function badgeKondisi(): string
    {
        return match($this->kondisi_terakhir) {
            'A' => 'bg-green-100 text-green-800',
            'B' => 'bg-yellow-100 text-yellow-800',
            'C' => 'bg-orange-100 text-orange-800',
            'D' => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-600',
        };
    }

    public function labelKondisi(): string
    {
        return match($this->kondisi_terakhir) {
            'A' => 'Baik',
            'B' => 'Sedang',
            'C' => 'Rusak Ringan',
            'D' => 'Rusak Berat',
            default => 'Belum Dinilai',
        };
    }

    // Cek dokumen wajib yang belum ada berdasarkan asset_type checklist
    public function dokumenWajibBelumAda(string $fase = 'after'): array
    {
        $checklist = $this->assetType?->checklist_dokumen_wajib ?? [];
        $wajib     = $checklist[$fase] ?? [];
        if (empty($wajib)) return [];

        $sudahAda = $this->documents()
            ->whereIn('doc_type_code', $wajib)
            ->where('status', 'approved')
            ->pluck('doc_type_code')
            ->toArray();

        return array_values(array_diff($wajib, $sudahAda));
    }

    public function persentaseKelengkapanDokumen(string $fase = 'after'): float
    {
        $checklist = $this->assetType?->checklist_dokumen_wajib ?? [];
        $wajib     = count($checklist[$fase] ?? []);
        if ($wajib === 0) return 100.0;

        $belum = count($this->dokumenWajibBelumAda($fase));
        return round(($wajib - $belum) / $wajib * 100, 1);
    }

    // Status OP bulan ini
    public function statusOpBulanIni(): ?OpRecord
    {
        return $this->opRecords()
            ->where('periode_tahun', now()->year)
            ->where('periode_bulan', now()->month)
            ->first();
    }

    // Generate kode aset otomatis: BBW-BDG-001
    public static function generateKode(string $kategori): string
    {
        $prefix = 'BBW-' . strtoupper(substr($kategori, 0, 3));
        $last   = static::where('asset_code', 'like', $prefix . '-%')
                        ->orderByDesc('asset_code')
                        ->value('asset_code');
        $num    = $last ? (int) substr($last, -3) + 1 : 1;
        return $prefix . '-' . str_pad($num, 3, '0', STR_PAD_LEFT);
    }
}
