<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Absensi extends Model
{
    use HasFactory;

    protected $fillable = [
        'pegawai_id',
        'tanggal',
        'jenis_presensi',
        'jam_masuk',
        'jam_pulang',
        'status',
        'sumber',
        'keterangan',
        'foto_path',
        'latitude',
        'longitude',
        'jarak_lokasi',
        'di_luar_kantor',
        'akurasi_gps',
        'status_verifikasi',
        'catatan_verifikasi',
        'diverifikasi_oleh',
        'diverifikasi_pada',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'di_luar_kantor' => 'boolean',
            'diverifikasi_pada' => 'datetime',
        ];
    }

    public function pegawai()
    {
        return $this->belongsTo(Pegawai::class);
    }

    public function diverifikasiOleh()
    {
        return $this->belongsTo(Pegawai::class, 'diverifikasi_oleh');
    }
}
