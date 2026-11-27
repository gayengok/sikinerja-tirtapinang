<?php

namespace App\Services\Cuti;

use App\Models\DetailPengajuanCuti;
use App\Models\KuotaCuti;
use App\Models\MutasiKuotaCuti;
use App\Models\Pegawai;
use App\Models\PengajuanCuti;
use App\Models\SkCuti;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use LogicException;

class PelepasanReservasiPembatalanCutiService
{
    public function __construct(
        protected KuotaCutiService $kuotaService,
        protected MutasiKuotaCutiService $mutasiService
    ) {}

    public function lepaskan(
        int $pengajuanId,
        int $userId,
        string $alasan
    ): array {
        if (DB::transactionLevel() === 0) {
            throw new LogicException(
                'Pelepasan reservasi pembatalan wajib berada dalam transaksi pembatalan pengajuan.'
            );
        }

        $alasan = trim($alasan);

        if ($alasan === '' || strlen($alasan) > 65535) {
            throw new InvalidArgumentException(
                'Alasan pembatalan wajib diisi dan tidak boleh melebihi kapasitas penyimpanan.'
            );
        }

        return DB::transaction(function () use (
            $pengajuanId,
            $userId,
            $alasan
        ) {
            $user = User::query()->findOrFail($userId);

            $pegawai = Pegawai::query()
                ->where('user_id', $userId)
                ->lockForUpdate()
                ->firstOrFail();

            $pengajuan = PengajuanCuti::query()
                ->lockForUpdate()
                ->findOrFail($pengajuanId);

            $this->validasiPemohon(
                $user,
                $pegawai,
                $pengajuan
            );

            $this->validasiPengajuan($pengajuan);

            $details = DetailPengajuanCuti::query()
                ->where('pengajuan_cuti_id', $pengajuanId)
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            if ($details->isEmpty()) {
                throw new LogicException(
                    'Pengajuan tidak memiliki detail tanggal cuti.'
                );
            }

            $detailDihitung = $details->filter(
                fn($detail) => $detail->dihitung_kuota
            );

            if (
                $detailDihitung->count()
                !== (int) $pengajuan->jumlah_hari_diajukan
            ) {
                throw new LogicException(
                    'Jumlah hari pengajuan tidak sesuai dengan detail tanggal.'
                );
            }

            foreach ($details as $detail) {
                $this->validasiDetail(
                    $detail,
                    $pengajuan
                );
            }

            $detailPending = $detailDihitung->filter(
                fn($detail) => $detail->bisaDiproses()
            );

            if ($detailPending->isEmpty()) {
                throw new LogicException(
                    'Tidak terdapat reservasi aktif yang dapat dibatalkan.'
                );
            }

            $kelompokKuota = $detailDihitung
                ->groupBy('kuota_cuti_id')
                ->sortKeys();

            $hasil = [];

            foreach ($kelompokKuota as $kuotaId => $daftarDetail) {
                $kuota = KuotaCuti::query()
                    ->lockForUpdate()
                    ->findOrFail($kuotaId);

                $this->validasiKuota(
                    $kuota,
                    $pengajuan
                );

                foreach ($daftarDetail as $detail) {
                    $this->validasiTanggalKuota(
                        $kuota,
                        $detail
                    );
                }

                $daftarPending = $daftarDetail->filter(
                    fn($detail) => $detail->bisaDiproses()
                );

                $tanggal = $daftarPending
                    ->map(
                        fn($detail) =>
                        $detail->tanggal->toDateString()
                    )
                    ->sort()
                    ->values()
                    ->all();

                $detailIds = $daftarPending
                    ->pluck('id')
                    ->map(fn($id) => (int) $id)
                    ->sort()
                    ->values()
                    ->all();

                $jumlahHari = count($detailIds);

                $tanggalReservasi = $this->ambilTanggalReservasi(
                    $kuota->getKey(),
                    $pengajuanId
                );

                if ($tanggalReservasi !== $tanggal) {
                    throw new LogicException(
                        'Tanggal reservasi aktif tidak sesuai dengan detail pengajuan.'
                    );
                }

                $jumlahReservasi = $this->kuotaService
                    ->jumlahReservasiPengajuan(
                        $kuota->getKey(),
                        $pengajuanId
                    );

                if ($jumlahReservasi !== $jumlahHari) {
                    throw new LogicException(
                        'Jumlah reservasi pengajuan tidak konsisten.'
                    );
                }

                if ($jumlahHari === 0) {
                    continue;
                }

                if ($kuota->kuota_direservasi < $jumlahHari) {
                    throw new LogicException(
                        'Saldo reservasi kuota tidak mencukupi.'
                    );
                }

                $kodeMutasi = $this->mutasiService
                    ->buatKodeMutasi(
                        MutasiKuotaCuti::JENIS_PELEPASAN_RESERVASI,
                        $kuota->getKey(),
                        'pembatalan-pengajuan-' . $pengajuanId
                    );

                $mutasiExisting = MutasiKuotaCuti::query()
                    ->where('kode_mutasi', $kodeMutasi)
                    ->lockForUpdate()
                    ->first();

                if ($mutasiExisting !== null) {
                    throw new LogicException(
                        'Pelepasan reservasi pembatalan sudah pernah tercatat.'
                    );
                }

                $saldoSebelum = $kuota->snapshotSaldo();

                $this->mutasiService->validasiSnapshot(
                    $saldoSebelum
                );

                $kuota->kuota_direservasi -= $jumlahHari;

                $kuota->save();

                $mutasi = $this->mutasiService->catat(
                    kuota: $kuota,
                    jenisMutasi: MutasiKuotaCuti::JENIS_PELEPASAN_RESERVASI,
                    jumlahHari: $jumlahHari,
                    saldoSebelum: $saldoSebelum,
                    kodeMutasi: $kodeMutasi,
                    keterangan: "Pengembalian reservasi akibat pembatalan pengajuan cuti #{$pengajuanId}.",
                    userId: $userId,
                    pengajuanId: $pengajuanId,
                    persetujuanId: null,
                    metadata: [
                        'sumber' => 'pembatalan_pengajuan',
                        'alasan_pembatalan' => $alasan,
                        'detail_ids' => $detailIds,
                        'tanggal' => $tanggal,
                        'kode_periode' => $kuota->kode_periode,
                        'status_pengajuan_sebelum' =>
                        $pengajuan->status,
                    ]
                );

                foreach ($daftarPending as $detail) {
                    $detail->update([
                        'status' =>
                        DetailPengajuanCuti::STATUS_CANCELLED,
                    ]);
                }

                $hasil[] = [
                    'kuota_id' => $kuota->getKey(),
                    'mutasi_id' => $mutasi->getKey(),
                    'kode_periode' => $kuota->kode_periode,
                    'jumlah_hari' => $jumlahHari,
                    'detail_ids' => $detailIds,
                    'tanggal' => $tanggal,
                    'sudah_tercatat' => false,
                    'saldo' => $kuota->snapshotSaldo(),
                ];
            }

            $this->validasiSeluruhReservasiSelesai(
                $pengajuanId
            );

            return [
                'pengajuan_cuti_id' => $pengajuanId,
                'pegawai_id' => $pegawai->getKey(),
                'user_id' => $userId,
                'jumlah_hari_dilepaskan' =>
                array_sum(array_column($hasil, 'jumlah_hari')),
                'jumlah_kuota' => count($hasil),
                'pelepasan' => $hasil,
            ];
        });
    }

