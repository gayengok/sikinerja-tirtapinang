<?php

namespace App\Models;

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
    ];

    public function jabatan()
    {
        return $this->belongsTo(Jabatan::class);
    }
}
