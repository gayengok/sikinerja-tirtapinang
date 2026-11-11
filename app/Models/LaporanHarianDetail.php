<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LaporanHarianDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'laporan_harian_id',
        'master_template_laporan_id',
        'nilai',
    ];

    public function laporanHarian()
    {
        return $this->belongsTo(LaporanHarian::class);
    }

    public function templateLaporan()
    {
        return $this->belongsTo(MasterTemplateLaporan::class, 'master_template_laporan_id');
    }

}