    protected function validasiPemohon(
        User $user,
        Pegawai $pegawai,
        PengajuanCuti $pengajuan
    ): void {
        if (
            !in_array($user->role, [
                'pegawai',
                'atasan',
                'kepala_bagian',
            ], true)
        ) {
            throw new LogicException(
                'Akun tidak memiliki kewenangan membatalkan pengajuan cuti.'
            );
        }

        if ($pegawai->status !== 'aktif') {
            throw new LogicException(
                'Pegawai tidak berstatus aktif.'
            );
        }

        if (
            (int) $pegawai->getKey()
            !== (int) $pengajuan->pegawai_id
        ) {
            throw new LogicException(
                'Pengajuan cuti bukan milik pemohon.'
            );
        }
    }

    protected function validasiPengajuan(
        PengajuanCuti $pengajuan
    ): void {
        if (!$pengajuan->isPending()) {
            throw new LogicException(
                'Hanya pengajuan cuti yang masih dalam proses persetujuan yang dapat dibatalkan.'
            );
        }

        if (
            $pengajuan->diajukan_pada === null
            || $pengajuan->tanggal_pengajuan === null
            || blank($pengajuan->nomor_pengajuan)
            || $pengajuan->current_approver_id === null
        ) {
            throw new LogicException(
                'Data pengajuan yang telah disubmit tidak lengkap.'
            );
        }

        if (
            $pengajuan->dibatalkan_pada !== null
            || $pengajuan->diselesaikan_pada !== null
            || (int) $pengajuan->jumlah_hari_disetujui !== 0
        ) {
            throw new LogicException(
                'Status penyelesaian pengajuan tidak konsisten.'
            );
        }

        $sk = SkCuti::query()
            ->where('pengajuan_cuti_id', $pengajuan->getKey())
            ->lockForUpdate()
            ->first();

        if ($sk !== null) {
            throw new LogicException(
                'Pengajuan yang telah memiliki data SK cuti tidak dapat dibatalkan.'
            );
        }
    }

