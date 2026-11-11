<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterKompetensi extends Model
{
    use HasFactory;

    protected $fillable = [
        'jabatan_id',
        'nama_kompetensi',
        'definisi_perilaku',
        'urutan',
    ];

    public function jabatan()
    {
        return $this->belongsTo(Jabatan::class);
    }

    public function penilaianKompetensis()
    {
        return $this->hasMany(PenilaianKompetensi::class);
    }

    // Kompetensi berlaku untuk semua jabatan jika jabatan_id null
    public function scopeUntukJabatan($query, $jabatanId)
    {
        return $query->where(function ($q) use ($jabatanId) {
            $q->whereNull('jabatan_id')->orWhere('jabatan_id', $jabatanId);
        });
    }
}
