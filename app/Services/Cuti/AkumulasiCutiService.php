<?php

namespace App\Services\Cuti;

use App\Models\JenisCuti;
use App\Models\KuotaCuti;
use App\Models\Pegawai;
use Carbon\CarbonImmutable;
use InvalidArgumentException;
use LogicException;

class AkumulasiCutiService
{
    public function validasiTahun(int $tahun): void
    {
        if ($tahun < 1900 || $tahun > 9999) {
            throw new InvalidArgumentException(
                'Tahun periode cuti tidak valid.'
            );
        }
    }

    public function validasiKebijakan(JenisCuti $jenisCuti): void
    {
        if ($jenisCuti->periode_kuota !== 'tahunan') {
            throw new InvalidArgumentException(
                'Perhitungan akumulasi hanya berlaku untuk kuota tahunan.'
            );
        }

        if (
            $jenisCuti->kuota_hari === null
            || $jenisCuti->kuota_hari < 0
        ) {
            throw new InvalidArgumentException(
                'Kuota dasar tahunan tidak valid.'
            );
        }

        if ($jenisCuti->minimal_masa_kerja_bulan < 0) {
            throw new InvalidArgumentException(
                'Minimal masa kerja tidak valid.'
            );
        }

        if (!$jenisCuti->bisa_akumulasi) {
            return;
        }

        if ($jenisCuti->tambahan_akumulasi_hari < 0) {
            throw new InvalidArgumentException(
                'Tambahan akumulasi tidak valid.'
            );
        }

        if ($jenisCuti->maksimal_tahun_akumulasi < 1) {
            throw new InvalidArgumentException(
                'Maksimal tahun akumulasi harus lebih dari nol.'
            );
        }

        if ($jenisCuti->tahun_awal_siklus === null) {
            throw new InvalidArgumentException(
                'Tahun awal siklus wajib ditentukan.'
            );
        }

        $this->validasiTahun(
            $jenisCuti->tahun_awal_siklus
        );
    }

    public function hitungPanjangSiklus(
        JenisCuti $jenisCuti
    ): int {
        $this->validasiKebijakan($jenisCuti);

        if (!$jenisCuti->bisa_akumulasi) {
            return 1;
        }

        return $jenisCuti->maksimal_tahun_akumulasi + 1;
    }

    public function hitungTahunKeSiklus(
        JenisCuti $jenisCuti,
        int $tahun
    ): ?int {
        $this->validasiTahun($tahun);
        $this->validasiKebijakan($jenisCuti);

        if (!$jenisCuti->bisa_akumulasi) {
            return null;
        }

        $tahunAwal = $jenisCuti->tahun_awal_siklus;

        if ($tahun < $tahunAwal) {
            return null;
        }

        $panjangSiklus = $this->hitungPanjangSiklus(
            $jenisCuti
        );

        return (($tahun - $tahunAwal) % $panjangSiklus) + 1;
    }

    public function isAwalSiklus(
        JenisCuti $jenisCuti,
        int $tahun
    ): bool {
        return $this->hitungTahunKeSiklus(
            $jenisCuti,
            $tahun
        ) === 1;
    }

    public function tanggalMemenuhiMasaKerja(
        Pegawai $pegawai,
        JenisCuti $jenisCuti
    ): ?CarbonImmutable {
        if ($pegawai->tanggal_masuk === null) {
            return null;
        }

        return CarbonImmutable::parse(
            $pegawai->tanggal_masuk
        )->startOfDay()->addMonthsNoOverflow(
            (int) $jenisCuti->minimal_masa_kerja_bulan
        );
    }

    public function memenuhiMasaKerja(
        Pegawai $pegawai,
        JenisCuti $jenisCuti,
        $tanggal
    ): bool {
        $tanggalMemenuhi = $this->tanggalMemenuhiMasaKerja(
            $pegawai,
            $jenisCuti
        );

        if ($tanggalMemenuhi === null) {
            return false;
        }

        return CarbonImmutable::parse($tanggal)
            ->startOfDay()
            ->greaterThanOrEqualTo($tanggalMemenuhi);
    }

