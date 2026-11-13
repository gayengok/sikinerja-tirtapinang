<?php

namespace Database\Seeders;

use App\Models\Divisi;
use App\Models\Jabatan;
use App\Models\Pegawai;
use App\Models\User;
use Illuminate\Database\Seeder;
use RuntimeException;

class DirekturSeeder extends Seeder
{
    public function run(): void
    {
        $direkturUser = User::where('email', 'direktur@sikinerja.test')->first();
        $kepalaBagianUser = User::where('email', env('KEPALA_BAGIAN_EMAIL', 'kabag@sikinerja.test'))
            ->where('role', 'kepala_bagian')
            ->first();

        if (! $direkturUser || ! $kepalaBagianUser?->pegawai) {
            throw new RuntimeException('Jalankan UserSeeder dan KepalaBagianSeeder sebelum DirekturSeeder.');
        }

        $divisi = Divisi::updateOrCreate(
            ['nama_divisi' => 'Direksi'],
            ['nama_divisi' => 'Direksi']
        );

        $jabatan = Jabatan::updateOrCreate(
            [
                'divisi_id' => $divisi->id,
                'nama_jabatan' => 'Direktur',
            ],
            ['base_tukin' => 0]
        );

        $direktur = Pegawai::updateOrCreate(
            ['user_id' => $direkturUser->id],
            [
                'nama' => $direkturUser->name,
                'jabatan_id' => $jabatan->id,
                'atasan_id' => null,
                'tanggal_masuk' => '2015-01-01',
                'status' => 'aktif',
            ]
        );

        $kepalaBagianUser->pegawai->update(['atasan_id' => $direktur->id]);
    }
}