    protected function validasiDetail(
        DetailPengajuanCuti $detail,
        PengajuanCuti $pengajuan
    ): void {
        if (
            $detail->tanggal->lessThan(
                $pengajuan->tanggal_mulai
            )
            || $detail->tanggal->greaterThan(
                $pengajuan->tanggal_selesai
            )
        ) {
            throw new LogicException(
                'Detail tanggal berada di luar rentang pengajuan.'
            );
        }

        if (!$detail->dihitung_kuota) {
            if (
                !$detail->isExcluded()
                || $detail->kuota_cuti_id !== null
            ) {
                throw new LogicException(
                    'Detail tanggal yang tidak dihitung tidak konsisten.'
                );
            }

            return;
        }

        if ($detail->kuota_cuti_id === null) {
            throw new LogicException(
                'Detail tanggal belum memiliki referensi kuota.'
            );
        }

        if (
            !in_array($detail->status, [
                DetailPengajuanCuti::STATUS_PENDING,
                DetailPengajuanCuti::STATUS_REJECTED,
            ], true)
        ) {
            throw new LogicException(
                'Terdapat detail tanggal yang tidak dapat dibatalkan.'
            );
        }

        if ($detail->disetujui_pada !== null) {
            throw new LogicException(
                'Terdapat detail tanggal yang telah disetujui secara final.'
            );
        }

        if ($detail->isRejected()) {
            if (
                $detail->ditolak_oleh === null
                || $detail->ditolak_pada === null
            ) {
                throw new LogicException(
                    'Riwayat penolakan detail tanggal tidak lengkap.'
                );
            }

            return;
        }

        if (
            $detail->ditolak_oleh !== null
            || $detail->ditolak_pada !== null
        ) {
            throw new LogicException(
                'Detail pending memiliki riwayat penolakan yang tidak konsisten.'
            );
        }
    }

    protected function validasiKuota(
        KuotaCuti $kuota,
        PengajuanCuti $pengajuan
    ): void {
        if (
            (int) $kuota->pegawai_id
            !== (int) $pengajuan->pegawai_id
        ) {
            throw new LogicException(
                'Kuota bukan milik pegawai yang mengajukan cuti.'
            );
        }

        if (
            (int) $kuota->jenis_cuti_id
            !== (int) $pengajuan->jenis_cuti_id
        ) {
            throw new LogicException(
                'Jenis kuota tidak sesuai dengan pengajuan cuti.'
            );
        }

        $this->mutasiService->validasiSnapshot(
            $kuota->snapshotSaldo()
        );
    }

    protected function validasiTanggalKuota(
        KuotaCuti $kuota,
        DetailPengajuanCuti $detail
    ): void {
        if (
            $detail->tanggal->lessThan(
                $kuota->tanggal_mulai_periode
            )
        ) {
            throw new LogicException(
                'Tanggal cuti berada sebelum periode kuota.'
            );
        }

        if (
            $kuota->tanggal_akhir_periode !== null
            && $detail->tanggal->greaterThan(
                $kuota->tanggal_akhir_periode
            )
        ) {
            throw new LogicException(
                'Tanggal cuti berada di luar periode kuota.'
            );
        }
    }

