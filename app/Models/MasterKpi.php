<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterKpi extends Model
{
    use HasFactory;

    protected $fillable = [
        'jabatan_id',
        'indikator',
        'target_default',
        'realisasi',
        'persen_capaian',
        'skor',
        'keterangan',
        'bobot',
        'arah_capaian',
        'urutan',
    ];

    public function jabatan()
    {
        return $this->belongsTo(Jabatan::class);
    }

    public function penilaianKpis()
    {
        return $this->hasMany(PenilaianKpi::class);
    }

    public function templateLaporans()
    {
        return $this->hasMany(MasterTemplateLaporan::class, 'terhubung_ke_kpi_id');
    }
}
