<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PenilaianKpi extends Model
{
    use HasFactory;

    protected $fillable = [
        'pegawai_id',
        'periode_id',
        'master_kpi_id',
        'target',
        'realisasi_otomatis',
        'realisasi_final',
        'persen_capaian',
        'skor',
        'keterangan',
    ];

    public function pegawai()
    {
        return $this->belongsTo(Pegawai::class);
    }

    public function periode()
    {
        return $this->belongsTo(PeriodePenilaian::class, 'periode_id');
    }

    public function masterKpi()
    {
        return $this->belongsTo(MasterKpi::class);
    }
}
