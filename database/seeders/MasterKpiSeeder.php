<?php

namespace Database\Seeders;

use App\Models\Jabatan;
use App\Models\MasterKpi;
use Illuminate\Database\Seeder;

class MasterKpiSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            'Staff Administrasi' => [
                [
                    'indikator' => 'Persentase keluhan pelanggan yang diselesaikan dalam waktu tanggap',
                    'target_default' => 100,
                    'realisasi' => 100,
                    'persen_capaian' => 100,
                    'skor' => 5,
                    'bobot' => 15,
                    'arah_capaian' => 'maksimal',
                    'urutan' => 1,
                ],
                [
                    'indikator' => 'Tingkat akurasi informasi yang diberikan kepada pelanggan mengenai produk/layanan',
                    'target_default' => 100,
                    'realisasi' => 100,
                    'persen_capaian' => 100,
                    'skor' => 5,
                    'bobot' => 15,
                    'arah_capaian' => 'maksimal',
                    'urutan' => 2,
                ],
                [
                    'indikator' => 'Tingkat kelengkapan dan ketelitian pencatatan setiap keluhan yang masuk',
                    'target_default' => 100,
                    'realisasi' => 100,
                    'persen_capaian' => 100,
                    'skor' => 5,
                    'bobot' => 15,
                    'arah_capaian' => 'maksimal',
                    'urutan' => 3,
                ],
                [
                    'indikator' => 'Persentase penyampaian informasi terbaru kepada pelanggan (gangguan air, status pengaduan)',
                    'target_default' => 100,
                    'realisasi' => 100,
                    'persen_capaian' => 100,
                    'skor' => 5,
                    'bobot' => 15,
                    'arah_capaian' => 'maksimal',
                    'urutan' => 4,
                ],
            ],
        ];

        foreach ($data as $namaJabatan => $kpis) {
            $jabatan = Jabatan::where('nama_jabatan', $namaJabatan)->first();

            if (!$jabatan) {
                continue;
            }

            foreach ($kpis as $kpi) {
                MasterKpi::updateOrCreate(
                    [
                        'jabatan_id' => $jabatan->id,
                        'indikator' => $kpi['indikator'],
                    ],
                    [
                        'target_default' => $kpi['target_default'],
                        'realisasi' => $kpi['realisasi'] ?? null,
                        'persen_capaian' => $kpi['persen_capaian'] ?? null,
                        'skor' => $kpi['skor'] ?? null,
                        'keterangan' => $kpi['keterangan'] ?? null,
                        'bobot' => $kpi['bobot'],
                        'arah_capaian' => $kpi['arah_capaian'],
                        'urutan' => $kpi['urutan'],
                    ]
                );
            }
        }
    }
}
