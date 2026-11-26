<?php

namespace App\Services\Cuti;

use App\Models\KuotaCuti;
use App\Models\MutasiKuotaCuti;
use App\Models\PengajuanCuti;
use App\Models\PersetujuanCuti;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use LogicException;

class MutasiKuotaCutiService
{
    public function buatKodeMutasi(
        string $jenisMutasi,
        int $kuotaId,
        string $referensi
    ): string {
        $this->validasiJenisMutasi($jenisMutasi);

        if ($kuotaId <= 0 || trim($referensi) === '') {
            throw new InvalidArgumentException(
                'Referensi mutasi tidak valid.'
            );
        }

        return strtoupper($jenisMutasi)
            . '-'
            . hash(
                'sha256',
                $jenisMutasi . '|' . $kuotaId . '|' . $referensi
            );
    }

    public function snapshotAwal(
        bool $tanpaBatas = false
    ): array {
        return [
            'kuota_dasar' => $tanpaBatas ? null : 0,
            'kuota_akumulasi' => 0,
            'kuota_penyesuaian' => 0,
            'kuota_terpakai' => 0,
            'kuota_direservasi' => 0,
            'kuota_hangus' => 0,
            'total_kuota' => $tanpaBatas ? null : 0,
            'sisa_kuota' => $tanpaBatas ? null : 0,
            'tanpa_batas' => $tanpaBatas,
        ];
    }

    public function hitungPerubahanSisa(
        array $saldoSebelum,
        array $saldoSesudah
    ): ?int {
        $this->validasiSnapshot($saldoSebelum);
        $this->validasiSnapshot($saldoSesudah);

        if (
            $saldoSebelum['tanpa_batas']
            !== $saldoSesudah['tanpa_batas']
        ) {
            throw new InvalidArgumentException(
                'Jenis batas kuota tidak boleh berubah dalam satu mutasi.'
            );
        }

        if ($saldoSesudah['tanpa_batas']) {
            return null;
        }

        return $saldoSesudah['sisa_kuota']
            - $saldoSebelum['sisa_kuota'];
    }

    public function validasiSnapshot(array $saldo): void
    {
        $kolom = [
            'kuota_dasar',
            'kuota_akumulasi',
            'kuota_penyesuaian',
            'kuota_terpakai',
            'kuota_direservasi',
            'kuota_hangus',
            'total_kuota',
            'sisa_kuota',
            'tanpa_batas',
        ];

        foreach ($kolom as $key) {
            if (!array_key_exists($key, $saldo)) {
                throw new InvalidArgumentException(
                    "Snapshot saldo tidak memiliki atribut {$key}."
                );
            }
        }

        if (!is_bool($saldo['tanpa_batas'])) {
            throw new InvalidArgumentException(
                'Atribut tanpa_batas harus berupa boolean.'
            );
        }

        $kolomNonNegatif = [
            'kuota_akumulasi',
            'kuota_terpakai',
            'kuota_direservasi',
            'kuota_hangus',
        ];

        foreach ($kolomNonNegatif as $key) {
            if (
                !is_int($saldo[$key])
                || $saldo[$key] < 0
            ) {
                throw new InvalidArgumentException(
                    "Nilai {$key} tidak valid."
                );
            }
        }

        if (!is_int($saldo['kuota_penyesuaian'])) {
            throw new InvalidArgumentException(
                'Kuota penyesuaian harus berupa bilangan bulat.'
            );
        }

        if ($saldo['tanpa_batas']) {
            if (
                $saldo['kuota_dasar'] !== null
                || $saldo['total_kuota'] !== null
                || $saldo['sisa_kuota'] !== null
                || $saldo['kuota_akumulasi'] !== 0
                || $saldo['kuota_penyesuaian'] !== 0
                || $saldo['kuota_hangus'] !== 0
            ) {
                throw new InvalidArgumentException(
                    'Snapshot kuota tanpa batas tidak valid.'
                );
            }

            return;
        }

        if (
            !is_int($saldo['kuota_dasar'])
            || $saldo['kuota_dasar'] < 0
        ) {
            throw new InvalidArgumentException(
                'Kuota dasar tidak valid.'
            );
        }

        $total = $saldo['kuota_dasar']
            + $saldo['kuota_akumulasi']
            + $saldo['kuota_penyesuaian'];

        $sisa = $total
            - $saldo['kuota_terpakai']
            - $saldo['kuota_direservasi']
            - $saldo['kuota_hangus'];

        if ($total < 0 || $sisa < 0) {
            throw new InvalidArgumentException(
                'Saldo kuota tidak boleh negatif.'
            );
        }

        if ($saldo['total_kuota'] !== $total) {
            throw new InvalidArgumentException(
                'Total kuota tidak sesuai dengan komponennya.'
            );
        }

        if ($saldo['sisa_kuota'] !== $sisa) {
            throw new InvalidArgumentException(
                'Sisa kuota tidak sesuai dengan komponennya.'
            );
        }
    }

