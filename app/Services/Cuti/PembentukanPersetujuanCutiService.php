<?php

namespace App\Services\Cuti;

use App\Models\DetailPengajuanCuti;
use App\Models\KuotaCuti;
use App\Models\Pegawai;
use App\Models\PengajuanCuti;
use App\Models\PersetujuanCuti;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use LogicException;

class PembentukanPersetujuanCutiService
{
    private const TIMEZONE = 'Asia/Jakarta';

    public function __construct(
        protected HierarkiCutiService $hierarkiService,
        protected KuotaCutiService $kuotaService
    ) {}

    public function bentuk(
        int $pengajuanId,
        int $userId
    ): array {
        if (DB::transactionLevel() === 0) {
            throw new LogicException(
                'Pembentukan persetujuan wajib berada dalam transaksi pengajuan cuti.'
            );
        }

        return DB::transaction(function () use (
            $pengajuanId,
            $userId
        ) {
            $pegawai = Pegawai::query()
                ->where('user_id', $userId)
                ->lockForUpdate()
                ->firstOrFail();

            $user = User::query()
                ->lockForUpdate()
                ->findOrFail($userId);

            $pengajuan = PengajuanCuti::query()
                ->lockForUpdate()
                ->findOrFail($pengajuanId);

            $this->validasiPengajuan(
                $pengajuan,
                $pegawai,
                $user
            );

            $this->validasiPersetujuanBelumAda(
                $pengajuan
            );

            $tanggal = $this->validasiReservasi(
                $pengajuan
            );

            $hasilHierarki = $this->hierarkiService
                ->tentukanHierarki(
                    $pegawai->getKey()
                );

            $waktu = CarbonImmutable::now(
                self::TIMEZONE
            );

            $persetujuan = [];

            foreach (
                $hasilHierarki['hierarki'] as $tahapan
            ) {
                $tahapPertama =
                    (int) $tahapan['urutan'] === 1;

                $record = PersetujuanCuti::query()
                    ->create([
                        'pengajuan_cuti_id' =>
                        $pengajuan->getKey(),

                        'approver_id' =>
                        $tahapan['approver_id'],

                        'role_approver' =>
                        $tahapan['role_approver'],

                        'urutan' =>
                        $tahapan['urutan'],

                        'status' => $tahapPertama
                            ? PersetujuanCuti::STATUS_PENDING
                            : PersetujuanCuti::STATUS_WAITING,

                        'jumlah_hari_diterima' =>
                        $tahapPertama
                            ? count($tanggal)
                            : 0,

                        'jumlah_hari_disetujui' => 0,

                        'jumlah_hari_ditolak' => 0,

                        'tanggal_diterima' =>
                        $tahapPertama
                            ? $tanggal
                            : null,

                        'tanggal_disetujui' => null,

                        'tanggal_ditolak' => null,

                        'catatan' => null,

                        'approver_snapshot' =>
                        $tahapan['approver_snapshot'],

                        'tanda_tangan_path' => null,

                        'token_verifikasi' => null,

                        'hash_verifikasi' => null,

                        'diterima_pada' =>
                        $tahapPertama
                            ? $waktu
                            : null,

                        'diproses_pada' => null,
                    ]);

                if (!$record->exists) {
                    throw new LogicException(
                        'Gagal menyimpan tahapan persetujuan cuti.'
                    );
                }

                $persetujuan[] = [
                    'persetujuan_id' =>
                    (int) $record->getKey(),

                    'approver_id' =>
                    (int) $record->approver_id,

                    'role_approver' =>
                    $record->role_approver,

                    'urutan' =>
                    $record->urutan,

                    'status' =>
                    $record->status,

                    'jumlah_hari_diterima' =>
                    $record->jumlah_hari_diterima,

                    'tanggal_diterima' =>
                    $record->tanggal_diterima,
                ];
            }

            return [
                'pengajuan_cuti_id' =>
                (int) $pengajuan->getKey(),

                'pegawai_id' =>
                (int) $pegawai->getKey(),

                'role_pemohon' =>
                $hasilHierarki['role_pemohon'],

                'jumlah_tahapan' =>
                count($persetujuan),

                'jumlah_hari' =>
                count($tanggal),

                'current_approver_id' =>
                $hasilHierarki['current_approver_id'],

                'status_pengajuan' =>
                $hasilHierarki['status_pengajuan'],

                'persetujuan_pertama_id' =>
                $persetujuan[0]['persetujuan_id'],

                'persetujuan' =>
                $persetujuan,
            ];
        }, 3);
    }

    private function validasiPengajuan(
        PengajuanCuti $pengajuan,
        Pegawai $pegawai,
        User $user
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
            $pegawai->status !== 'aktif'
            || !in_array(
                $user->role,
                [
                    'pegawai',
                    'atasan',
                    'kepala_bagian',
                ],
                true
            )
        ) {
            throw ValidationException::withMessages([
                'pegawai_id' =>
                'Pemohon tidak berwenang mengajukan cuti.',
            ]);
        }

