<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $password = Hash::make('password');

        User::updateOrCreate(
            ['email' => 'admin@sikinerja.test'],
            [
                'name' => 'Administrator SIKINERJA',
                'password' => $password,
                'role' => 'master',
            ]
        );

        $atasan = [
            ['name' => 'Kepala Atasan Hubungan Langganan', 'email' => 'atasan@sikinerja.test'],
            ['name' => 'Kepala Atasan Teknik', 'email' => 'atasan.teknik@sikinerja.test'],
            ['name' => 'Kepala Atasan Produksi', 'email' => 'atasan.produksi@sikinerja.test'],
        ];

        foreach ($atasan as $user) {
            User::updateOrCreate(
                ['email' => $user['email']],
                [
                    'name' => $user['name'],
                    'password' => $password,
                    'role' => 'atasan',
                ]
            );
        }

        $pegawai = [
            ['name' => 'Pegawai Demo', 'email' => 'pegawai@sikinerja.test'],
            ['name' => 'Andi Saputra', 'email' => 'pegawai.andi@sikinerja.test'],
            ['name' => 'Budi Santoso', 'email' => 'pegawai.budi@sikinerja.test'],
            ['name' => 'Citra Lestari', 'email' => 'pegawai.citra@sikinerja.test'],
            ['name' => 'Deni Kurniawan', 'email' => 'pegawai.deni@sikinerja.test'],
            ['name' => 'Eka Pratama', 'email' => 'pegawai.eka@sikinerja.test'],
        ];

        foreach ($pegawai as $user) {
            User::updateOrCreate(
                ['email' => $user['email']],
                [
                    'name' => $user['name'],
                    'password' => $password,
                    'role' => 'pegawai',
                ]
            );
        }
    }
}
