<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            DivisiSeeder::class,
            JabatanSeeder::class,
            PegawaiSeeder::class,
            KepalaBagianSeeder::class,
            PeriodePenilaianSeeder::class,
            MasterKpiSeeder::class,
            MasterKompetensiSeeder::class,
            TemplateLaporanSeeder::class,
            JamKerjaSeeder::class,
            JenisPotonganSeeder::class,
        ]);
    }
}
