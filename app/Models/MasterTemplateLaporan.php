<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterTemplateLaporan extends Model
{
    use HasFactory;

    protected $fillable = [
        'divisi_id',
        'jabatan_id',
        'nama_field',
        'tipe_field',
        'satuan',
        'terhubung_ke_kpi_id',
    ];

    public function divisi()
    {
        return $this->belongsTo(Divisi::class);
    }

    public function jabatan()
    {
        return $this->belongsTo(Jabatan::class);
    }

    public function masterKpi()
    {
        return $this->belongsTo(MasterKpi::class, 'terhubung_ke_kpi_id');
    }

    public function laporanHarianDetails()
    {
        return $this->hasMany(LaporanHarianDetail::class);
    }

    public function options()
    {
        return $this->hasMany(MasterTemplateLaporanOption::class)->orderBy('urutan')->orderBy('id');
    }
}
