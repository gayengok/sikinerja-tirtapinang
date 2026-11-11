<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('master_kpis')
            ->whereIn('indikator', [
                'Persentase keluhan pelanggan yang diselesaikan dalam waktu tanggap',
                'Persentase penyampaian informasi terbaru kepada pelanggan (gangguan air, status pengaduan)',
            ])
            ->update([
                'target_default' => 100,
                'realisasi' => 100,
                'persen_capaian' => 100,
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        DB::table('master_kpis')
            ->whereIn('indikator', [
                'Persentase keluhan pelanggan yang diselesaikan dalam waktu tanggap',
                'Persentase penyampaian informasi terbaru kepada pelanggan (gangguan air, status pengaduan)',
            ])
            ->update([
                'target_default' => 5,
                'realisasi' => 4,
                'persen_capaian' => 80,
                'updated_at' => now(),
            ]);
    }
};
