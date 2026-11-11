<?php

namespace Database\Seeders;

use App\Models\MasterJenisPotongan;
use Illuminate\Database\Seeder;

class JenisPotonganSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            [
                'nama_potongan' => 'Terlambat Ringan',
                'tipe_pelanggaran' => 'terlambat',
                'menit_min' => 1,
                'menit_max' => 10,
                'keterangan' => 'Keterlambatan 1 - 10 menit setelah waktu masuk dan toleransi kantor.',
                'persen_potongan' => 0.5,
            ],
            [
                'nama_potongan' => 'Terlambat Sedang',
                'tipe_pelanggaran' => 'terlambat',
                'menit_min' => 11,
                'menit_max' => 20,
                'keterangan' => 'Keterlambatan 11 - 20 menit setelah waktu masuk dan toleransi kantor.',
                'persen_potongan' => 2,
            ],
            [
                'nama_potongan' => 'Terlambat Berat',
                'tipe_pelanggaran' => 'terlambat',
                'menit_min' => 21,
                'menit_max' => null,
                'keterangan' => 'Keterlambatan lebih dari 20 menit setelah waktu masuk dan toleransi kantor.',
                'persen_potongan' => 5,
            ],
            [
                'nama_potongan' => 'Tidak Absen',
                'tipe_pelanggaran' => 'tidak_absen',
                'menit_min' => null,
                'menit_max' => null,
                'keterangan' => 'Potongan untuk setiap absensi masuk atau pulang yang tidak tercatat.',
                'persen_potongan' => 1,
            ],
            [
                'nama_potongan' => 'Tanpa Keterangan',
                'tipe_pelanggaran' => 'alpa',
                'menit_min' => null,
                'menit_max' => null,
                'keterangan' => 'Pegawai tidak hadir tanpa keterangan yang sah.',
                'persen_potongan' => 5,
            ],
            [
                'nama_potongan' => 'Pulang Sebelum Waktu (Ringan)',
                'tipe_pelanggaran' => 'pulang_awal',
                'menit_min' => 1,
                'menit_max' => 30,
                'keterangan' => 'Pulang hingga 30 menit sebelum jam kerja berakhir.',
                'persen_potongan' => 1,
            ],
            [
                'nama_potongan' => 'Pulang Sebelum Waktu (Berat)',
                'tipe_pelanggaran' => 'pulang_awal',
                'menit_min' => 31,
                'menit_max' => null,
                'keterangan' => 'Pulang lebih dari 30 menit sebelum jam kerja berakhir.',
                'persen_potongan' => 5,
            ],
        ];

        foreach ($data as $item) {
            MasterJenisPotongan::updateOrCreate(
                ['nama_potongan' => $item['nama_potongan']],
                [
                    'tipe_pelanggaran' => $item['tipe_pelanggaran'],
                    'menit_min' => $item['menit_min'],
                    'menit_max' => $item['menit_max'],
                    'keterangan' => $item['keterangan'],
                    'persen_potongan' => $item['persen_potongan'],
                ]
            );
        }
    }
}
