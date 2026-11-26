<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('master_jam_kerjas', function (Blueprint $table) {
            $table->time('jam_buka_absen_masuk')->nullable();
            $table->time('batas_absen_masuk')->nullable();
            $table->time('jam_buka_absen_pulang')->nullable();
            $table->time('jam_tutup_absen_pulang')->nullable();
        });

        DB::table('master_jam_kerjas')
            ->select([
                'id',
                'jam_masuk',
                'jam_pulang',
                'toleransi_menit',
            ])
            ->orderBy('id')
            ->chunkById(100, function ($jamKerjas): void {
                foreach ($jamKerjas as $jamKerja) {
                    $jamMasuk = Carbon::parse($jamKerja->jam_masuk);
                    $jamPulang = Carbon::parse($jamKerja->jam_pulang);

                    DB::table('master_jam_kerjas')
                        ->where('id', $jamKerja->id)
                        ->update([
                            'jam_buka_absen_masuk' => $jamMasuk->copy()->subHour()->format('H:i:s'),
                            'batas_absen_masuk' => $jamMasuk->copy()->addMinutes((int) $jamKerja->toleransi_menit)->format('H:i:s'),
                            'jam_buka_absen_pulang' => $jamPulang->format('H:i:s'),
                            'jam_tutup_absen_pulang' => $jamPulang->copy()->addHours(2)->format('H:i:s'),
                        ]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('master_jam_kerjas', function (Blueprint $table) {
            $table->dropColumn([
                'jam_buka_absen_masuk',
                'batas_absen_masuk',
                'jam_buka_absen_pulang',
                'jam_tutup_absen_pulang',
            ]);
        });
    }
};
