<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LaporanHarian extends Model
{
    use HasFactory;

    protected $fillable = [
        'pegawai_id',
        'tanggal',
        'catatan',
        'bukti_gambar',
        'status',
        'disetujui_oleh',
        'disetujui_at',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'disetujui_at' => 'datetime',
        ];
    }

    public function pegawai()
    {
        return $this->belongsTo(Pegawai::class);
    }

    public function penyetuju()
    {
        return $this->belongsTo(Pegawai::class, 'disetujui_oleh');
    }

    public function details()
    {
        return $this->hasMany(LaporanHarianDetail::class);
    }
}
