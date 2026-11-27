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
                    'jenis_jadwal' => 'normal',
                    'nomor_shift' => null,
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

        $shiftData = [
            1 => [
                'jam_masuk' => '07:00:00',
                'jam_pulang' => '15:00:00',
                'jam_buka_absen_masuk' => '06:00:00',
                'batas_absen_masuk' => '07:15:00',
                'jam_buka_absen_pulang' => '15:00:00',
                'jam_tutup_absen_pulang' => '17:00:00',
            ],
            2 => [
                'jam_masuk' => '15:00:00',
                'jam_pulang' => '23:00:00',
                'jam_buka_absen_masuk' => '14:00:00',
                'batas_absen_masuk' => '15:15:00',
                'jam_buka_absen_pulang' => '23:00:00',
                'jam_tutup_absen_pulang' => '23:59:00',
            ],
            3 => [
                'jam_masuk' => '23:00:00',
                'jam_pulang' => '07:00:00',
                'jam_buka_absen_masuk' => '22:00:00',
                'batas_absen_masuk' => '23:15:00',
                'jam_buka_absen_pulang' => '07:00:00',
                'jam_tutup_absen_pulang' => '09:00:00',
            ],
        ];

        foreach ($shiftData as $nomorShift => $jadwal) {
            MasterJamKerja::firstOrCreate(
                [
                    'jabatan_id' => null,
                    'hari' => 'Kerja',
                    'jenis_jadwal' => 'shift',
                    'nomor_shift' => $nomorShift,
                ],
                array_merge($jadwal, ['toleransi_menit' => 15])
            );
        }
    }
}
