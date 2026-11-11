<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pegawai extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'nama',
        'jabatan_id',
        'atasan_id',
        'tanggal_masuk',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_masuk' => 'date',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function jabatan()
    {
        return $this->belongsTo(Jabatan::class);
    }

    public function atasan()
    {
        return $this->belongsTo(Pegawai::class, 'atasan_id');
    }

    public function bawahans()
    {
        return $this->hasMany(Pegawai::class, 'atasan_id');
    }

    public function laporanHarians()
    {
        return $this->hasMany(LaporanHarian::class);
    }

    public function laporanDisetujui()
    {
        return $this->hasMany(LaporanHarian::class, 'disetujui_oleh');
    }

    public function absensis()
    {
        return $this->hasMany(Absensi::class);
    }

    public function penilaianKpis()
    {
        return $this->hasMany(PenilaianKpi::class);
    }

    public function penilaianKompetensis()
    {
        return $this->hasMany(PenilaianKompetensi::class);
    }

    public function presensiPotongans()
    {
        return $this->hasMany(PresensiPotongan::class);
    }

    public function hasilPenilaians()
    {
        return $this->hasMany(HasilPenilaian::class);
    }

    public function scopeAktif($query)
    {
        return $query->where('status', 'aktif');
    }
}
