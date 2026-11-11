<?php

namespace Database\Seeders;

use App\Models\MasterKompetensi;
use Illuminate\Database\Seeder;

class MasterKompetensiSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            [
                'nama_kompetensi' => 'Orientasi Pelanggan',
                'definisi_perilaku' => 'Kemampuan memberikan pelayanan yang ramah dan memuaskan.',
                'urutan' => 1,
            ],
            [
                'nama_kompetensi' => 'Komunikasi',
                'definisi_perilaku' => 'Kemampuan memberikan informasi yang akurat dan jelas.',
                'urutan' => 2,
            ],
            [
                'nama_kompetensi' => 'Pemecahan Masalah',
                'definisi_perilaku' => 'Kemampuan menyelesaikan masalah pelanggan dengan cepat dan efektif.',
                'urutan' => 3,
            ],
            [
                'nama_kompetensi' => 'Promosi Perusahaan',
                'definisi_perilaku' => 'Kemampuan membangun citra positif perusahaan di mata pelanggan.',
                'urutan' => 4,
            ],
            [
                'nama_kompetensi' => 'Kedisiplinan',
                'definisi_perilaku' => 'Kepatuhan terhadap aturan jam kerja.',
                'urutan' => 5,
            ],
        ];

        foreach ($data as $item) {
            MasterKompetensi::updateOrCreate(
                [
                    'jabatan_id' => null,
                    'nama_kompetensi' => $item['nama_kompetensi'],
                ],
                [
                    'definisi_perilaku' => $item['definisi_perilaku'],
                    'urutan' => $item['urutan'],
                ]
            );
        }
    }
}