    public function ambilKuotaTahunSebelumnya(
        Pegawai $pegawai,
        JenisCuti $jenisCuti,
        int $tahun
    ): ?KuotaCuti {
        $this->validasiTahun($tahun);

        if (!$pegawai->exists || !$jenisCuti->exists) {
            throw new LogicException(
                'Pegawai dan jenis cuti harus tersimpan di database.'
            );
        }

        return KuotaCuti::query()
            ->pegawai($pegawai->getKey())
            ->jenisCuti($jenisCuti->getKey())
            ->tahun($tahun - 1)
            ->where('kode_periode', (string) ($tahun - 1))
            ->first();
    }

    public function hitung(
        Pegawai $pegawai,
        JenisCuti $jenisCuti,
        int $tahun,
        ?KuotaCuti $kuotaSebelumnya = null
    ): array {
        $this->validasiTahun($tahun);
        $this->validasiKebijakan($jenisCuti);

        $awalTahun = CarbonImmutable::create(
            $tahun,
            1,
            1
        )->startOfDay();

        $akhirTahun = $awalTahun->endOfYear()->startOfDay();

        $tanggalMemenuhi = $this->tanggalMemenuhiMasaKerja(
            $pegawai,
            $jenisCuti
        );

        $tahunKeSiklus = $this->hitungTahunKeSiklus(
            $jenisCuti,
            $tahun
        );

        $hasil = [
            'tahun' => $tahun,
            'berhak_kuota' => false,
            'berhak_akumulasi' => false,
            'kuota_dasar' => 0,
            'akumulasi_sebelumnya' => 0,
            'tambahan_akumulasi' => 0,
            'kuota_akumulasi' => 0,
            'total_kuota' => 0,
            'tahun_ke_siklus' => $tahunKeSiklus,
            'tanggal_memenuhi_masa_kerja' =>
            $tanggalMemenuhi?->toDateString(),
            'tanggal_mulai_berlaku' => null,
            'kuota_sebelumnya_id' => null,
            'perlu_evaluasi_ulang' => false,
            'kode_alasan' => null,
            'keterangan' => null,
        ];

        if ($pegawai->status !== 'aktif') {
            return $this->denganAlasan(
                $hasil,
                'pegawai_tidak_aktif',
                'Pegawai tidak berstatus aktif.'
            );
        }

        if (!$jenisCuti->is_active) {
            return $this->denganAlasan(
                $hasil,
                'jenis_cuti_tidak_aktif',
                'Jenis cuti tidak berstatus aktif.'
            );
        }

        if ($tanggalMemenuhi === null) {
            return $this->denganAlasan(
                $hasil,
                'tanggal_masuk_kosong',
                'Tanggal masuk pegawai belum tersedia.'
            );
        }

        if ($tanggalMemenuhi->greaterThan($akhirTahun)) {
            return $this->denganAlasan(
                $hasil,
                'masa_kerja_belum_memenuhi',
                'Minimal masa kerja belum terpenuhi pada periode ini.'
            );
        }

        $tanggalMulaiBerlaku = $tanggalMemenuhi
            ->greaterThan($awalTahun)
            ? $tanggalMemenuhi
            : $awalTahun;

        $hasil['berhak_kuota'] = true;
        $hasil['kuota_dasar'] = (int) $jenisCuti->kuota_hari;
        $hasil['total_kuota'] = $hasil['kuota_dasar'];
        $hasil['tanggal_mulai_berlaku'] =
            $tanggalMulaiBerlaku->toDateString();

        if (!$jenisCuti->bisa_akumulasi) {
            return $this->denganAlasan(
                $hasil,
                'akumulasi_tidak_aktif',
                'Jenis cuti tidak menggunakan akumulasi.'
            );
        }

        if ($tahunKeSiklus === null) {
            return $this->denganAlasan(
                $hasil,
                'sebelum_awal_siklus',
                'Periode berada sebelum tahun awal siklus.'
            );
        }

        if ($tahunKeSiklus === 1) {
            return $this->denganAlasan(
                $hasil,
                'awal_siklus',
                'Awal siklus baru, akumulasi kembali nol.'
            );
        }

        if ($kuotaSebelumnya === null) {
            return $this->denganAlasan(
                $hasil,
                'tidak_ada_kuota_sebelumnya',
                'Tidak terdapat kuota pada tahun sebelumnya.'
            );
        }

        $this->validasiKuotaSebelumnya(
            $pegawai,
            $jenisCuti,
            $tahun,
            $kuotaSebelumnya
        );

        $hasil['kuota_sebelumnya_id'] =
            $kuotaSebelumnya->getKey();

        if ($kuotaSebelumnya->kuota_terpakai > 0) {
            return $this->denganAlasan(
                $hasil,
                'cuti_sebelumnya_terpakai',
                'Cuti tahunan sebelumnya pernah digunakan.'
            );
        }

        if ($kuotaSebelumnya->kuota_direservasi > 0) {
            $hasil['perlu_evaluasi_ulang'] = true;

            return $this->denganAlasan(
                $hasil,
                'reservasi_sebelumnya_belum_selesai',
                'Masih terdapat reservasi pada periode sebelumnya.'
            );
        }

        $snapshotSebelumnya =
            $kuotaSebelumnya->kebijakan_snapshot ?? [];

        if (
            !data_get(
                $snapshotSebelumnya,
                'bisa_akumulasi',
                false
            )
        ) {
            return $this->denganAlasan(
                $hasil,
                'periode_sebelumnya_tidak_akumulatif',
                'Kebijakan periode sebelumnya tidak mendukung akumulasi.'
            );
        }

        $akhirTahunSebelumnya = $awalTahun
            ->subDay()
            ->startOfDay();

        if (
            $tanggalMemenuhi->greaterThan(
                $akhirTahunSebelumnya
            )
        ) {
            return $this->denganAlasan(
                $hasil,
                'masa_kerja_periode_sebelumnya',
                'Pegawai belum memenuhi masa kerja pada tahun sebelumnya.'
            );
        }

        $akumulasiSebelumnya = max(
            0,
            (int) $kuotaSebelumnya->kuota_akumulasi
        );

        $tambahan = (int) $jenisCuti->tambahan_akumulasi_hari;

        $hasil['berhak_akumulasi'] = true;
        $hasil['akumulasi_sebelumnya'] =
            $akumulasiSebelumnya;

        $hasil['tambahan_akumulasi'] = $tambahan;

        $hasil['kuota_akumulasi'] =
            $akumulasiSebelumnya + $tambahan;

        $hasil['total_kuota'] =
            $hasil['kuota_dasar']
            + $hasil['kuota_akumulasi'];

        return $this->denganAlasan(
            $hasil,
            'memenuhi_akumulasi',
            'Pegawai memenuhi ketentuan akumulasi cuti tahunan.'
        );
    }

