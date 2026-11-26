<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function pegawai()
    {
        return $this->hasOne(Pegawai::class);
    }

    public function pegawaiNotifications()
    {
        return $this->hasMany(PegawaiNotification::class);
    }

    public function isMaster(): bool
    {
        return $this->role === 'master';
    }

    public function isAtasan(): bool
    {
        return $this->role === 'atasan';
    }

    public function isKepalaBagian(): bool
    {
        return $this->role === 'kepala_bagian';
    }

    public function isDirektur(): bool
    {
        return $this->role === 'direktur';
    }

    public function isPegawai(): bool
    {
        return $this->role === 'pegawai';
    }

    public function mutasiKuotaCutis()
    {
        return $this->hasMany(
            MutasiKuotaCuti::class,
            'user_id'
        )->terbaru();
    }

    public function tandaTangans()
    {
        return $this->hasMany(
            TandaTanganPengguna::class,
            'user_id'
        )->terbaru();
    }

    public function tandaTanganAktif()
    {
        return $this->hasOne(
            TandaTanganPengguna::class,
            'user_id'
        )->aktif();
    }

    public function tandaTanganNonaktif()
    {
        return $this->tandaTangans()->nonaktif();
    }

    public function memilikiTandaTanganAktif(): bool
    {
        return $this->tandaTanganAktif()->exists();
    }

    public function templateSuratCutis()
    {
        return $this->hasMany(
            TemplateSuratCuti::class,
            'dibuat_oleh'
        )->terbaru();
    }

    public function suratPermohonanCutis()
    {
        return $this->hasMany(
            SuratPermohonanCuti::class,
            'dibuat_oleh'
        )->orderByDesc('id');
    }

    public function skCutisDiunggah()
    {
        return $this->hasMany(
            SkCuti::class,
            'diunggah_oleh'
        )->terbaru();
    }
}
