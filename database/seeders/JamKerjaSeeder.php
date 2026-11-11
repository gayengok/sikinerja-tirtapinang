<?php

namespace Database\Seeders;

use App\Models\MasterJamKerja;
use Illuminate\Database\Seeder;

class JamKerjaSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['hari' => 'Senin', 'jam_masuk' => '07:30:00', 'jam_pulang' => '16:00:00'],
            ['hari' => 'Selasa', 'jam_masuk' => '07:30:00', 'jam_pulang' => '16:00:00'],
            ['hari' => 'Rabu', 'jam_masuk' => '07:30:00', 'jam_pulang' => '16:00:00'],
            ['hari' => 'Kamis', 'jam_masuk' => '07:30:00', 'jam_pulang' => '16:00:00'],
            ['hari' => 'Jumat', 'jam_masuk' => '07:30:00', 'jam_pulang' => '16:30:00'],
        ];

        foreach ($data as $item) {
            MasterJamKerja::updateOrCreate(
                [
                    'jabatan_id' => null,
                    'hari' => $item['hari'],
                ],
                [
                    'jam_masuk' => $item['jam_masuk'],
                    'jam_pulang' => $item['jam_pulang'],
                    'toleransi_menit' => 15,
                ]
            );
        }
    }
}
