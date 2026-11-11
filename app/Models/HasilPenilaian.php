<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HasilPenilaian extends Model
{
    use HasFactory;

    protected $fillable = [
        'pegawai_id',
        'periode_id',
        'rata_skor_kpi',
        'rata_skor_kompetensi',
        'total_skor_a',
        'total_skor_b',
        'nilai_akhir',
        'predikat',
        'total_potongan_presensi',
        'tukin_bruto',
        'tukin_neto',
        'status',
        'dikirim_at',
    ];

    protected function casts(): array
    {
        return [
            'dikirim_at' => 'datetime',
        ];
    }

    public function pegawai()
    {
        return $this->belongsTo(Pegawai::class);
    }

    public function periode()
    {
        return $this->belongsTo(PeriodePenilaian::class, 'periode_id');
    }
}
