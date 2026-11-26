<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JenisCuti extends Model
{
    use HasFactory;

    protected $table = 'jenis_cuti';

    protected $fillable = [
        'kode',
        'nama',
        'deskripsi',
        'kuota_hari',
        'periode_kuota',
        'minimal_hari_pengajuan',
        'maksimal_hari_pengajuan',
        'boleh_hari_h',
        'metode_hitung_hari',
        'minimal_masa_kerja_bulan',
        'bisa_akumulasi',
        'tambahan_akumulasi_hari',
        'maksimal_tahun_akumulasi',
        'tahun_awal_siklus',
        'wajib_lampiran',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'kuota_hari' => 'integer',
            'minimal_hari_pengajuan' => 'integer',
            'maksimal_hari_pengajuan' => 'integer',
            'boleh_hari_h' => 'boolean',
            'minimal_masa_kerja_bulan' => 'integer',
            'bisa_akumulasi' => 'boolean',
            'tambahan_akumulasi_hari' => 'integer',
            'maksimal_tahun_akumulasi' => 'integer',
            'tahun_awal_siklus' => 'integer',
            'wajib_lampiran' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function scopeAktif($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeTahunan($query)
    {
        return $query->where('periode_kuota', 'tahunan');
    }

    public function kuotaCutis()
    {
        return $this->hasMany(KuotaCuti::class, 'jenis_cuti_id');
    }

    public function pengajuanCutis()
    {
        return $this->hasMany(
            PengajuanCuti::class,
            'jenis_cuti_id'
        );
    }
}
