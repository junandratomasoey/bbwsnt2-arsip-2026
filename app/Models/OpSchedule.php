<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;

class OpSchedule extends Model
{
    use HasUuid;
    protected $table = 'op_schedules';
    protected $fillable = [
        'asset_id','unit_kerja_id','tahun',
        'anggaran_op_rutin','anggaran_op_berkala','kode_dipa',
        'rencana_kegiatan','status','dibuat_oleh',
    ];
    protected function casts(): array
    {
        return [
            'anggaran_op_rutin'    => 'decimal:2',
            'anggaran_op_berkala'  => 'decimal:2',
            'rencana_kegiatan'     => 'array',
        ];
    }
    public function asset()      { return $this->belongsTo(Asset::class); }
    public function unitKerja()  { return $this->belongsTo(UnitKerja::class); }
    public function dibuatOleh() { return $this->belongsTo(User::class, 'dibuat_oleh'); }
    public function opRecords()  { return $this->hasMany(OpRecord::class); }
}
