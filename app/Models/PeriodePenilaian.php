<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PeriodePenilaian extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama_periode',
        'tanggal_mulai',
        'tanggal_selesai',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_mulai' => 'date',
            'tanggal_selesai' => 'date',
        ];
    }

    public function penilaianKpis()
    {
        return $this->hasMany(PenilaianKpi::class, 'periode_id');
    }

    public function penilaianKompetensis()
    {
        return $this->hasMany(PenilaianKompetensi::class, 'periode_id');
    }

    public function presensiPotongans()
    {
        return $this->hasMany(PresensiPotongan::class, 'periode_id');
    }

    public function hasilPenilaians()
    {
        return $this->hasMany(HasilPenilaian::class, 'periode_id');
    }

    public function scopeAktif($query)
    {
        return $query->where('status', 'berjalan');
    }

    public static function finalizeExpired(): int
    {
        return static::query()
            ->where('status', 'berjalan')
            ->whereDate('tanggal_selesai', '<', now()->toDateString())
            ->update([
                'status' => 'final',
                'updated_at' => now(),
            ]);
    }
}
