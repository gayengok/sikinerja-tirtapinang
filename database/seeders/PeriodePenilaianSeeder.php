<?php

namespace Database\Seeders;

use App\Models\PeriodePenilaian;
use Illuminate\Database\Seeder;

class PeriodePenilaianSeeder extends Seeder
{
    public function run(): void
    {
        PeriodePenilaian::updateOrCreate(
            ['nama_periode' => 'Penilaian Juli 2026'],
            [
                'tanggal_mulai' => '2026-07-01',
                'tanggal_selesai' => '2026-07-31',
                'status' => 'final',
            ]
        );

        PeriodePenilaian::updateOrCreate(
            ['nama_periode' => 'Penilaian Agustus 2026'],
            [
                'tanggal_mulai' => '2026-08-01',
                'tanggal_selesai' => '2026-08-31',
                'status' => 'berjalan',
            ]
        );
    }
}
