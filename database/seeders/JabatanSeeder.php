<?php

namespace Database\Seeders;

use App\Models\Divisi;
use App\Models\Jabatan;
use Illuminate\Database\Seeder;

class JabatanSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            'Seksi Hubungan Langganan' => [
                ['nama_jabatan' => 'Kepala Seksi Hubungan Langganan', 'grade' => 'Grade 3', 'base_tukin' => 2500000],
                ['nama_jabatan' => 'Staff Administrasi', 'grade' => 'Grade 2', 'base_tukin' => 2000000],
                ['nama_jabatan' => 'Pelaksana Pembaca Meter', 'grade' => 'Grade 1', 'base_tukin' => 1500000],
            ],
            'Seksi Teknik' => [
                ['nama_jabatan' => 'Kepala Seksi Teknik', 'grade' => 'Grade 3', 'base_tukin' => 2500000],
                ['nama_jabatan' => 'Pelaksana Teknik', 'grade' => 'Grade 1', 'base_tukin' => 1500000],
            ],
            'Seksi Produksi & Pengolahan' => [
                ['nama_jabatan' => 'Kepala Seksi Produksi & Pengolahan', 'grade' => 'Grade 3', 'base_tukin' => 2500000],
                ['nama_jabatan' => 'Operator Produksi', 'grade' => 'Grade 1', 'base_tukin' => 1500000],
            ],
            'Seksi Keuangan' => [
                ['nama_jabatan' => 'Kepala Seksi Keuangan', 'grade' => 'Grade 3', 'base_tukin' => 2500000],
                ['nama_jabatan' => 'Staff Keuangan', 'grade' => 'Grade 2', 'base_tukin' => 2000000],
            ],
            'Seksi Umum' => [
                ['nama_jabatan' => 'Kepala Seksi Umum', 'grade' => 'Grade 3', 'base_tukin' => 2500000],
                ['nama_jabatan' => 'Staff Administrasi Umum', 'grade' => 'Grade 2', 'base_tukin' => 2000000],
            ],
        ];

        foreach ($data as $namaDivisi => $jabatans) {
            $divisi = Divisi::where('nama_divisi', $namaDivisi)->first();

            if (!$divisi) {
                continue;
            }

            foreach ($jabatans as $jabatan) {
                Jabatan::updateOrCreate(
                    [
                        'divisi_id' => $divisi->id,
                        'nama_jabatan' => $jabatan['nama_jabatan'],
                    ],
                    [
                        'grade' => $jabatan['grade'],
                        'base_tukin' => $jabatan['base_tukin'],
                    ]
                );
            }
        }
    }
}
