<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Jabatan extends Model
{
    use HasFactory;

    protected $fillable = [
        'divisi_id',
        'nama_jabatan',
        'grade',
        'base_tukin',
    ];

    protected function casts(): array
    {
        return [
            'base_tukin' => 'decimal:2',
        ];
    }

    public function divisi()
    {
        return $this->belongsTo(Divisi::class);
    }

    public function pegawais()
    {
        return $this->hasMany(Pegawai::class);
    }

    public function masterKpis()
    {
        return $this->hasMany(MasterKpi::class);
    }

    public function masterKompetensis()
    {
        return $this->hasMany(MasterKompetensi::class);
    }

    public function jamKerjas()
    {
        return $this->hasMany(MasterJamKerja::class);
    }
}
