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

    public function isPegawai(): bool
    {
        return $this->role === 'pegawai';
    }
}
