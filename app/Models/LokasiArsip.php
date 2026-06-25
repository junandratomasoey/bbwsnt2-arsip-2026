<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class LokasiArsip extends Model
{
    use SoftDeletes, LogsActivity;

    protected $table = 'lokasi_arsip';
    protected $fillable = ['gedung', 'lantai', 'lemari', 'rak', 'kode_lokasi', 'keterangan'];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logFillable()->logOnlyDirty();
    }

    public function dokumen()
    {
        return $this->hasMany(Dokumen::class);
    }

    public function labelLengkap(): string
    {
        $parts = array_filter([
            $this->gedung,
            $this->lantai  ? 'Lt. ' . $this->lantai  : null,
            $this->lemari  ? 'Lemari ' . $this->lemari : null,
            $this->rak     ? 'Rak ' . $this->rak      : null,
        ]);
        return implode(' › ', $parts);
    }
}