    public function validasiJenisMutasi(
        string $jenisMutasi
    ): void {
        $jenisDiizinkan = [
            MutasiKuotaCuti::JENIS_PEMBERIAN_KUOTA,
            MutasiKuotaCuti::JENIS_AKUMULASI,
            MutasiKuotaCuti::JENIS_RESERVASI,
            MutasiKuotaCuti::JENIS_PELEPASAN_RESERVASI,
            MutasiKuotaCuti::JENIS_PENGGUNAAN,
            MutasiKuotaCuti::JENIS_PENYESUAIAN_TAMBAH,
            MutasiKuotaCuti::JENIS_PENYESUAIAN_KURANG,
            MutasiKuotaCuti::JENIS_KEDALUWARSA,
            MutasiKuotaCuti::JENIS_RESET_AKUMULASI,
            MutasiKuotaCuti::JENIS_KOREKSI,
        ];

        if (!in_array($jenisMutasi, $jenisDiizinkan, true)) {
            throw new InvalidArgumentException(
                'Jenis mutasi kuota tidak valid.'
            );
        }
    }

    public function validasiPerubahan(
        string $jenisMutasi,
        int $jumlahHari,
        array $saldoSebelum,
        array $saldoSesudah
    ): void {
        $this->validasiJenisMutasi($jenisMutasi);

        $this->hitungPerubahanSisa(
            $saldoSebelum,
            $saldoSesudah
        );

        if ($jumlahHari < 0) {
            throw new InvalidArgumentException(
                'Jumlah hari mutasi tidak boleh negatif.'
            );
        }

        if (
            $jumlahHari === 0
            && $jenisMutasi !== MutasiKuotaCuti::JENIS_PEMBERIAN_KUOTA
        ) {
            throw new InvalidArgumentException(
                'Jumlah hari mutasi harus lebih dari nol.'
            );
        }

        $tanpaBatas = $saldoSebelum['tanpa_batas'];

        if (
            $tanpaBatas
            && !in_array($jenisMutasi, [
                MutasiKuotaCuti::JENIS_PEMBERIAN_KUOTA,
                MutasiKuotaCuti::JENIS_RESERVASI,
                MutasiKuotaCuti::JENIS_PELEPASAN_RESERVASI,
                MutasiKuotaCuti::JENIS_PENGGUNAAN,
                MutasiKuotaCuti::JENIS_KOREKSI,
            ], true)
        ) {
            throw new InvalidArgumentException(
                'Jenis mutasi tidak berlaku untuk kuota tanpa batas.'
            );
        }

        $komponen = [
            'kuota_dasar',
            'kuota_akumulasi',
            'kuota_penyesuaian',
            'kuota_terpakai',
            'kuota_direservasi',
            'kuota_hangus',
        ];

        $perubahan = [];

        foreach ($komponen as $key) {
            $perubahan[$key] =
                (int) $saldoSesudah[$key]
                - (int) $saldoSebelum[$key];
        }

        $perubahanDiharapkan = match ($jenisMutasi) {
            MutasiKuotaCuti::JENIS_PEMBERIAN_KUOTA => [
                'kuota_dasar' => $tanpaBatas ? 0 : $jumlahHari,
            ],

            MutasiKuotaCuti::JENIS_AKUMULASI => [
                'kuota_akumulasi' => $jumlahHari,
            ],

            MutasiKuotaCuti::JENIS_RESERVASI => [
                'kuota_direservasi' => $jumlahHari,
            ],

            MutasiKuotaCuti::JENIS_PELEPASAN_RESERVASI => [
                'kuota_direservasi' => -$jumlahHari,
            ],

            MutasiKuotaCuti::JENIS_PENGGUNAAN => [
                'kuota_direservasi' => -$jumlahHari,
                'kuota_terpakai' => $jumlahHari,
            ],

            MutasiKuotaCuti::JENIS_PENYESUAIAN_TAMBAH => [
                'kuota_penyesuaian' => $jumlahHari,
            ],

            MutasiKuotaCuti::JENIS_PENYESUAIAN_KURANG => [
                'kuota_penyesuaian' => -$jumlahHari,
            ],

            MutasiKuotaCuti::JENIS_KEDALUWARSA => [
                'kuota_hangus' => $jumlahHari,
            ],

            MutasiKuotaCuti::JENIS_RESET_AKUMULASI => [
                'kuota_akumulasi' => -$jumlahHari,
            ],

            MutasiKuotaCuti::JENIS_KOREKSI => null,
        };

        if (
            $tanpaBatas
            && $jenisMutasi === MutasiKuotaCuti::JENIS_PEMBERIAN_KUOTA
            && $jumlahHari !== 0
        ) {
            throw new InvalidArgumentException(
                'Pemberian kuota tanpa batas harus menggunakan nol hari.'
            );
        }

        if ($perubahanDiharapkan === null) {
            throw new LogicException(
                'Mutasi koreksi memerlukan mekanisme khusus.'
            );
        }

        foreach ($komponen as $key) {
            $nilaiDiharapkan = $perubahanDiharapkan[$key] ?? 0;

            if ($perubahan[$key] !== $nilaiDiharapkan) {
                throw new InvalidArgumentException(
                    "Perubahan {$key} tidak sesuai dengan jenis mutasi."
                );
            }
        }
    }

