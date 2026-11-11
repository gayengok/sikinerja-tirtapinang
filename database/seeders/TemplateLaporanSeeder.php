<?php

namespace Database\Seeders;

use App\Models\Divisi;
use App\Models\MasterKpi;
use App\Models\MasterTemplateLaporan;
use Illuminate\Database\Seeder;

class TemplateLaporanSeeder extends Seeder
{
    public function run(): void
    {
        $divisi = Divisi::where('nama_divisi', 'Seksi Hubungan Langganan')->first();

        if (!$divisi) {
            return;
        }

        $kpiKeluhan = MasterKpi::where('indikator', 'Persentase keluhan pelanggan yang diselesaikan dalam waktu tanggap')->first();
        $kpiAkurasi = MasterKpi::where('indikator', 'Tingkat akurasi informasi yang diberikan kepada pelanggan mengenai produk/layanan')->first();
        $kpiPencatatan = MasterKpi::where('indikator', 'Tingkat kelengkapan dan ketelitian pencatatan setiap keluhan yang masuk')->first();
        $kpiInformasi = MasterKpi::where('indikator', 'Persentase penyampaian informasi terbaru kepada pelanggan (gangguan air, status pengaduan)')->first();

        $fields = [
            [
                'nama_field' => 'Jumlah Keluhan Diselesaikan dalam Waktu Tanggap',
                'tipe_field' => 'angka',
                'satuan' => 'keluhan',
                'terhubung_ke_kpi_id' => $kpiKeluhan?->id,
            ],
            [
                'nama_field' => 'Jumlah Informasi Akurat yang Diberikan',
                'tipe_field' => 'angka',
                'satuan' => 'informasi',
                'terhubung_ke_kpi_id' => $kpiAkurasi?->id,
            ],
            [
                'nama_field' => 'Jumlah Keluhan Tercatat Lengkap dan Detail',
                'tipe_field' => 'angka',
                'satuan' => 'keluhan',
                'terhubung_ke_kpi_id' => $kpiPencatatan?->id,
            ],
            [
                'nama_field' => 'Jumlah Penyampaian Informasi Terbaru ke Pelanggan',
                'tipe_field' => 'angka',
                'satuan' => 'kali',
                'terhubung_ke_kpi_id' => $kpiInformasi?->id,
            ],
        ];

        foreach ($fields as $field) {
            MasterTemplateLaporan::updateOrCreate(
                [
                    'divisi_id' => $divisi->id,
                    'nama_field' => $field['nama_field'],
                ],
                [
                    'tipe_field' => $field['tipe_field'],
                    'satuan' => $field['satuan'],
                    'terhubung_ke_kpi_id' => $field['terhubung_ke_kpi_id'],
                ]
            );
        }
    }
}