        if (
            $pengajuan->role_pemohon
            !== $user->role
        ) {
            throw ValidationException::withMessages([
                'role_pemohon' =>
                'Role pemohon tidak sesuai dengan pengajuan.',
            ]);
        }

        if (
            $pengajuan->status
            !== PengajuanCuti::STATUS_DRAFT
        ) {
            throw ValidationException::withMessages([
                'status' =>
                'Persetujuan awal hanya dapat dibentuk untuk draft.',
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

    private function validasiPersetujuanBelumAda(
        PengajuanCuti $pengajuan
    ): void {
        $persetujuan = PersetujuanCuti::query()
            ->where(
                'pengajuan_cuti_id',
                $pengajuan->getKey()
            )
            ->lockForUpdate()
            ->first();

        if ($persetujuan !== null) {
            throw ValidationException::withMessages([
                'persetujuan' =>
                'Tahapan persetujuan pengajuan sudah tersedia.',
            ]);
        }
    }

    private function validasiReservasi(
        PengajuanCuti $pengajuan
    ): array {
        $details = DetailPengajuanCuti::query()
            ->where(
                'pengajuan_cuti_id',
                $pengajuan->getKey()
            )
            ->orderBy('tanggal')
            ->lockForUpdate()
            ->get();

        if ($details->isEmpty()) {
            throw ValidationException::withMessages([
                'tanggal' =>
                'Detail tanggal pengajuan tidak tersedia.',
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
                'tanggal' =>
                'Detail rentang tanggal pengajuan tidak lengkap.',
            ]);
        }

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
                    'Detail tanggal berada di luar rentang pengajuan.',
                ]);
            }

            if (
                $detail->ditolak_oleh !== null
                || $detail->ditolak_pada !== null
                || $detail->disetujui_pada !== null
            ) {
                throw ValidationException::withMessages([
                    'status' =>
                    'Detail telah memiliki riwayat persetujuan.',
                ]);
            }

            if (!$detail->dihitung_kuota) {
                if (
                    $detail->status
                    !== DetailPengajuanCuti::STATUS_EXCLUDED
                    || $detail->kuota_cuti_id !== null
                ) {
                    throw ValidationException::withMessages([
                        'status' =>
                        'Detail excluded memiliki data yang tidak valid.',
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

            if ($detail->kuota_cuti_id === null) {
                throw ValidationException::withMessages([
                    'kuota_cuti' =>
                    'Detail pengajuan belum memiliki pemetaan kuota.',
                ]);
            }
        }

        $detailDihitung = $details
            ->filter(
                fn($detail) =>
                $detail->dihitung_kuota
            );

        if (
            $detailDihitung->isEmpty()
            || $detailDihitung->count()
            !== (int) $pengajuan->jumlah_hari_diajukan
        ) {
            throw ValidationException::withMessages([
                'tanggal' =>
                'Jumlah hari pengajuan tidak sesuai dengan detail.',
            ]);
        }

        $kelompok = $detailDihitung
            ->groupBy('kuota_cuti_id')
            ->sortKeys();

        foreach ($kelompok as $kuotaId => $daftar) {
            $kuota = KuotaCuti::query()
                ->lockForUpdate()
                ->findOrFail($kuotaId);

            if (
                (int) $kuota->pegawai_id
                !== (int) $pengajuan->pegawai_id
                || (int) $kuota->jenis_cuti_id
                !== (int) $pengajuan->jenis_cuti_id
            ) {
                throw ValidationException::withMessages([
                    'kuota_cuti' =>
                    'Kepemilikan kuota tidak sesuai dengan pengajuan.',
                ]);
            }

            if (!$kuota->is_active) {
                throw ValidationException::withMessages([
                    'kuota_cuti' =>
                    'Kuota pengajuan tidak aktif.',
                ]);
            }

            foreach ($daftar as $detail) {
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
                        'Tanggal pengajuan berada di luar periode kuota.',
                    ]);
                }
            }

            $jumlahReservasi = $this->kuotaService
                ->jumlahReservasiPengajuan(
                    (int) $kuotaId,
                    $pengajuan->getKey()
                );

            if (
                $jumlahReservasi !== $daftar->count()
                || (int) $kuota->kuota_direservasi
                < $jumlahReservasi
            ) {
                throw ValidationException::withMessages([
                    'kuota_cuti' =>
                    'Reservasi kuota pengajuan belum lengkap atau tidak sesuai.',
                ]);
            }
        }

        return $detailDihitung
            ->map(
                fn($detail) =>
                $detail->tanggal->toDateString()
            )
            ->values()
            ->all();
    }
}