<?php

namespace Database\Seeders;

use App\Models\Jabatan;
use App\Models\Pegawai;
use App\Models\User;
use Illuminate\Database\Seeder;

class PegawaiSeeder extends Seeder
{
    public function run(): void
    {
        $teams = [
            [
                'atasan_email' => 'atasan@sikinerja.test',
                'atasan_nama' => 'Kepala Bagian Hubungan Langganan',
                'atasan_jabatan' => 'Kepala Seksi Hubungan Langganan',
                'atasan_tanggal' => '2018-01-01',
                'pegawai' => [
                    ['email' => 'pegawai@sikinerja.test', 'nama' => 'Pegawai Demo', 'jabatan' => 'Pelaksana Pembaca Meter', 'tanggal' => '2021-03-15'],
                    ['email' => 'pegawai.andi@sikinerja.test', 'nama' => 'Andi Saputra', 'jabatan' => 'Staff Administrasi', 'tanggal' => '2020-06-01'],
                ],
            ],
            [
                'atasan_email' => 'atasan.teknik@sikinerja.test',
                'atasan_nama' => 'Kepala Bagian Teknik',
                'atasan_jabatan' => 'Kepala Seksi Teknik',
                'atasan_tanggal' => '2017-02-01',
                'pegawai' => [
                    ['email' => 'pegawai.budi@sikinerja.test', 'nama' => 'Budi Santoso', 'jabatan' => 'Pelaksana Teknik', 'tanggal' => '2021-07-01'],
                    ['email' => 'pegawai.citra@sikinerja.test', 'nama' => 'Citra Lestari', 'jabatan' => 'Pelaksana Teknik', 'tanggal' => '2022-01-10'],
                ],
            ],
            [
                'atasan_email' => 'atasan.produksi@sikinerja.test',
                'atasan_nama' => 'Kepala Atasan',
                'atasan_jabatan' => 'Kepala Seksi Produksi & Pengolahan',
                'atasan_tanggal' => '2016-04-01',
                'pegawai' => [
                    ['email' => 'pegawai.deni@sikinerja.test', 'nama' => 'Deni Kurniawan', 'jabatan' => 'Operator Produksi', 'tanggal' => '2020-08-15'],
                    ['email' => 'pegawai.eka@sikinerja.test', 'nama' => 'Eka Pratama', 'jabatan' => 'Operator Produksi', 'tanggal' => '2022-03-01'],
                ],
            ],
        ];

        foreach ($teams as $team) {
            $userAtasan = User::where('email', $team['atasan_email'])->first();
            $jabatanAtasan = Jabatan::where('nama_jabatan', $team['atasan_jabatan'])->first();

            if (! $userAtasan || ! $jabatanAtasan) {
                continue;
            }

            $atasan = Pegawai::updateOrCreate(
                ['user_id' => $userAtasan->id],
                [
                    'nama' => $team['atasan_nama'],
                    'jabatan_id' => $jabatanAtasan->id,
                    'atasan_id' => null,
                    'tanggal_masuk' => $team['atasan_tanggal'],
                    'status' => 'aktif',
                ]
            );

            foreach ($team['pegawai'] as $data) {
                $userPegawai = User::where('email', $data['email'])->first();
                $jabatanPegawai = Jabatan::where('nama_jabatan', $data['jabatan'])
                    ->where('divisi_id', $jabatanAtasan->divisi_id)
                    ->first();

                if (! $userPegawai || ! $jabatanPegawai) {
                    continue;
                }

                Pegawai::updateOrCreate(
                    ['user_id' => $userPegawai->id],
                    [
                        'nama' => $data['nama'],
                        'jabatan_id' => $jabatanPegawai->id,
                        'atasan_id' => $atasan->id,
                        'tanggal_masuk' => $data['tanggal'],
                        'status' => 'aktif',
                    ]
                );
            }
        }
    }
}
