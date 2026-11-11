<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PenilaianKompetensi extends Model
{
    use HasFactory;

    protected $fillable = [
        'pegawai_id',
        'periode_id',
        'master_kompetensi_id',
        'skor',
        'catatan',
    ];

    public function pegawai()
    {
        return $this->belongsTo(Pegawai::class);
    }

    public function periode()
    {
        return $this->belongsTo(PeriodePenilaian::class, 'periode_id');
    }

    public function masterKompetensi()
    {
        return $this->belongsTo(MasterKompetensi::class);
    }
}
