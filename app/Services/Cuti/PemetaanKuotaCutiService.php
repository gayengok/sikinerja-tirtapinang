<?php

namespace App\Services\Cuti;

use App\Models\DetailPengajuanCuti;
use App\Models\JenisCuti;
use App\Models\KuotaCuti;
use App\Models\Pegawai;
use App\Models\PengajuanCuti;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;
use LogicException;

class PemetaanKuotaCutiService
{
    public function __construct(
        protected KuotaCutiService $kuotaService
    ) {}

    public function petakan(
        int $pengajuanId,
        int $userId
    ): array {
        if (DB::transactionLevel() === 0) {
            throw new LogicException(
                'Pemetaan kuota wajib berada dalam transaksi pengajuan cuti.'
            );
        }

        return DB::transaction(function () use (
            $pengajuanId,
            $userId
        ) {
            $user = User::query()
                ->findOrFail($userId);

            $pegawai = Pegawai::query()
                ->where('user_id', $userId)
                ->lockForUpdate()
                ->firstOrFail();

            $pengajuan = PengajuanCuti::query()
                ->lockForUpdate()
                ->findOrFail($pengajuanId);

            $this->validasiPengajuan(
                $user,
                $pegawai,
                $pengajuan
            );

            $jenisCuti = JenisCuti::query()
                ->lockForUpdate()
                ->findOrFail(
                    $pengajuan->jenis_cuti_id
                );

            $this->validasiJenisCuti($jenisCuti);

            $details = DetailPengajuanCuti::query()
                ->where(
                    'pengajuan_cuti_id',
                    $pengajuanId
                )
                ->orderBy('tanggal')
                ->lockForUpdate()
                ->get();

            $this->validasiDetail(
                $pengajuan,
                $details
            );

            $detailDihitung = $details
                ->filter(
                    fn($detail) =>
                    $detail->dihitung_kuota
                );

            $referensiKejadian = null;

            if (
                $jenisCuti->periode_kuota
                === 'per_kejadian'
            ) {
                $referensiKejadian = $this->kuotaService
                    ->buatReferensiKejadian();
            }

            $kelompok = $detailDihitung
                ->groupBy(function ($detail) use (
                    $jenisCuti,
                    $referensiKejadian
                ) {
                    if (
                        $jenisCuti->periode_kuota
                        === 'tahunan'
                    ) {
                        return $detail->tanggal->year;
                    }

                    return $this->kuotaService
                        ->buatKodePeriodeNonTahunan(
                            $jenisCuti,
                            $referensiKejadian
                        );
                })
                ->sortKeys();

            $hasil = [];

            foreach (
                $kelompok as $kodePeriode => $daftar
            ) {
                $kuota = $this->siapkanKuota(
                    $pegawai,
                    $jenisCuti,
                    (string) $kodePeriode,
                    $referensiKejadian,
                    $userId
                );

                $this->validasiKuota(
                    $kuota,
                    $pengajuan,
                    $jenisCuti,
                    $daftar
                );

                foreach ($daftar as $detail) {
                    $detail->kuota_cuti_id =
                        $kuota->getKey();

                    if (!$detail->save()) {
                        throw new LogicException(
                            'Gagal menyimpan pemetaan kuota pada detail cuti.'
                        );
                    }
                }

                $hasil[] = [
                    'kuota_id' =>
                    (int) $kuota->getKey(),
                    'kode_periode' =>
                    $kuota->kode_periode,
                    'tahun' => $kuota->tahun,
                    'jumlah_hari' =>
                    $daftar->count(),
                    'tanggal' => $daftar
                        ->map(
                            fn($detail) =>
                            $detail->tanggal->toDateString()
                        )
                        ->values()
                        ->all(),
                    'detail_ids' => $daftar
                        ->pluck('id')
                        ->map(fn($id) => (int) $id)
                        ->values()
                        ->all(),
                    'tanpa_batas' =>
                    $kuota->tanpa_batas,
                    'saldo' =>
                    $kuota->snapshotSaldo(),
                ];
            }

            return [
                'pengajuan_cuti_id' =>
                (int) $pengajuan->getKey(),
                'pegawai_id' =>
                (int) $pegawai->getKey(),
                'jenis_cuti_id' =>
                (int) $jenisCuti->getKey(),
                'periode_kuota' =>
                $jenisCuti->periode_kuota,
                'referensi_kejadian' =>
                $referensiKejadian,
                'jumlah_hari' =>
                $detailDihitung->count(),
                'jumlah_kuota' =>
                count($hasil),
                'pemetaan' => $hasil,
            ];
        }, 3);
    }

