<?php

namespace Database\Seeders;

use App\Models\MasterJamKerja;
use Illuminate\Database\Seeder;

class JamKerjaSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            'Senin' => [
                'jam_pulang' => '16:00:00',
                'jam_buka_absen_pulang' => '16:00:00',
                'jam_tutup_absen_pulang' => '18:00:00',
            ],
            'Selasa' => [
                'jam_pulang' => '16:00:00',
                'jam_buka_absen_pulang' => '16:00:00',
                'jam_tutup_absen_pulang' => '18:00:00',
            ],
            'Rabu' => [
                'jam_pulang' => '16:00:00',
                'jam_buka_absen_pulang' => '16:00:00',
                'jam_tutup_absen_pulang' => '18:00:00',
            ],
            'Kamis' => [
                'jam_pulang' => '16:00:00',
                'jam_buka_absen_pulang' => '16:00:00',
                'jam_tutup_absen_pulang' => '18:00:00',
            ],
            'Jumat' => [
                'jam_pulang' => '16:30:00',
                'jam_buka_absen_pulang' => '16:30:00',
                'jam_tutup_absen_pulang' => '18:30:00',
            ],
        ];

        foreach ($data as $hari => $item) {
            MasterJamKerja::firstOrCreate(
                [
                    'jabatan_id' => null,
                    'hari' => $hari,
                ],
                [
                    'jam_masuk' => '07:30:00',
                    'jam_pulang' => $item['jam_pulang'],
                    'toleransi_menit' => 15,
                    'jam_buka_absen_masuk' => '06:30:00',
                    'batas_absen_masuk' => '07:45:00',
                    'jam_buka_absen_pulang' => $item['jam_buka_absen_pulang'],
                    'jam_tutup_absen_pulang' => $item['jam_tutup_absen_pulang'],
                ]
            );
        }
    }
}