    protected function ambilTanggalReservasi(
        int $kuotaId,
        int $pengajuanId
    ): array {
        $mutasis = MutasiKuotaCuti::query()
            ->where('kuota_cuti_id', $kuotaId)
            ->where('pengajuan_cuti_id', $pengajuanId)
            ->whereIn('jenis_mutasi', [
                MutasiKuotaCuti::JENIS_RESERVASI,
                MutasiKuotaCuti::JENIS_PELEPASAN_RESERVASI,
                MutasiKuotaCuti::JENIS_PENGGUNAAN,
            ])
            ->orderBy('id')
            ->lockForUpdate()
            ->get();

        $tersedia = [];

        foreach ($mutasis as $mutasi) {
            $tanggal = $mutasi->metadata['tanggal'] ?? null;

            if (
                !is_array($tanggal)
                || count($tanggal) !== (int) $mutasi->jumlah_hari
            ) {
                throw new LogicException(
                    'Riwayat tanggal reservasi tidak lengkap.'
                );
            }

            $tanggalMutasi = [];

            foreach ($tanggal as $hari) {
                if (
                    !is_string($hari)
                    || !preg_match('/^\d{4}-\d{2}-\d{2}$/D', $hari)
                ) {
                    throw new LogicException(
                        'Format tanggal riwayat reservasi tidak valid.'
                    );
                }

                [$tahun, $bulan, $tanggalHari] = array_map(
                    'intval',
                    explode('-', $hari)
                );

                if (!checkdate($bulan, $tanggalHari, $tahun)) {
                    throw new LogicException(
                        'Tanggal riwayat reservasi tidak valid.'
                    );
                }

                if (isset($tanggalMutasi[$hari])) {
                    throw new LogicException(
                        'Terdapat tanggal ganda dalam riwayat mutasi.'
                    );
                }

                $tanggalMutasi[$hari] = true;

                if (
                    $mutasi->jenis_mutasi
                    === MutasiKuotaCuti::JENIS_RESERVASI
                ) {
                    if (isset($tersedia[$hari])) {
                        throw new LogicException(
                            'Terdapat reservasi tanggal ganda.'
                        );
                    }

                    $tersedia[$hari] = true;

                    continue;
                }

                if (
                    $mutasi->jenis_mutasi
                    === MutasiKuotaCuti::JENIS_PENGGUNAAN
                ) {
                    throw new LogicException(
                        'Pengajuan telah memiliki mutasi penggunaan kuota.'
                    );
                }

                if (!isset($tersedia[$hari])) {
                    throw new LogicException(
                        'Riwayat pelepasan tanggal reservasi tidak konsisten.'
                    );
                }

                unset($tersedia[$hari]);
            }
        }

        $hasil = array_keys($tersedia);

        sort($hasil);

        return $hasil;
    }

    protected function validasiSeluruhReservasiSelesai(
        int $pengajuanId
    ): void {
        $mutasis = MutasiKuotaCuti::query()
            ->where('pengajuan_cuti_id', $pengajuanId)
            ->whereIn('jenis_mutasi', [
                MutasiKuotaCuti::JENIS_RESERVASI,
                MutasiKuotaCuti::JENIS_PELEPASAN_RESERVASI,
                MutasiKuotaCuti::JENIS_PENGGUNAAN,
            ])
            ->orderBy('kuota_cuti_id')
            ->orderBy('id')
            ->lockForUpdate()
            ->get();

        if (
            $mutasis->contains(
                fn($mutasi) =>
                $mutasi->jenis_mutasi
                    === MutasiKuotaCuti::JENIS_PENGGUNAAN
            )
        ) {
            throw new LogicException(
                'Pengajuan telah memiliki riwayat penggunaan kuota.'
            );
        }

        $kuotaIds = $mutasis
            ->pluck('kuota_cuti_id')
            ->map(fn($id) => (int) $id)
            ->unique()
            ->sort()
            ->values()
            ->all();

        foreach ($kuotaIds as $kuotaId) {
            $jumlah = $this->kuotaService
                ->jumlahReservasiPengajuan(
                    $kuotaId,
                    $pengajuanId
                );

            if ($jumlah !== 0) {
                throw new LogicException(
                    'Masih terdapat saldo reservasi pengajuan yang belum dilepaskan.'
                );
            }

            if (
                $this->ambilTanggalReservasi(
                    $kuotaId,
                    $pengajuanId
                ) !== []
            ) {
                throw new LogicException(
                    'Masih terdapat tanggal reservasi yang belum dilepaskan.'
                );
            }
        }
    }
}