    private function validasiPengajuan(
        User $user,
        Pegawai $pegawai,
        PengajuanCuti $pengajuan
    ): void {
        if (
            (int) $pengajuan->pegawai_id
            !== (int) $pegawai->getKey()
        ) {
            throw ValidationException::withMessages([
                'pengajuan_cuti_id' =>
                'Pengajuan cuti bukan milik pemohon.',
            ]);
        }

        if (
            !in_array(
                $user->role,
                [
                    'pegawai',
                    'atasan',
                    'kepala_bagian',
                ],
                true
            )
            || $pegawai->status !== 'aktif'
        ) {
            throw ValidationException::withMessages([
                'pegawai_id' =>
                'Pemohon tidak berwenang mengajukan cuti.',
            ]);
        }

        if (
            $pengajuan->status
            !== PengajuanCuti::STATUS_DRAFT
        ) {
            throw ValidationException::withMessages([
                'status' =>
                'Pemetaan kuota hanya berlaku untuk draft.',
            ]);
        }

        if (
            $pengajuan->nomor_pengajuan !== null
            || $pengajuan->tanggal_pengajuan !== null
            || $pengajuan->diajukan_pada !== null
            || $pengajuan->current_approver_id !== null
            || $pengajuan->diselesaikan_pada !== null
            || $pengajuan->dibatalkan_pada !== null
            || $pengajuan->alasan_pembatalan !== null
            || (int) $pengajuan->jumlah_hari_disetujui !== 0
        ) {
            throw ValidationException::withMessages([
                'status' =>
                'Draft memiliki riwayat pengajuan yang tidak valid.',
            ]);
        }
    }

    private function validasiJenisCuti(
        JenisCuti $jenisCuti
    ): void {
        if (!$jenisCuti->is_active) {
            throw ValidationException::withMessages([
                'jenis_cuti_id' =>
                'Jenis cuti tidak aktif.',
            ]);
        }

        if (
            !in_array(
                $jenisCuti->periode_kuota,
                [
                    'tahunan',
                    'per_kejadian',
                    'seumur_kerja',
                    'tanpa_batas',
                ],
                true
            )
        ) {
            throw ValidationException::withMessages([
                'jenis_cuti_id' =>
                'Periode kuota tidak valid.',
            ]);
        }
    }

    private function validasiDetail(
        PengajuanCuti $pengajuan,
        $details
    ): void {
        if ($details->isEmpty()) {
            throw ValidationException::withMessages([
                'tanggal_mulai' =>
                'Detail tanggal pengajuan belum tersedia.',
            ]);
        }

        $jumlahKalender = (int) $pengajuan
            ->tanggal_mulai
            ->diffInDays(
                $pengajuan->tanggal_selesai
            ) + 1;

        if (
            $pengajuan->tanggal_mulai->greaterThan(
                $pengajuan->tanggal_selesai
            )
            || $details->count() !== $jumlahKalender
        ) {
            throw ValidationException::withMessages([
                'tanggal_selesai' =>
                'Detail rentang tanggal tidak lengkap.',
            ]);
        }

        $jumlahDihitung = 0;

        foreach ($details as $detail) {
            if (
                $detail->tanggal->lessThan(
                    $pengajuan->tanggal_mulai
                )
                || $detail->tanggal->greaterThan(
                    $pengajuan->tanggal_selesai
                )
            ) {
                throw ValidationException::withMessages([
                    'tanggal' =>
                    'Detail berada di luar rentang pengajuan.',
                ]);
            }

            if (
                $detail->kuota_cuti_id !== null
                || $detail->ditolak_oleh !== null
                || $detail->ditolak_pada !== null
                || $detail->disetujui_pada !== null
            ) {
                throw ValidationException::withMessages([
                    'status' =>
                    'Detail telah memiliki riwayat pemetaan atau pemrosesan.',
                ]);
            }

            if (!$detail->dihitung_kuota) {
                if (
                    $detail->status
                    !== DetailPengajuanCuti::STATUS_EXCLUDED
                ) {
                    throw ValidationException::withMessages([
                        'status' =>
                        'Detail yang tidak dihitung harus berstatus excluded.',
                    ]);
                }

                continue;
            }

            if (
                $detail->status
                !== DetailPengajuanCuti::STATUS_PENDING
            ) {
                throw ValidationException::withMessages([
                    'status' =>
                    'Detail yang dihitung harus berstatus pending.',
                ]);
            }

            $jumlahDihitung++;
        }

        if (
            $jumlahDihitung === 0
            || $jumlahDihitung
            !== (int) $pengajuan->jumlah_hari_diajukan
        ) {
            throw ValidationException::withMessages([
                'tanggal_selesai' =>
                'Jumlah hari pengajuan tidak sesuai dengan detail.',
            ]);
        }
    }

