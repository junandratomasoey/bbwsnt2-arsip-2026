<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Infrastruktur extends Model
{
    use SoftDeletes, LogsActivity;

    protected $fillable = [
        'nama', 'kode', 'jenis', 'latitude', 'longitude',
        'desa', 'kecamatan', 'kabupaten', 'kondisi', 'tahun_bangun', 'keterangan',
    ];

    protected function casts(): array
    {
        return [
            'latitude'  => 'decimal:7',
            'longitude' => 'decimal:7',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logFillable()->logOnlyDirty();
    }

    public function pekerjaan()
    {
        return $this->hasMany(Pekerjaan::class);
    }

    public function labelJenis(): string
    {
        return match($this->jenis) {
            'bendung'         => 'Bendung',
            'embung'          => 'Embung',
            'waduk'           => 'Waduk',
            'saluran_irigasi' => 'Saluran Irigasi',
            'jaringan_air_baku' => 'Jaringan Air Baku',
            'drainase'        => 'Drainase',
            'tanggul'         => 'Tanggul',
            default           => 'Lainnya',
        };
    }
}