    public function validasiKuotaSebelumnya(
        Pegawai $pegawai,
        JenisCuti $jenisCuti,
        int $tahun,
        KuotaCuti $kuotaSebelumnya
    ): void {
        if (
            (int) $kuotaSebelumnya->pegawai_id
            !== (int) $pegawai->getKey()
        ) {
            throw new InvalidArgumentException(
                'Kuota sebelumnya bukan milik pegawai tersebut.'
            );
        }

        if (
            (int) $kuotaSebelumnya->jenis_cuti_id
            !== (int) $jenisCuti->getKey()
        ) {
            throw new InvalidArgumentException(
                'Jenis kuota sebelumnya tidak sesuai.'
            );
        }

        if (
            (int) $kuotaSebelumnya->tahun !== $tahun - 1
            || $kuotaSebelumnya->kode_periode
            !== (string) ($tahun - 1)
        ) {
            throw new InvalidArgumentException(
                'Kuota sebelumnya bukan periode tahun sebelumnya.'
            );
        }

        if ($kuotaSebelumnya->tanpa_batas) {
            throw new InvalidArgumentException(
                'Kuota sebelumnya tidak boleh berupa kuota tanpa batas.'
            );
        }

        app(MutasiKuotaCutiService::class)->validasiSnapshot(
            $kuotaSebelumnya->snapshotSaldo()
        );
    }

    public function hitungDariDatabase(
        Pegawai $pegawai,
        JenisCuti $jenisCuti,
        int $tahun
    ): array {
        $kuotaSebelumnya = $this->ambilKuotaTahunSebelumnya(
            $pegawai,
            $jenisCuti,
            $tahun
        );

        return $this->hitung(
            $pegawai,
            $jenisCuti,
            $tahun,
            $kuotaSebelumnya
        );
    }

    private function denganAlasan(
        array $hasil,
        string $kode,
        string $keterangan
    ): array {
        $hasil['kode_alasan'] = $kode;
        $hasil['keterangan'] = $keterangan;

        return $hasil;
    }
}