    private function siapkanKuota(
        Pegawai $pegawai,
        JenisCuti $jenisCuti,
        string $kodePeriode,
        ?string $referensiKejadian,
        int $userId
    ): KuotaCuti {
        try {
            if (
                $jenisCuti->periode_kuota
                === 'tahunan'
            ) {
                return $this->kuotaService
                    ->berikanKuotaTahunan(
                        $pegawai->getKey(),
                        $jenisCuti->getKey(),
                        (int) $kodePeriode,
                        $userId
                    );
            }

            return $this->kuotaService
                ->berikanKuotaNonTahunan(
                    $pegawai->getKey(),
                    $jenisCuti->getKey(),
                    $referensiKejadian,
                    $userId
                );
        } catch (
            InvalidArgumentException | LogicException $e
        ) {
            throw ValidationException::withMessages([
                'kuota_cuti' => $e->getMessage(),
            ]);
        }
    }

    private function validasiKuota(
        KuotaCuti $kuota,
        PengajuanCuti $pengajuan,
        JenisCuti $jenisCuti,
        $details
    ): void {
        if (
            (int) $kuota->pegawai_id
            !== (int) $pengajuan->pegawai_id
            || (int) $kuota->jenis_cuti_id
            !== (int) $pengajuan->jenis_cuti_id
        ) {
            throw new LogicException(
                'Kepemilikan kuota tidak sesuai dengan pengajuan.'
            );
        }

        if (!$kuota->is_active) {
            throw ValidationException::withMessages([
                'kuota_cuti' =>
                'Periode kuota tidak aktif.',
            ]);
        }

        if (
            ($kuota->kebijakan_snapshot['periode_kuota'] ?? null)
            !== $jenisCuti->periode_kuota
        ) {
            throw ValidationException::withMessages([
                'kuota_cuti' =>
                'Periode kebijakan kuota tidak sesuai dengan jenis cuti.',
            ]);
        }

        foreach ($details as $detail) {
            if (
                $detail->tanggal->lessThan(
                    $kuota->tanggal_mulai_periode
                )
                || (
                    $kuota->tanggal_akhir_periode !== null
                    && $detail->tanggal->greaterThan(
                        $kuota->tanggal_akhir_periode
                    )
                )
            ) {
                throw ValidationException::withMessages([
                    'kuota_cuti' =>
                    'Tanggal '
                        . $detail->tanggal->toDateString()
                        . ' berada di luar periode kuota.',
                ]);
            }
        }

        if (
            !$kuota->tanpa_batas
            && $kuota->sisa_kuota < $details->count()
        ) {
            throw ValidationException::withMessages([
                'kuota_cuti' =>
                'Sisa kuota periode '
                    . $kuota->kode_periode
                    . ' tidak mencukupi. Tersedia '
                    . $kuota->sisa_kuota
                    . ' hari, dibutuhkan '
                    . $details->count()
                    . ' hari.',
            ]);
        }
    }
}