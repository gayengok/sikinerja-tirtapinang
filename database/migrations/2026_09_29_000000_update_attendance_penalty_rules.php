<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $rules = [
            'Terlambat Ringan' => [
                'tipe_pelanggaran' => 'terlambat',
                'menit_min' => 1,
                'menit_max' => 10,
                'keterangan' => 'Keterlambatan 1 - 10 menit setelah waktu masuk dan toleransi kantor.',
                'persen_potongan' => 0.5,
            ],
            'Terlambat Sedang' => [
                'tipe_pelanggaran' => 'terlambat',
                'menit_min' => 11,
                'menit_max' => 20,
                'keterangan' => 'Keterlambatan 11 - 20 menit setelah waktu masuk dan toleransi kantor.',
                'persen_potongan' => 2,
            ],
            'Terlambat Berat' => [
                'tipe_pelanggaran' => 'terlambat',
                'menit_min' => 21,
                'menit_max' => null,
                'keterangan' => 'Keterlambatan lebih dari 20 menit setelah waktu masuk dan toleransi kantor.',
                'persen_potongan' => 5,
            ],
            'Tidak Absen' => [
                'tipe_pelanggaran' => 'tidak_absen',
                'menit_min' => null,
                'menit_max' => null,
                'keterangan' => 'Potongan untuk setiap absensi masuk atau pulang yang tidak tercatat.',
                'persen_potongan' => 1,
            ],
            'Tanpa Keterangan' => [
                'tipe_pelanggaran' => 'alpa',
                'menit_min' => null,
                'menit_max' => null,
                'keterangan' => 'Tidak hadir tanpa izin sah atau sakit.',
                'persen_potongan' => 5,
            ],
            'Pulang Sebelum Waktu (Ringan)' => [
                'tipe_pelanggaran' => 'pulang_awal',
                'menit_min' => 1,
                'menit_max' => 30,
                'keterangan' => 'Pulang hingga 30 menit sebelum jam kerja berakhir.',
                'persen_potongan' => 1,
            ],
            'Pulang Sebelum Waktu (Berat)' => [
                'tipe_pelanggaran' => 'pulang_awal',
                'menit_min' => 31,
                'menit_max' => null,
                'keterangan' => 'Pulang lebih dari 30 menit sebelum jam kerja berakhir.',
                'persen_potongan' => 5,
            ],
        ];

        DB::table('master_jenis_potongans')
            ->where('nama_potongan', 'Alpa')
            ->update(['nama_potongan' => 'Tanpa Keterangan']);

        foreach ($rules as $nama => $data) {
            $sudahAda = DB::table('master_jenis_potongans')
                ->where('nama_potongan', $nama)
                ->exists();
            DB::table('master_jenis_potongans')->updateOrInsert(
                ['nama_potongan' => $nama],
                [
                    ...$data,
                    'updated_at' => now(),
                    ...($sudahAda ? [] : ['created_at' => now()]),
                ]
            );
        }
    }

    public function down(): void
    {
        DB::table('master_jenis_potongans')
            ->whereIn('nama_potongan', [
                'Pulang Sebelum Waktu (Ringan)',
                'Pulang Sebelum Waktu (Berat)',
            ])
            ->delete();

        DB::table('master_jenis_potongans')
            ->where('nama_potongan', 'Tanpa Keterangan')
            ->update([
                'nama_potongan' => 'Alpa',
                'persen_potongan' => 5,
            ]);
        DB::table('master_jenis_potongans')->where('nama_potongan', 'Terlambat Ringan')->update([
            'menit_min' => 1,
            'menit_max' => 15,
            'keterangan' => 'Keterlambatan 1 - 15 menit dari jam masuk yang ditentukan.',
            'persen_potongan' => 1,
        ]);
        DB::table('master_jenis_potongans')->where('nama_potongan', 'Terlambat Sedang')->update([
            'menit_min' => 16,
            'menit_max' => 30,
            'keterangan' => 'Keterlambatan 16 - 30 menit dari jam masuk yang ditentukan.',
            'persen_potongan' => 2,
        ]);
        DB::table('master_jenis_potongans')->where('nama_potongan', 'Terlambat Berat')->update([
            'menit_min' => 31,
            'menit_max' => 60,
            'keterangan' => 'Keterlambatan 31 - 60 menit dari jam masuk yang ditentukan.',
            'persen_potongan' => 5,
        ]);
        DB::table('master_jenis_potongans')->where('nama_potongan', 'Tidak Absen')->update([
            'keterangan' => 'Pegawai tidak melakukan absensi masuk maupun pulang tanpa keterangan.',
            'persen_potongan' => 2,
        ]);
    }
};
