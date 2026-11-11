<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PresensiPotongan extends Model
{
    use HasFactory;

    protected $fillable = [
        'pegawai_id',
        'periode_id',
        'jenis_potongan_id',
        'jumlah_kejadian',
        'persen_potongan',
    ];

    public function pegawai()
    {
        return $this->belongsTo(Pegawai::class);
    }

    public function periode()
    {
        return $this->belongsTo(PeriodePenilaian::class, 'periode_id');
    }

    public function jenisPotongan()
    {
        return $this->belongsTo(MasterJenisPotongan::class, 'jenis_potongan_id');
    }
}
