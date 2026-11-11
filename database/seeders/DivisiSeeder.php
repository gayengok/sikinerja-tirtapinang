<?php

namespace Database\Seeders;

use App\Models\Divisi;
use Illuminate\Database\Seeder;

class DivisiSeeder extends Seeder
{
    public function run(): void
    {
        $divisis = [
            'Seksi Hubungan Langganan',
            'Seksi Teknik',
            'Seksi Produksi & Pengolahan',
            'Seksi Keuangan',
            'Seksi Umum',
        ];

        foreach ($divisis as $nama) {
            Divisi::updateOrCreate(
                ['nama_divisi' => $nama],
                ['nama_divisi' => $nama]
            );
        }
    }
}
