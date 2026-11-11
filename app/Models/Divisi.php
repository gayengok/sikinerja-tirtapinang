<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Divisi extends Model
{
    use HasFactory;

    protected $fillable = ['nama_divisi'];

    public function jabatans()
    {
        return $this->hasMany(Jabatan::class);
    }

    public function templateLaporans()
    {
        return $this->hasMany(MasterTemplateLaporan::class);
    }
}
