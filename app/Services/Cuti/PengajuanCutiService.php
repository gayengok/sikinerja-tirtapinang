<?php

namespace App\Services\Cuti;

use App\Models\DetailPengajuanCuti;
use App\Models\JenisCuti;
use App\Models\MutasiKuotaCuti;
use App\Models\Pegawai;
use App\Models\PengajuanCuti;
use App\Models\PersetujuanCuti;
use App\Models\SkCuti;
use App\Models\SuratPermohonanCuti;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PengajuanCutiService
{
    private const TIMEZONE = 'Asia/Jakarta';

    private const MAKSIMAL_RENTANG_KALENDER = 366;

    private const ROLE_PEMOHON = [
        'pegawai',
        'atasan',
        'kepala_bagian',
    ];

    public function __construct(
        protected KalenderCutiService $kalenderService,
        protected KuotaCutiService $kuotaService,
        protected HierarkiCutiService $hierarkiService,
        protected BentrokCutiService $bentrokService,
        protected PemetaanKuotaCutiService $pemetaanService,
        protected PembentukanPersetujuanCutiService $pembentukanService,
        protected PelepasanReservasiPembatalanCutiService $pelepasanPembatalanService
    ) {}

    public function buatDraft(
        int $userId,
        int $jenisCutiId,
        string $tanggalMulai,
        string $tanggalSelesai,
        string $alasan,
        ?string $lampiran = null
    ): PengajuanCuti {
        return DB::transaction(function () use (
            $userId,
            $jenisCutiId,
            $tanggalMulai,
            $tanggalSelesai,
            $alasan,
            $lampiran
        ) {
            $user = User::query()
                ->findOrFail($userId);

            $pegawai = Pegawai::query()
                ->where('user_id', $userId)
                ->lockForUpdate()
                ->firstOrFail();

            $jenisCuti = JenisCuti::query()
                ->lockForUpdate()
                ->findOrFail($jenisCutiId);

            $this->validasiPemohon(
                $user,
                $pegawai
            );

            $this->validasiJenisCuti(
                $jenisCuti
            );

            $this->validasiAlasan(
                $alasan
            );

            $hasil = $this->hitungKalenderDraft(
                $pegawai,
                $jenisCuti,
                $tanggalMulai,
                $tanggalSelesai
            );

            $pengajuan = PengajuanCuti::query()->create([
                'nomor_pengajuan' => null,
                'pegawai_id' => $pegawai->getKey(),
                'jenis_cuti_id' => $jenisCuti->getKey(),
                'role_pemohon' => $user->role,
                'tanggal_pengajuan' => null,
                'tanggal_mulai' => $hasil['tanggal_mulai'],
                'tanggal_selesai' => $hasil['tanggal_selesai'],
                'jumlah_hari_diajukan' =>
                $hasil['jumlah_hari_diajukan'],
                'jumlah_hari_disetujui' => 0,
                'alasan' => trim($alasan),
                'lampiran' => filled($lampiran)
                    ? trim($lampiran)
                    : null,
                'status' => PengajuanCuti::STATUS_DRAFT,
                'current_approver_id' => null,
                'kebijakan_snapshot' =>
                $this->kuotaService->snapshotKebijakan(
                    $jenisCuti
                ),
                'pemohon_snapshot' =>
                $this->buatSnapshotPemohon(
                    $user,
                    $pegawai
                ),
                'diajukan_pada' => null,
                'diselesaikan_pada' => null,
                'dibatalkan_pada' => null,
                'alasan_pembatalan' => null,
            ]);

            $this->simpanDetailDraft(
                $pengajuan,
                $hasil['details']
            );

            return $pengajuan->refresh()->load('details');
        }, 3);
    }

    public function ubahDraft(
        int $userId,
        int $pengajuanId,
        int $jenisCutiId,
        string $tanggalMulai,
        string $tanggalSelesai,
        string $alasan,
        ?string $lampiran = null
    ): PengajuanCuti {
        return DB::transaction(function () use (
            $userId,
            $pengajuanId,
            $jenisCutiId,
            $tanggalMulai,
            $tanggalSelesai,
            $alasan,
            $lampiran
        ) {
            $user = User::query()
                ->findOrFail($userId);

            $pegawai = Pegawai::query()
                ->where('user_id', $userId)
                ->lockForUpdate()
                ->firstOrFail();

            $this->validasiPemohon(
                $user,
                $pegawai
            );

            $pengajuan = PengajuanCuti::query()
                ->lockForUpdate()
                ->findOrFail($pengajuanId);

            $this->validasiDraftMilikPemohon(
                $pengajuan,
                $pegawai
            );

            $jenisCuti = JenisCuti::query()
                ->lockForUpdate()
                ->findOrFail($jenisCutiId);

            $this->validasiJenisCuti(
                $jenisCuti
            );

            $this->validasiAlasan(
                $alasan
            );

            $hasil = $this->hitungKalenderDraft(
                $pegawai,
                $jenisCuti,
                $tanggalMulai,
                $tanggalSelesai
            );

            $pengajuan->update([
                'jenis_cuti_id' => $jenisCuti->getKey(),
                'role_pemohon' => $user->role,
                'tanggal_mulai' => $hasil['tanggal_mulai'],
                'tanggal_selesai' => $hasil['tanggal_selesai'],
                'jumlah_hari_diajukan' =>
                $hasil['jumlah_hari_diajukan'],
                'alasan' => trim($alasan),
                'lampiran' => filled($lampiran)
                    ? trim($lampiran)
                    : null,
                'kebijakan_snapshot' =>
                $this->kuotaService->snapshotKebijakan(
                    $jenisCuti
                ),
                'pemohon_snapshot' =>
                $this->buatSnapshotPemohon(
                    $user,
                    $pegawai
                ),
            ]);

            $pengajuan->details()->delete();

            $this->simpanDetailDraft(
                $pengajuan,
                $hasil['details']
            );

            return $pengajuan->refresh()->load('details');
        }, 3);
    }

    public function hapusDraft(
        int $userId,
        int $pengajuanId
    ): array {
        return DB::transaction(function () use (
            $userId,
            $pengajuanId
        ) {
            $user = User::query()
                ->findOrFail($userId);

            $pegawai = Pegawai::query()
                ->where('user_id', $userId)
                ->lockForUpdate()
                ->firstOrFail();

            $this->validasiPemohon(
                $user,
                $pegawai
            );

            $pengajuan = PengajuanCuti::query()
                ->lockForUpdate()
                ->findOrFail($pengajuanId);

            $this->validasiDraftMilikPemohon(
                $pengajuan,
                $pegawai
            );

            $lampiran = $pengajuan->lampiran;

            $jumlahDetailDihapus = $pengajuan
                ->details()
                ->delete();

            if (!$pengajuan->delete()) {
                throw new \RuntimeException(
                    'Draft pengajuan cuti gagal dihapus.'
                );
            }

            return [
                'pengajuan_cuti_id' => $pengajuanId,
                'pegawai_id' => $pegawai->getKey(),
                'jumlah_detail_dihapus' => $jumlahDetailDihapus,
                'lampiran' => $lampiran,
                'deleted' => true,
            ];
        }, 3);
    }

    public function ajukanDraft(
        int $userId,
        int $pengajuanId
    ): PengajuanCuti {
        return DB::transaction(function () use ($userId, $pengajuanId) {
            $pegawai = Pegawai::query()
                ->where('user_id', $userId)
                ->lockForUpdate()
                ->firstOrFail();

            $user = User::query()->lockForUpdate()->findOrFail($userId);
            $this->validasiPemohon($user, $pegawai);

            $pengajuan = PengajuanCuti::query()
                ->lockForUpdate()
                ->findOrFail($pengajuanId);

            $this->validasiDraftMilikPemohon($pengajuan, $pegawai);

            $jenisCuti = JenisCuti::query()
                ->lockForUpdate()
                ->findOrFail($pengajuan->jenis_cuti_id);

            $hasil = $this->validasiKebijakan(
                $user,
                $pegawai,
                $jenisCuti,
                $pengajuan->tanggal_mulai->toDateString(),
                $pengajuan->tanggal_selesai->toDateString(),
                $pengajuan->alasan,
                $pengajuan->lampiran
            );

            $this->hierarkiService->tentukanHierarki($pegawai->getKey());

            $this->bentrokService->validasiTidakBentrok(
                $pegawai->getKey(),
                $this->bentrokService->ambilTanggalDihitung($hasil['details']),
                $pengajuanId,
                true
            );

            if (!$pengajuan->update([
                'role_pemohon' => $user->role,
                'jumlah_hari_diajukan' => $hasil['jumlah_hari_diajukan'],
                'kebijakan_snapshot' => $this->kuotaService->snapshotKebijakan($jenisCuti),
                'pemohon_snapshot' => $this->buatSnapshotPemohon($user, $pegawai),
            ])) {
                throw new \RuntimeException('Gagal memperbarui draft sebelum submit.');
            }

            $pengajuan->details()->delete();
            $this->simpanDetailDraft($pengajuan, $hasil['details']);

            $this->pemetaanService->petakan($pengajuanId, $userId);
            $this->kuotaService->reservasiPengajuan($pengajuanId, $userId);
            $persetujuan = $this->pembentukanService->bentuk($pengajuanId, $userId);

            $waktu = CarbonImmutable::now(self::TIMEZONE);
            $nomor = sprintf('CUTI-%s-%06d', $waktu->format('Ymd'), $pengajuanId);

            if (!$pengajuan->update([
                'nomor_pengajuan' => $nomor,
                'tanggal_pengajuan' => $waktu->toDateString(),
                'diajukan_pada' => $waktu,
                'status' => $persetujuan['status_pengajuan'],
                'current_approver_id' => $persetujuan['current_approver_id'],
            ])) {
                throw new \RuntimeException('Gagal menyimpan pengajuan cuti.');
            }

            return $pengajuan->refresh()->load('details');
        }, 3);
    }

    public function batalkanPengajuan(
        int $userId,
        int $pengajuanId,
        string $alasan
    ): PengajuanCuti {
        $alasan = trim($alasan);

        if ($alasan === '' || strlen($alasan) > 65535) {
            throw ValidationException::withMessages([
                'alasan_pembatalan' => 'Alasan pembatalan wajib diisi dan tidak boleh melebihi kapasitas penyimpanan.',
            ]);
        }

        return DB::transaction(function () use ($userId, $pengajuanId, $alasan) {
            $pegawai = Pegawai::query()
                ->where('user_id', $userId)
                ->lockForUpdate()
                ->firstOrFail();

            $user = User::query()->lockForUpdate()->findOrFail($userId);
            $this->validasiPemohon($user, $pegawai);

            $pengajuan = PengajuanCuti::query()
                ->lockForUpdate()
                ->findOrFail($pengajuanId);

            if ((int) $pengajuan->pegawai_id !== (int) $pegawai->getKey()) {
                throw ValidationException::withMessages([
                    'pengajuan_cuti_id' => 'Pengajuan cuti bukan milik pemohon.',
                ]);
            }

            if (!$pengajuan->isPending() || $pengajuan->alasan_pembatalan !== null) {
                throw ValidationException::withMessages([
                    'status' => 'Hanya pengajuan yang masih dalam proses persetujuan dapat dibatalkan.',
                ]);
            }

            $persetujuan = PersetujuanCuti::query()
                ->where('pengajuan_cuti_id', $pengajuanId)
                ->orderBy('urutan')
                ->lockForUpdate()
                ->get();

            $pending = $persetujuan->where('status', PersetujuanCuti::STATUS_PENDING);
            $aktif = $pending->first();

            if (
                $pending->count() !== 1
                || (int) $aktif->approver_id !== (int) $pengajuan->current_approver_id
                || $pengajuan->status !== 'pending_' . $aktif->role_approver
            ) {
                throw ValidationException::withMessages([
                    'status' => 'Tahapan persetujuan aktif tidak konsisten.',
                ]);
            }

            foreach ($persetujuan as $tahap) {
                $statusValid = match (true) {
                    $tahap->urutan < $aktif->urutan => in_array($tahap->status, [
                        PersetujuanCuti::STATUS_APPROVED,
                        PersetujuanCuti::STATUS_PARTIALLY_APPROVED,
                    ], true),
                    $tahap->urutan > $aktif->urutan => $tahap->status === PersetujuanCuti::STATUS_WAITING,
                    default => $tahap->status === PersetujuanCuti::STATUS_PENDING,
                };

                if (!$statusValid) {
                    throw ValidationException::withMessages([
                        'status' => 'Urutan status persetujuan tidak konsisten.',
                    ]);
                }
            }

            $surats = SuratPermohonanCuti::query()
                ->where('pengajuan_cuti_id', $pengajuanId)
                ->where('is_latest', true)
                ->lockForUpdate()
                ->get();

            $this->pelepasanPembatalanService->lepaskan($pengajuanId, $userId, $alasan);

            $waktu = CarbonImmutable::now(self::TIMEZONE);

            foreach ($persetujuan as $tahap) {
                if (!in_array($tahap->status, [
                    PersetujuanCuti::STATUS_PENDING,
                    PersetujuanCuti::STATUS_WAITING,
                ], true)) {
                    continue;
                }

                if (!$tahap->update([
                    'status' => PersetujuanCuti::STATUS_CANCELLED,
                ])) {
                    throw new \RuntimeException('Gagal membatalkan tahapan persetujuan.');
                }
            }

            foreach ($surats as $surat) {
                if (!$surat->update([
                    'status_dokumen' => SuratPermohonanCuti::STATUS_SUPERSEDED,
                    'is_latest' => false,
                ])) {
                    throw new \RuntimeException('Gagal menonaktifkan surat permohonan.');
                }
            }

            if (!$pengajuan->update([
                'status' => PengajuanCuti::STATUS_CANCELLED,
                'current_approver_id' => null,
                'jumlah_hari_disetujui' => 0,
                'dibatalkan_pada' => $waktu,
                'diselesaikan_pada' => $waktu,
                'alasan_pembatalan' => $alasan,
            ])) {
                throw new \RuntimeException('Gagal menyimpan pembatalan pengajuan cuti.');
            }

            return $pengajuan->refresh()->load('details');
        }, 3);
    }

    public function validasiKebijakan(
        User $user,
        Pegawai $pegawai,
        JenisCuti $jenisCuti,
        string $tanggalMulai,
        string $tanggalSelesai,
        string $alasan,
        ?string $lampiran = null
    ): array {
        $hariIni = CarbonImmutable::today(
            self::TIMEZONE
        );

        $this->validasiPemohon(
            $user,
            $pegawai
        );

        $this->validasiJenisCuti(
            $jenisCuti
        );

        [$mulai, $selesai] = $this->kalenderService
            ->validasiRentangTanggal(
                $tanggalMulai,
                $tanggalSelesai
            );

        $this->validasiBatasRentang(
            $mulai,
            $selesai
        );

        $this->validasiMasaKerja(
            $pegawai,
            $jenisCuti,
            $hariIni
        );

        $this->validasiWaktuPengajuan(
            $jenisCuti,
            $hariIni,
            $mulai
        );

        $this->validasiAlasan(
            $alasan
        );

        $this->validasiLampiran(
            $jenisCuti,
            $lampiran
        );

        $hasil = $this->kalenderService->hitung(
            $pegawai,
            $jenisCuti,
            $mulai,
            $selesai
        );

        $this->validasiDurasiCuti(
            $jenisCuti,
            $hasil['jumlah_hari_diajukan']
        );

        return $hasil;
    }

    private function validasiDraftMilikPemohon(
        PengajuanCuti $pengajuan,
        Pegawai $pegawai
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

        if ($pengajuan->status !== PengajuanCuti::STATUS_DRAFT) {
            throw ValidationException::withMessages([
                'status' =>
                'Hanya pengajuan berstatus draft yang dapat diubah atau dihapus.',
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
                'Data draft memiliki riwayat pengajuan yang tidak valid.',
            ]);
        }

        if (
            $pengajuan->details()
            ->where(function ($query) {
                $query->whereNotNull('kuota_cuti_id')
                    ->orWhereNotIn('status', [
                        DetailPengajuanCuti::STATUS_PENDING,
                        DetailPengajuanCuti::STATUS_EXCLUDED,
                    ])
                    ->orWhereNotNull('ditolak_oleh')
                    ->orWhereNotNull('ditolak_pada')
                    ->orWhereNotNull('disetujui_pada');
            })
            ->exists()
        ) {
            throw ValidationException::withMessages([
                'status' =>
                'Detail draft telah memiliki riwayat pemrosesan.',
            ]);
        }

        if (
            PersetujuanCuti::query()
            ->where(
                'pengajuan_cuti_id',
                $pengajuan->getKey()
            )
            ->exists()
        ) {
            throw ValidationException::withMessages([
                'status' =>
                'Draft telah memiliki data persetujuan.',
            ]);
        }

        if (
            MutasiKuotaCuti::query()
            ->where(
                'pengajuan_cuti_id',
                $pengajuan->getKey()
            )
            ->exists()
        ) {
            throw ValidationException::withMessages([
                'status' =>
                'Draft telah memiliki riwayat mutasi kuota.',
            ]);
        }

        if (
            SuratPermohonanCuti::query()
            ->where(
                'pengajuan_cuti_id',
                $pengajuan->getKey()
            )
            ->exists()
        ) {
            throw ValidationException::withMessages([
                'status' =>
                'Draft telah memiliki surat permohonan cuti.',
            ]);
        }

        if (
            SkCuti::query()
            ->where(
                'pengajuan_cuti_id',
                $pengajuan->getKey()
            )
            ->exists()
        ) {
            throw ValidationException::withMessages([
                'status' =>
                'Draft telah memiliki surat keputusan cuti.',
            ]);
        }
    }

    private function validasiPemohon(
        User $user,
        Pegawai $pegawai
    ): void {
        if (
            !$user->exists
            || !$pegawai->exists
            || (int) $pegawai->user_id !== (int) $user->id
        ) {
            throw ValidationException::withMessages([
                'pegawai_id' =>
                'Data pegawai tidak sesuai dengan akun pemohon.',
            ]);
        }

        if (!in_array($user->role, self::ROLE_PEMOHON, true)) {
            throw ValidationException::withMessages([
                'role_pemohon' =>
                'Role pengguna tidak diperbolehkan mengajukan cuti.',
            ]);
        }

        if ($pegawai->status !== 'aktif') {
            throw ValidationException::withMessages([
                'pegawai_id' =>
                'Pegawai tidak aktif tidak dapat mengajukan cuti.',
            ]);
        }
    }

    private function validasiJenisCuti(
        JenisCuti $jenisCuti
    ): void {
        if (!$jenisCuti->exists || !$jenisCuti->is_active) {
            throw ValidationException::withMessages([
                'jenis_cuti_id' =>
                'Jenis cuti tidak tersedia atau tidak aktif.',
            ]);
        }

        if (!in_array($jenisCuti->periode_kuota, [
            'tahunan',
            'per_kejadian',
            'seumur_kerja',
            'tanpa_batas',
        ], true)) {
            throw ValidationException::withMessages([
                'jenis_cuti_id' =>
                'Periode kuota jenis cuti tidak valid.',
            ]);
        }
    }

    private function validasiMasaKerja(
        Pegawai $pegawai,
        JenisCuti $jenisCuti,
        CarbonImmutable $hariIni
    ): void {
        $minimalBulan = (int) $jenisCuti
            ->minimal_masa_kerja_bulan;

        if ($minimalBulan < 0) {
            throw ValidationException::withMessages([
                'jenis_cuti_id' =>
                'Kebijakan minimal masa kerja tidak valid.',
            ]);
        }

        if (!$pegawai->tanggal_masuk) {
            throw ValidationException::withMessages([
                'tanggal_masuk' =>
                'Tanggal masuk pegawai belum tersedia.',
            ]);
        }

        $tanggalMasuk = CarbonImmutable::parse(
            $pegawai->tanggal_masuk->toDateString(),
            self::TIMEZONE
        )->startOfDay();

        if ($tanggalMasuk->gt($hariIni)) {
            throw ValidationException::withMessages([
                'tanggal_masuk' =>
                'Tanggal masuk pegawai belum tercapai.',
            ]);
        }

        $tanggalMemenuhiSyarat = $tanggalMasuk
            ->addMonthsNoOverflow($minimalBulan);

        if ($hariIni->lt($tanggalMemenuhiSyarat)) {
            throw ValidationException::withMessages([
                'jenis_cuti_id' =>
                "Minimal masa kerja untuk jenis cuti ini adalah {$minimalBulan} bulan.",
            ]);
        }
    }

    private function validasiWaktuPengajuan(
        JenisCuti $jenisCuti,
        CarbonImmutable $hariIni,
        CarbonImmutable $tanggalMulai
    ): void {
        if ($tanggalMulai->lt($hariIni)) {
            throw ValidationException::withMessages([
                'tanggal_mulai' =>
                'Tanggal mulai cuti tidak boleh sebelum hari ini.',
            ]);
        }

        if ($tanggalMulai->eq($hariIni)) {
            if (!$jenisCuti->boleh_hari_h) {
                throw ValidationException::withMessages([
                    'tanggal_mulai' =>
                    'Jenis cuti ini tidak dapat diajukan pada hari H.',
                ]);
            }
        }

        $minimalHari = (int) $jenisCuti
            ->minimal_hari_pengajuan;

        if ($minimalHari < 0) {
            throw ValidationException::withMessages([
                'jenis_cuti_id' =>
                'Kebijakan minimal waktu pengajuan tidak valid.',
            ]);
        }

        $tanggalMinimal = $hariIni->addDays(
            $minimalHari
        );

        if ($tanggalMulai->lt($tanggalMinimal)) {
            throw ValidationException::withMessages([
                'tanggal_mulai' =>
                "Cuti harus diajukan minimal {$minimalHari} hari sebelumnya.",
            ]);
        }
    }

    private function validasiDurasiCuti(
        JenisCuti $jenisCuti,
        int $jumlahHari
    ): void {
        if ($jumlahHari <= 0) {
            throw ValidationException::withMessages([
                'tanggal_selesai' =>
                'Tidak terdapat tanggal cuti yang dapat dihitung.',
            ]);
        }

        $maksimalHari = $jenisCuti
            ->maksimal_hari_pengajuan;

        if (
            $maksimalHari !== null
            && $jumlahHari > (int) $maksimalHari
        ) {
            throw ValidationException::withMessages([
                'tanggal_selesai' =>
                "Maksimal pengajuan cuti adalah {$maksimalHari} hari.",
            ]);
        }
    }

    private function validasiAlasan(
        string $alasan
    ): void {
        if (trim($alasan) === '') {
            throw ValidationException::withMessages([
                'alasan' => 'Alasan cuti wajib diisi.',
            ]);
        }
    }

    private function validasiLampiran(
        JenisCuti $jenisCuti,
        ?string $lampiran
    ): void {
        if (
            $jenisCuti->wajib_lampiran
            && ($lampiran === null || trim($lampiran) === '')
        ) {
            throw ValidationException::withMessages([
                'lampiran' =>
                'Jenis cuti ini mewajibkan lampiran.',
            ]);
        }
    }

    private function validasiBatasRentang(
        CarbonImmutable $mulai,
        CarbonImmutable $selesai
    ): void {
        $jumlahHariKalender = (int) $mulai
            ->diffInDays($selesai) + 1;

        if (
            $jumlahHariKalender
            > self::MAKSIMAL_RENTANG_KALENDER
        ) {
            throw ValidationException::withMessages([
                'tanggal_selesai' =>
                'Rentang tanggal pengajuan maksimal '
                    . self::MAKSIMAL_RENTANG_KALENDER
                    . ' hari kalender.',
            ]);
        }
    }

    private function hitungKalenderDraft(
        Pegawai $pegawai,
        JenisCuti $jenisCuti,
        string $tanggalMulai,
        string $tanggalSelesai
    ): array {
        [$mulai, $selesai] = $this->kalenderService
            ->validasiRentangTanggal(
                $tanggalMulai,
                $tanggalSelesai
            );

        $hariIni = CarbonImmutable::today(
            self::TIMEZONE
        );

        if ($mulai->lt($hariIni)) {
            throw ValidationException::withMessages([
                'tanggal_mulai' =>
                'Tanggal mulai cuti tidak boleh sebelum hari ini.',
            ]);
        }

        $this->validasiBatasRentang(
            $mulai,
            $selesai
        );

        $hasil = $this->kalenderService->hitung(
            $pegawai,
            $jenisCuti,
            $mulai,
            $selesai
        );

        if ($hasil['jumlah_hari_diajukan'] <= 0) {
            throw ValidationException::withMessages([
                'tanggal_selesai' =>
                'Tidak terdapat tanggal cuti yang dapat dihitung.',
            ]);
        }

        return $hasil;
    }

    private function buatSnapshotPemohon(
        User $user,
        Pegawai $pegawai
    ): array {
        $pegawai->loadMissing('jabatan.divisi');

        return [
            'pegawai_id' => $pegawai->getKey(),
            'user_id' => $user->getKey(),
            'nama' => $pegawai->nama,
            'nip' => $pegawai->nip,
            'role' => $user->role,
            'jabatan_id' => $pegawai->jabatan_id,
            'nama_jabatan' =>
            $pegawai->jabatan?->nama_jabatan,
            'divisi_id' =>
            $pegawai->jabatan?->divisi_id,
            'nama_divisi' =>
            $pegawai->jabatan?->divisi?->nama_divisi,
            'atasan_id' => $pegawai->atasan_id,
            'tanggal_masuk' =>
            $pegawai->tanggal_masuk?->toDateString(),
            'status' => $pegawai->status,
            'snapshot_pada' => CarbonImmutable::now(
                self::TIMEZONE
            )->toIso8601String(),
        ];
    }

    private function simpanDetailDraft(
        PengajuanCuti $pengajuan,
        array $details
    ): void {
        foreach ($details as $detail) {
            DetailPengajuanCuti::query()->create([
                'pengajuan_cuti_id' =>
                $pengajuan->getKey(),
                'kuota_cuti_id' => null,
                'tanggal' => $detail['tanggal'],
                'is_hari_kerja' =>
                $detail['is_hari_kerja'],
                'dihitung_kuota' =>
                $detail['dihitung_kuota'],
                'status' => $detail['status'],
                'ditolak_oleh' => null,
                'ditolak_pada' => null,
                'disetujui_pada' => null,
            ]);
        }
    }
}