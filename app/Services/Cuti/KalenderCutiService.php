<?php

namespace App\Services\Cuti;

use App\Models\DetailPengajuanCuti;
use App\Models\JenisCuti;
use App\Models\MasterJamKerja;
use App\Models\Pegawai;
use App\Support\HariLibur;
use Carbon\CarbonImmutable;
use DateTimeInterface;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class KalenderCutiService
{
    private const TIMEZONE = 'Asia/Jakarta';

    private const METODE_HARI_KERJA = 'hari_kerja';

    private const METODE_HARI_KALENDER = 'hari_kalender';

    private const NAMA_HARI = [
        'Minggu',
        'Senin',
        'Selasa',
        'Rabu',
        'Kamis',
        'Jumat',
        'Sabtu',
    ];

    public function hitung(
        Pegawai $pegawai,
        JenisCuti $jenisCuti,
        string|DateTimeInterface $tanggalMulai,
        string|DateTimeInterface $tanggalSelesai
    ): array {
        [$mulai, $selesai] = $this->validasiRentangTanggal(
            $tanggalMulai,
            $tanggalSelesai
        );

        $metode = $jenisCuti->metode_hitung_hari;

        $this->validasiMetode($metode);

        $jadwalKerja = $this->ambilJadwalKerja($pegawai);

        $details = [];

        $jumlahHariKalender = 0;
        $jumlahHariKerja = 0;
        $jumlahHariDihitung = 0;

        for (
            $tanggal = $mulai;
            $tanggal->lte($selesai);
            $tanggal = $tanggal->addDay()
        ) {
            $isHariKerja = $this->tentukanHariKerja(
                $tanggal,
                $jadwalKerja
            );

            $dihitungKuota = match ($metode) {
                self::METODE_HARI_KERJA => $isHariKerja,
                self::METODE_HARI_KALENDER => true,
            };

            $details[] = [
                'tanggal' => $tanggal->toDateString(),
                'is_hari_kerja' => $isHariKerja,
                'dihitung_kuota' => $dihitungKuota,
                'status' => $dihitungKuota
                    ? DetailPengajuanCuti::STATUS_PENDING
                    : DetailPengajuanCuti::STATUS_EXCLUDED,
            ];

            $jumlahHariKalender++;

            if ($isHariKerja) {
                $jumlahHariKerja++;
            }

            if ($dihitungKuota) {
                $jumlahHariDihitung++;
            }
        }

        return [
            'tanggal_mulai' => $mulai->toDateString(),
            'tanggal_selesai' => $selesai->toDateString(),
            'metode_hitung_hari' => $metode,
            'jumlah_hari_kalender' => $jumlahHariKalender,
            'jumlah_hari_kerja' => $jumlahHariKerja,
            'jumlah_hari_libur' => $jumlahHariKalender
                - $jumlahHariKerja,
            'jumlah_hari_diajukan' => $jumlahHariDihitung,
            'jumlah_hari_dikecualikan' => $jumlahHariKalender
                - $jumlahHariDihitung,
            'details' => $details,
        ];
    }

    public function isHariKerja(
        Pegawai $pegawai,
        string|DateTimeInterface $tanggal
    ): bool {
        $date = $this->normalisasiTanggal(
            $tanggal,
            'tanggal'
        );

        return $this->tentukanHariKerja(
            $date,
            $this->ambilJadwalKerja($pegawai)
        );
    }

    public function hitungHari(
        Pegawai $pegawai,
        JenisCuti $jenisCuti,
        string|DateTimeInterface $tanggalMulai,
        string|DateTimeInterface $tanggalSelesai
    ): int {
        return $this->hitung(
            $pegawai,
            $jenisCuti,
            $tanggalMulai,
            $tanggalSelesai
        )['jumlah_hari_diajukan'];
    }

    public function generateDetailTanggal(
        Pegawai $pegawai,
        JenisCuti $jenisCuti,
        string|DateTimeInterface $tanggalMulai,
        string|DateTimeInterface $tanggalSelesai
    ): array {
        return $this->hitung(
            $pegawai,
            $jenisCuti,
            $tanggalMulai,
            $tanggalSelesai
        )['details'];
    }

    public function validasiRentangTanggal(
        string|DateTimeInterface $tanggalMulai,
        string|DateTimeInterface $tanggalSelesai
    ): array {
        $mulai = $this->normalisasiTanggal(
            $tanggalMulai,
            'tanggal_mulai'
        );

        $selesai = $this->normalisasiTanggal(
            $tanggalSelesai,
            'tanggal_selesai'
        );

        if ($selesai->lt($mulai)) {
            throw ValidationException::withMessages([
                'tanggal_selesai' =>
                'Tanggal selesai tidak boleh sebelum tanggal mulai.',
            ]);
        }

        return [$mulai, $selesai];
    }

    private function validasiMetode(?string $metode): void
    {
        if (! in_array($metode, [
            self::METODE_HARI_KERJA,
            self::METODE_HARI_KALENDER,
        ], true)) {
            throw ValidationException::withMessages([
                'metode_hitung_hari' =>
                'Metode perhitungan hari cuti tidak valid.',
            ]);
        }
    }

    private function ambilJadwalKerja(
        Pegawai $pegawai
    ): Collection {
        return MasterJamKerja::query()
            ->where(function ($query) use ($pegawai) {
                $query->whereNull('jabatan_id');

                if ($pegawai->jabatan_id !== null) {
                    $query->orWhere(
                        'jabatan_id',
                        $pegawai->jabatan_id
                    );
                }
            })
            ->orderBy('id')
            ->get()
            ->groupBy('hari')
            ->map(function (Collection $jadwals) use ($pegawai) {
                return $jadwals->firstWhere(
                    'jabatan_id',
                    $pegawai->jabatan_id
                ) ?? $jadwals->firstWhere(
                    'jabatan_id',
                    null
                );
            });
    }

    private function jadwalUntukTanggal(
        CarbonImmutable $tanggal,
        Collection $jadwalKerja
    ): ?MasterJamKerja {
        $namaHari = self::NAMA_HARI[$tanggal->dayOfWeek];

        return $jadwalKerja->get($namaHari)
            ?? $jadwalKerja->get('Kerja');
    }

    private function tentukanHariKerja(
        CarbonImmutable $tanggal,
        Collection $jadwalKerja
    ): bool {
        if (HariLibur::detail($tanggal->toDateString())) {
            return false;
        }

        $jadwal = $this->jadwalUntukTanggal(
            $tanggal,
            $jadwalKerja
        );

        if ($jadwal !== null) {
            return true;
        }

        return $tanggal->isWeekday();
    }

    private function normalisasiTanggal(
        string|DateTimeInterface $tanggal,
        string $field
    ): CarbonImmutable {
        if ($tanggal instanceof DateTimeInterface) {
            return CarbonImmutable::instance($tanggal)
                ->setTimezone(self::TIMEZONE)
                ->startOfDay();
        }

        $tanggal = trim($tanggal);

        if (
            ! preg_match(
                '/^\d{4}-\d{2}-\d{2}$/D',
                $tanggal
            )
        ) {
            throw ValidationException::withMessages([
                $field => 'Format tanggal harus YYYY-MM-DD.',
            ]);
        }

        [$tahun, $bulan, $hari] = array_map(
            'intval',
            explode('-', $tanggal)
        );

        if (! checkdate($bulan, $hari, $tahun)) {
            throw ValidationException::withMessages([
                $field => 'Tanggal tidak valid.',
            ]);
        }

        return CarbonImmutable::create(
            $tahun,
            $bulan,
            $hari,
            0,
            0,
            0,
            self::TIMEZONE
        );
    }
}