    public function sudahTercatat(string $kodeMutasi): bool
    {
        return MutasiKuotaCuti::query()
            ->where('kode_mutasi', $kodeMutasi)
            ->exists();
    }

    public function catat(
        KuotaCuti $kuota,
        string $jenisMutasi,
        int $jumlahHari,
        array $saldoSebelum,
        string $kodeMutasi,
        string $keterangan,
        ?int $userId = null,
        ?int $pengajuanId = null,
        ?int $persetujuanId = null,
        ?array $metadata = null
    ): MutasiKuotaCuti {
        if (DB::transactionLevel() < 1) {
            throw new LogicException(
                'Pencatatan mutasi harus berada dalam database transaction.'
            );
        }

        if (!$kuota->exists || $kuota->isDirty()) {
            throw new LogicException(
                'Saldo kuota harus sudah disimpan sebelum mutasi dicatat.'
            );
        }

        if (
            trim($kodeMutasi) === ''
            || strlen($kodeMutasi) > 100
        ) {
            throw new InvalidArgumentException(
                'Kode mutasi tidak valid.'
            );
        }

        if (trim($keterangan) === '') {
            throw new InvalidArgumentException(
                'Keterangan mutasi wajib diisi.'
            );
        }

        $kuotaTersimpan = KuotaCuti::query()
            ->lockForUpdate()
            ->findOrFail($kuota->getKey());

        $saldoSesudah = $kuotaTersimpan->snapshotSaldo();

        if ($kuota->snapshotSaldo() !== $saldoSesudah) {
            throw new LogicException(
                'Saldo kuota tidak sesuai dengan data terbaru.'
            );
        }

        if ($this->sudahTercatat($kodeMutasi)) {
            throw new LogicException(
                'Kode mutasi sudah pernah digunakan.'
            );
        }

        $mutasiTerakhir = MutasiKuotaCuti::query()
            ->where('kuota_cuti_id', $kuota->getKey())
            ->orderByDesc('id')
            ->first();

        if ($mutasiTerakhir) {
            if ($saldoSebelum != $mutasiTerakhir->saldo_sesudah) {
                throw new LogicException(
                    'Saldo sebelum tidak sesuai dengan riwayat mutasi terakhir.'
                );
            }

            if (
                $jenisMutasi
                === MutasiKuotaCuti::JENIS_PEMBERIAN_KUOTA
            ) {
                throw new LogicException(
                    'Pemberian kuota awal sudah pernah dicatat.'
                );
            }
        } else {
            if (
                $jenisMutasi
                !== MutasiKuotaCuti::JENIS_PEMBERIAN_KUOTA
            ) {
                throw new LogicException(
                    'Mutasi pertama harus berupa pemberian kuota.'
                );
            }

            if (
                $saldoSebelum
                != $this->snapshotAwal($saldoSesudah['tanpa_batas'])
            ) {
                throw new LogicException(
                    'Saldo awal pemberian kuota tidak valid.'
                );
            }
        }

        $this->validasiPerubahan(
            $jenisMutasi,
            $jumlahHari,
            $saldoSebelum,
            $saldoSesudah
        );

        $jenisMemerlukanPengajuan = [
            MutasiKuotaCuti::JENIS_RESERVASI,
            MutasiKuotaCuti::JENIS_PELEPASAN_RESERVASI,
            MutasiKuotaCuti::JENIS_PENGGUNAAN,
        ];

        if (
            in_array($jenisMutasi, $jenisMemerlukanPengajuan, true)
            && $pengajuanId === null
        ) {
            throw new InvalidArgumentException(
                'Mutasi ini wajib memiliki referensi pengajuan cuti.'
            );
        }

        if ($pengajuanId !== null) {
            $pengajuan = PengajuanCuti::query()
                ->findOrFail($pengajuanId);

            if (
                (int) $pengajuan->pegawai_id
                !== (int) $kuota->pegawai_id
                || (int) $pengajuan->jenis_cuti_id
                !== (int) $kuota->jenis_cuti_id
            ) {
                throw new InvalidArgumentException(
                    'Pengajuan tidak sesuai dengan pemilik atau jenis kuota.'
                );
            }
        }

        if ($persetujuanId !== null) {
            $persetujuan = PersetujuanCuti::query()
                ->findOrFail($persetujuanId);

            if (
                $pengajuanId === null
                || (int) $persetujuan->pengajuan_cuti_id
                !== $pengajuanId
            ) {
                throw new InvalidArgumentException(
                    'Persetujuan tidak sesuai dengan pengajuan cuti.'
                );
            }
        }

        if (
            $jenisMutasi === MutasiKuotaCuti::JENIS_PENGGUNAAN
            && (
                !isset($persetujuan)
                || $persetujuan->role_approver !== 'direktur'
                || !$persetujuan->isDisetujui()
            )
        ) {
            throw new InvalidArgumentException(
                'Penggunaan kuota memerlukan persetujuan akhir Direktur.'
            );
        }

        return MutasiKuotaCuti::query()->create([
            'kuota_cuti_id' => $kuota->getKey(),
            'pengajuan_cuti_id' => $pengajuanId,
            'persetujuan_cuti_id' => $persetujuanId,
            'user_id' => $userId,
            'kode_mutasi' => $kodeMutasi,
            'jenis_mutasi' => $jenisMutasi,
            'jumlah_hari' => $jumlahHari,
            'perubahan_sisa' => $this->hitungPerubahanSisa(
                $saldoSebelum,
                $saldoSesudah
            ),
            'saldo_sebelum' => $saldoSebelum,
            'saldo_sesudah' => $saldoSesudah,
            'keterangan' => trim($keterangan),
            'metadata' => $metadata,
            'terjadi_pada' => now(),
        ]);
    }
}
