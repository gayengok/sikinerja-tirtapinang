<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterJamKerja extends Model
{
    use HasFactory;

    protected $fillable = [
        'jabatan_id',
        'hari',
        'jam_masuk',
        'jam_pulang',
        'toleransi_menit',
        'jam_buka_absen_masuk',
        'batas_absen_masuk',
        'jam_buka_absen_pulang',
        'jam_tutup_absen_pulang',
    ];

    public function jabatan()
    {
        return $this->belongsTo(Jabatan::class);
    }

    public function rentangAbsenMasuk(): array
    {
        return [
            Carbon::parse($this->jam_buka_absen_masuk),
            Carbon::parse($this->batas_absen_masuk),
        ];
    }

    public function rentangAbsenPulang(): array
    {
        return [
            Carbon::parse($this->jam_buka_absen_pulang),
            Carbon::parse($this->jam_tutup_absen_pulang),
        ];
    }
}
