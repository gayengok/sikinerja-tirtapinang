<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterJenisPotongan extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama_potongan',
        'tipe_pelanggaran',
        'menit_min',
        'menit_max',
        'keterangan',
        'persen_potongan',
    ];

    public function presensiPotongans()
    {
        return $this->hasMany(PresensiPotongan::class, 'jenis_potongan_id');
    }
}
