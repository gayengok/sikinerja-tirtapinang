<?php

namespace Database\Seeders;

use App\Models\Absensi;
use App\Models\Pegawai;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class AbsensiSeeder extends Seeder
{
    /**
     * Daftar status beserta bobot kemunculannya (persentase kasar).
     */
    protected array $statusWeights = [
        'hadir'      => 80,
        'izin'       => 6,
        'sakit'      => 6,
        'alpa'       => 3,
        'cuti'       => 3,
        'dinas_luar' => 2,
    ];

    public function run(): void
    {
        $pegawais = Pegawai::all();

        if ($pegawais->isEmpty()) {
            $this->command->warn('Tidak ada data pegawai. Jalankan seeder pegawai terlebih dahulu.');
            return;
        }

        // Rentang tanggal: 3 bulan ke belakang sampai hari ini
        $mulai  = Carbon::now()->subMonths(3)->startOfMonth();
        $selesai = Carbon::now();

        foreach ($pegawais as $pegawai) {
            $tanggal = $mulai->copy();

            while ($tanggal->lte($selesai)) {
                // Lewati Sabtu & Minggu (asumsi kerja Senin-Jumat)
                if ($tanggal->isWeekend()) {
                    $tanggal->addDay();
                    continue;
                }

                $status = $this->randomStatus();

                Absensi::create([
                    'pegawai_id'  => $pegawai->id,
                    'tanggal'     => $tanggal->format('Y-m-d'),
                    'jam_masuk'   => $this->jamMasuk($status),
                    'jam_pulang'  => $this->jamPulang($status),
                    'status'      => $status,
                    'sumber'      => $this->randomSumber($status),
                    'keterangan'  => $this->keterangan($status),
                ]);

                $tanggal->addDay();
            }
        }

        $this->command->info('Data absensi dummy berhasil dibuat.');
    }

    protected function randomStatus(): string
    {
        $rand = random_int(1, 100);
        $kumulatif = 0;

        foreach ($this->statusWeights as $status => $bobot) {
            $kumulatif += $bobot;
            if ($rand <= $kumulatif) {
                return $status;
            }
        }

        return 'hadir';
    }

    protected function jamMasuk(string $status): ?string
    {
        if (!in_array($status, ['hadir', 'dinas_luar'])) {
            return null;
        }

        // Jam masuk acak 07:30 - 08:30, kadang telat sampai 09:00
        $menit = random_int(7 * 60 + 30, 9 * 60);

        return sprintf('%02d:%02d:00', intdiv($menit, 60), $menit % 60);
    }

    protected function jamPulang(string $status): ?string
    {
        if (!in_array($status, ['hadir', 'dinas_luar'])) {
            return null;
        }

        // Jam pulang acak 16:00 - 17:30
        $menit = random_int(16 * 60, 17 * 60 + 30);

        return sprintf('%02d:%02d:00', intdiv($menit, 60), $menit % 60);
    }

    protected function randomSumber(string $status): string
    {
        if ($status === 'alpa') {
            return 'sistem';
        }

        return collect(['manual', 'fingerprint', 'aplikasi'])->random();
    }

    protected function keterangan(string $status): ?string
    {
        return match ($status) {
            'izin'       => collect([
                'Keperluan keluarga',
                'Urusan administrasi',
                'Acara pribadi',
            ])->random(),
            'sakit'      => collect([
                'Demam',
                'Flu',
                'Sakit gigi',
                'Kontrol dokter',
            ])->random(),
            'cuti'       => 'Cuti tahunan',
            'dinas_luar' => collect([
                'Rapat koordinasi',
                'Kunjungan lapangan',
                'Diklat luar kota',
            ])->random(),
            'alpa'       => null,
            default      => null,
        };
    }
}
