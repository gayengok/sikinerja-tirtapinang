<?php

namespace Database\Seeders;

use App\Models\Jabatan;
use App\Models\Pegawai;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class KepalaBagianSeeder extends Seeder
{
    public function run(): void
    {
        $atasanUser = User::where('email', 'atasan@sikinerja.test')->first();
        $atasan = $atasanUser?->pegawai;

        if (! $atasan || ! $atasan->jabatan) {
            throw new RuntimeException('Jalankan UserSeeder, DivisiSeeder, JabatanSeeder, dan PegawaiSeeder sebelum KepalaBagianSeeder.');
        }

        $kepalaBagianUser = User::updateOrCreate(
            ['email' => env('KEPALA_BAGIAN_EMAIL', 'kabag@sikinerja.test')],
            [
                'name' => 'Kepala Bagian Demo',
                'password' => Hash::make(env('KEPALA_BAGIAN_PASSWORD', 'password')),
                'role' => 'kepala_bagian',
            ]
        );

        $jabatan = Jabatan::updateOrCreate(
            [
                'divisi_id' => $atasan->jabatan->divisi_id,
                'nama_jabatan' => 'Kepala Bagian',
            ],
            [
                'grade' => $atasan->jabatan->grade,
                'base_tukin' => $atasan->jabatan->base_tukin,
            ]
        );

        $kepalaBagian = Pegawai::updateOrCreate(
            ['user_id' => $kepalaBagianUser->id],
            [
                'nama' => $kepalaBagianUser->name,
                'jabatan_id' => $jabatan->id,
                'atasan_id' => null,
                'tanggal_masuk' => '2015-01-01',
                'status' => 'aktif',
            ]
        );

        $atasan->update(['atasan_id' => $kepalaBagian->id]);
    }
}
