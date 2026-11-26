<?php

namespace App\Services\Cuti;

use App\Models\JenisCuti;
use App\Models\KuotaCuti;
use App\Models\MutasiKuotaCuti;
use App\Models\Pegawai;
use App\Models\PengajuanCuti;
use App\Models\DetailPengajuanCuti;
use App\Models\PersetujuanCuti;
use Carbon\CarbonImmutable;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use LogicException;

class KuotaCutiService
{
    public function __construct(
        protected AkumulasiCutiService $akumulasiService,
        protected MutasiKuotaCutiService $mutasiService
    ) {}

    public function snapshotKebijakan(
        JenisCuti $jenisCuti
    ): array {
        return [
            'jenis_cuti_id' => $jenisCuti->getKey(),
            'kode' => $jenisCuti->kode,
            'nama' => $jenisCuti->nama,
            'deskripsi' => $jenisCuti->deskripsi,
            'kuota_hari' => $jenisCuti->kuota_hari,
            'periode_kuota' => $jenisCuti->periode_kuota,
            'minimal_hari_pengajuan' =>
            (int) $jenisCuti->minimal_hari_pengajuan,
            'maksimal_hari_pengajuan' =>
            $jenisCuti->maksimal_hari_pengajuan,
            'boleh_hari_h' =>
            (bool) $jenisCuti->boleh_hari_h,
            'metode_hitung_hari' =>
            $jenisCuti->metode_hitung_hari,
            'minimal_masa_kerja_bulan' =>
            (int) $jenisCuti->minimal_masa_kerja_bulan,
            'bisa_akumulasi' =>
            (bool) $jenisCuti->bisa_akumulasi,
            'tambahan_akumulasi_hari' =>
            (int) $jenisCuti->tambahan_akumulasi_hari,
            'maksimal_tahun_akumulasi' =>
            (int) $jenisCuti->maksimal_tahun_akumulasi,
            'tahun_awal_siklus' =>
            $jenisCuti->tahun_awal_siklus,
            'wajib_lampiran' =>
            (bool) $jenisCuti->wajib_lampiran,
            'is_active' =>
            (bool) $jenisCuti->is_active,
            'snapshot_pada' => now()->toIso8601String(),
        ];
    }

    public function cariKuotaTahunan(
        int $pegawaiId,
        int $jenisCutiId,
        int $tahun
    ): ?KuotaCuti {
        $this->akumulasiService->validasiTahun($tahun);

        return KuotaCuti::query()
            ->pegawai($pegawaiId)
            ->jenisCuti($jenisCutiId)
            ->tahun($tahun)
            ->where('kode_periode', (string) $tahun)
            ->first();
    }

    public function berikanKuotaTahunan(
        int $pegawaiId,
        int $jenisCutiId,
        int $tahun,
        ?int $userId = null
    ): KuotaCuti {
        $this->akumulasiService->validasiTahun($tahun);

        if ($tahun > now()->year) {
            throw new InvalidArgumentException(
                'Kuota tahun mendatang belum dapat diterbitkan.'
            );
        }

        return DB::transaction(function () use (
            $pegawaiId,
            $jenisCutiId,
            $tahun,
            $userId
        ) {
            $pegawai = Pegawai::query()
                ->lockForUpdate()
                ->findOrFail($pegawaiId);

            $jenisCuti = JenisCuti::query()
                ->lockForUpdate()
                ->findOrFail($jenisCutiId);

            $kuotaExisting = KuotaCuti::query()
                ->pegawai($pegawaiId)
                ->jenisCuti($jenisCutiId)
                ->where('kode_periode', (string) $tahun)
                ->lockForUpdate()
                ->first();

            if ($kuotaExisting) {
                return $kuotaExisting;
            }

            $this->akumulasiService->validasiKebijakan(
                $jenisCuti
            );

            $kuotaLebihBaru = KuotaCuti::query()
                ->pegawai($pegawaiId)
                ->jenisCuti($jenisCutiId)
                ->where('tahun', '>', $tahun)
                ->exists();

            if ($kuotaLebihBaru) {
                throw new LogicException(
                    'Tidak dapat menerbitkan kuota lama setelah kuota periode berikutnya tersedia.'
                );
            }

            $kuotaSebelumnya = KuotaCuti::query()
                ->pegawai($pegawaiId)
                ->jenisCuti($jenisCutiId)
                ->tahun($tahun - 1)
                ->where('kode_periode', (string) ($tahun - 1))
                ->lockForUpdate()
                ->first();

            $hasil = $this->akumulasiService->hitung(
                $pegawai,
                $jenisCuti,
                $tahun,
                $kuotaSebelumnya
            );

            if (!$hasil['berhak_kuota']) {
                throw new LogicException(
                    $hasil['keterangan']
                );
            }

            if ($hasil['perlu_evaluasi_ulang']) {
                throw new LogicException(
                    'Penerbitan kuota ditunda karena reservasi periode sebelumnya belum selesai.'
                );
            }

            $tanggalMulai = CarbonImmutable::parse(
                $hasil['tanggal_mulai_berlaku']
            )->startOfDay();

            $tanggalAkhir = CarbonImmutable::create(
                $tahun,
                12,
                31
            )->startOfDay();

            if ($tanggalMulai->greaterThan(today())) {
                throw new LogicException(
                    'Pegawai belum mencapai tanggal mulai hak cuti.'
                );
            }

            $snapshot = $this->snapshotKebijakan(
                $jenisCuti
            );

            $snapshot['perhitungan_akumulasi'] = $hasil;

            $kuota = KuotaCuti::query()->create([
                'pegawai_id' => $pegawaiId,
                'jenis_cuti_id' => $jenisCutiId,
                'kode_periode' => (string) $tahun,
                'tahun' => $tahun,
                'tanggal_mulai_periode' =>
                $tanggalMulai->toDateString(),
                'tanggal_akhir_periode' =>
                $tanggalAkhir->toDateString(),
                'kuota_dasar' => $hasil['kuota_dasar'],
                'kuota_akumulasi' => 0,
                'kuota_penyesuaian' => 0,
                'kuota_terpakai' => 0,
                'kuota_direservasi' => 0,
                'kuota_hangus' => 0,
                'tahun_ke_siklus' =>
                $hasil['tahun_ke_siklus'],
                'kebijakan_snapshot' => $snapshot,
                'is_active' =>
                !$tanggalAkhir->lessThan(today()),
            ]);

            $kodePemberian = $this->mutasiService
                ->buatKodeMutasi(
                    MutasiKuotaCuti::JENIS_PEMBERIAN_KUOTA,
                    $kuota->getKey(),
                    'periode-' . $tahun
                );

            $this->mutasiService->catat(
                kuota: $kuota,
                jenisMutasi: MutasiKuotaCuti::JENIS_PEMBERIAN_KUOTA,
                jumlahHari: $hasil['kuota_dasar'],
                saldoSebelum: $this->mutasiService->snapshotAwal(),
                kodeMutasi: $kodePemberian,
                keterangan: "Pemberian kuota dasar cuti tahun {$tahun}.",
                userId: $userId,
                metadata: [
                    'tahun' => $tahun,
                    'tahun_ke_siklus' =>
                    $hasil['tahun_ke_siklus'],
                    'kode_alasan' =>
                    $hasil['kode_alasan'],
                ]
            );

            if ($hasil['kuota_akumulasi'] > 0) {
                $saldoSebelum = $kuota->snapshotSaldo();

                $kuota->kuota_akumulasi =
                    $hasil['kuota_akumulasi'];

                $kuota->save();

                $kodeAkumulasi = $this->mutasiService
                    ->buatKodeMutasi(
                        MutasiKuotaCuti::JENIS_AKUMULASI,
                        $kuota->getKey(),
                        'periode-' . $tahun
                    );

                $this->mutasiService->catat(
                    kuota: $kuota,
                    jenisMutasi: MutasiKuotaCuti::JENIS_AKUMULASI,
                    jumlahHari: $hasil['kuota_akumulasi'],
                    saldoSebelum: $saldoSebelum,
                    kodeMutasi: $kodeAkumulasi,
                    keterangan: "Pemberian akumulasi cuti tahun {$tahun}.",
                    userId: $userId,
                    metadata: [
                        'tahun' => $tahun,
                        'kuota_sebelumnya_id' =>
                        $hasil['kuota_sebelumnya_id'],
                        'akumulasi_sebelumnya' =>
                        $hasil['akumulasi_sebelumnya'],
                        'tambahan_akumulasi' =>
                        $hasil['tambahan_akumulasi'],
                        'tahun_ke_siklus' =>
                        $hasil['tahun_ke_siklus'],
                    ]
                );
            }

            return $kuota->refresh();
        }, 3);
    }

    public function ringkasanKuotaTahunan(
        int $pegawaiId,
        int $jenisCutiId,
        int $tahun
    ): ?array {
        $kuota = $this->cariKuotaTahunan(
            $pegawaiId,
            $jenisCutiId,
            $tahun
        );

        if ($kuota === null) {
            return null;
        }

        return [
            'kuota_id' => $kuota->getKey(),
            'pegawai_id' => $kuota->pegawai_id,
            'jenis_cuti_id' => $kuota->jenis_cuti_id,
            'tahun' => $kuota->tahun,
            'kode_periode' => $kuota->kode_periode,
            'tanggal_mulai_periode' =>
            $kuota->tanggal_mulai_periode->toDateString(),
            'tanggal_akhir_periode' =>
            $kuota->tanggal_akhir_periode?->toDateString(),
            'tahun_ke_siklus' => $kuota->tahun_ke_siklus,
            'is_active' => $kuota->is_active,
            ...$kuota->snapshotSaldo(),
        ];
    }

    public function validasiKebijakanNonTahunan(
        JenisCuti $jenisCuti
    ): void {
        if (!in_array($jenisCuti->periode_kuota, [
            'per_kejadian',
            'seumur_kerja',
            'tanpa_batas',
        ], true)) {
            throw new InvalidArgumentException(
                'Periode kuota non-tahunan tidak valid.'
            );
        }

        if (!$jenisCuti->is_active) {
            throw new LogicException(
                'Jenis cuti tidak berstatus aktif.'
            );
        }

        if ($jenisCuti->bisa_akumulasi) {
            throw new InvalidArgumentException(
                'Kuota non-tahunan tidak mendukung akumulasi.'
            );
        }

        if ($jenisCuti->minimal_masa_kerja_bulan < 0) {
            throw new InvalidArgumentException(
                'Minimal masa kerja tidak valid.'
            );
        }

        if ($jenisCuti->periode_kuota === 'tanpa_batas') {
            if ($jenisCuti->kuota_hari !== null) {
                throw new InvalidArgumentException(
                    'Kuota tanpa batas harus memiliki kuota hari null.'
                );
            }

            return;
        }

        if (
            $jenisCuti->kuota_hari === null
            || $jenisCuti->kuota_hari <= 0
        ) {
            throw new InvalidArgumentException(
                'Jumlah kuota hari harus lebih dari nol.'
            );
        }
    }

    public function buatReferensiKejadian(): string
    {
        return (string) Str::uuid();
    }

    public function buatKodePeriodeNonTahunan(
        JenisCuti $jenisCuti,
        ?string $referensiKejadian = null
    ): string {
        if ($jenisCuti->periode_kuota === 'per_kejadian') {
            if (
                $referensiKejadian === null
                || !Str::isUuid($referensiKejadian)
            ) {
                throw new InvalidArgumentException(
                    'Referensi kejadian harus berupa UUID yang valid.'
                );
            }

            return 'KEJADIAN-' . strtolower($referensiKejadian);
        }

        if ($referensiKejadian !== null) {
            throw new InvalidArgumentException(
                'Referensi kejadian hanya berlaku untuk kuota per kejadian.'
            );
        }

        return match ($jenisCuti->periode_kuota) {
            'seumur_kerja' => 'SEUMUR-KERJA',
            'tanpa_batas' => 'TANPA-BATAS',
            default => throw new InvalidArgumentException(
                'Periode kuota non-tahunan tidak valid.'
            ),
        };
    }

    public function berikanKuotaNonTahunan(
        int $pegawaiId,
        int $jenisCutiId,
        ?string $referensiKejadian = null,
        ?int $userId = null
    ): KuotaCuti {
        return DB::transaction(function () use (
            $pegawaiId,
            $jenisCutiId,
            $referensiKejadian,
            $userId
        ) {
            $pegawai = Pegawai::query()
                ->lockForUpdate()
                ->findOrFail($pegawaiId);

            $jenisCuti = JenisCuti::query()
                ->lockForUpdate()
                ->findOrFail($jenisCutiId);

            $kodePeriode = $this->buatKodePeriodeNonTahunan(
                $jenisCuti,
                $referensiKejadian
            );

            $kuotaExisting = KuotaCuti::query()
                ->pegawai($pegawaiId)
                ->jenisCuti($jenisCutiId)
                ->where('kode_periode', $kodePeriode)
                ->lockForUpdate()
                ->first();

            if ($kuotaExisting) {
                return $kuotaExisting;
            }

            $this->validasiKebijakanNonTahunan($jenisCuti);

            if ($pegawai->status !== 'aktif') {
                throw new LogicException(
                    'Pegawai tidak berstatus aktif.'
                );
            }

            $tanggalMemenuhi = $this->akumulasiService
                ->tanggalMemenuhiMasaKerja(
                    $pegawai,
                    $jenisCuti
                );

            if ($tanggalMemenuhi === null) {
                throw new LogicException(
                    'Tanggal masuk pegawai belum tersedia.'
                );
            }

            if ($tanggalMemenuhi->greaterThan(today())) {
                throw new LogicException(
                    'Pegawai belum memenuhi minimal masa kerja.'
                );
            }

            $tanpaBatas =
                $jenisCuti->periode_kuota === 'tanpa_batas';

            $snapshot = $this->snapshotKebijakan($jenisCuti);

            $snapshot['referensi_kejadian'] =
                $referensiKejadian;

            $snapshot['tanggal_memenuhi_masa_kerja'] =
                $tanggalMemenuhi->toDateString();

            $kuota = KuotaCuti::query()->create([
                'pegawai_id' => $pegawaiId,
                'jenis_cuti_id' => $jenisCutiId,
                'kode_periode' => $kodePeriode,
                'tahun' => null,
                'tanggal_mulai_periode' =>
                $tanggalMemenuhi->toDateString(),
                'tanggal_akhir_periode' => null,
                'kuota_dasar' => $tanpaBatas
                    ? null
                    : (int) $jenisCuti->kuota_hari,
                'kuota_akumulasi' => 0,
                'kuota_penyesuaian' => 0,
                'kuota_terpakai' => 0,
                'kuota_direservasi' => 0,
                'kuota_hangus' => 0,
                'tahun_ke_siklus' => null,
                'kebijakan_snapshot' => $snapshot,
                'is_active' => true,
            ]);

            $kodeMutasi = $this->mutasiService
                ->buatKodeMutasi(
                    MutasiKuotaCuti::JENIS_PEMBERIAN_KUOTA,
                    $kuota->getKey(),
                    $kodePeriode
                );

            $this->mutasiService->catat(
                kuota: $kuota,
                jenisMutasi: MutasiKuotaCuti::JENIS_PEMBERIAN_KUOTA,
                jumlahHari: $tanpaBatas
                    ? 0
                    : (int) $jenisCuti->kuota_hari,
                saldoSebelum: $this->mutasiService
                    ->snapshotAwal($tanpaBatas),
                kodeMutasi: $kodeMutasi,
                keterangan: "Pemberian kuota {$jenisCuti->nama}.",
                userId: $userId,
                metadata: [
                    'periode_kuota' => $jenisCuti->periode_kuota,
                    'kode_periode' => $kodePeriode,
                    'referensi_kejadian' => $referensiKejadian,
                ]
            );

            return $kuota->refresh();
        }, 3);
    }

    public function cariKuotaNonTahunan(
        int $pegawaiId,
        int $jenisCutiId,
        string $kodePeriode
    ): ?KuotaCuti {
        return KuotaCuti::query()
            ->pegawai($pegawaiId)
            ->jenisCuti($jenisCutiId)
            ->whereNull('tahun')
            ->where('kode_periode', $kodePeriode)
            ->first();
    }

    public function jumlahReservasiPengajuan(
        int $kuotaId,
        int $pengajuanId
    ): int {
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

        $jumlah = 0;

        foreach ($mutasis as $mutasi) {
            $jumlah += match ($mutasi->jenis_mutasi) {
                MutasiKuotaCuti::JENIS_RESERVASI =>
                (int) $mutasi->jumlah_hari,

                MutasiKuotaCuti::JENIS_PELEPASAN_RESERVASI,
                MutasiKuotaCuti::JENIS_PENGGUNAAN =>
                -(int) $mutasi->jumlah_hari,

                default => 0,
            };
        }

        if ($jumlah < 0) {
            throw new LogicException(
                'Riwayat reservasi pengajuan tidak konsisten.'
            );
        }

        return $jumlah;
    }

    protected function validasiKepemilikanKuota(
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

    public function reservasiPengajuan(
        int $pengajuanId,
        ?int $userId = null
    ): array {
        return DB::transaction(function () use (
            $pengajuanId,
            $userId
        ) {
            $pengajuan = PengajuanCuti::query()
                ->lockForUpdate()
                ->findOrFail($pengajuanId);

            if (!$pengajuan->isDraft()) {
                throw new LogicException(
                    'Reservasi awal hanya dapat dilakukan pada pengajuan draft.'
                );
            }

            $details = DetailPengajuanCuti::query()
                ->where('pengajuan_cuti_id', $pengajuanId)
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            $detailDihitung = $details
                ->filter(fn($detail) => $detail->dihitung_kuota);

            if ($detailDihitung->isEmpty()) {
                throw new LogicException(
                    'Tidak terdapat tanggal cuti yang dihitung.'
                );
            }

            if (
                $detailDihitung->count()
                !== (int) $pengajuan->jumlah_hari_diajukan
            ) {
                throw new LogicException(
                    'Jumlah hari pengajuan tidak sesuai dengan detail tanggal.'
                );
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
                    throw new LogicException(
                        'Detail tanggal berada di luar rentang pengajuan.'
                    );
                }

                if (!$detail->dihitung_kuota) {
                    if (!$detail->isExcluded()) {
                        throw new LogicException(
                            'Tanggal yang tidak dihitung harus berstatus excluded.'
                        );
                    }

                    continue;
                }

                if (!$detail->bisaDiproses()) {
                    throw new LogicException(
                        'Terdapat detail cuti yang tidak dapat direservasi.'
                    );
                }

                if ($detail->kuota_cuti_id === null) {
                    throw new LogicException(
                        'Detail tanggal belum memiliki kuota cuti.'
                    );
                }
            }

            $kelompokKuota = $detailDihitung
                ->groupBy('kuota_cuti_id')
                ->sortKeys();

            $hasil = [];

            foreach ($kelompokKuota as $kuotaId => $daftarDetail) {
                $kuota = KuotaCuti::query()
                    ->lockForUpdate()
                    ->findOrFail($kuotaId);

                $this->validasiKepemilikanKuota(
                    $kuota,
                    $pengajuan
                );

                $jumlahHari = $daftarDetail->count();

                $tanggal = $daftarDetail
                    ->map(
                        fn($detail) =>
                        $detail->tanggal->toDateString()
                    )
                    ->sort()
                    ->values()
                    ->all();

                foreach ($daftarDetail as $detail) {
                    $this->validasiTanggalKuota(
                        $kuota,
                        $detail
                    );
                }

                $kodeMutasi = $this->mutasiService
                    ->buatKodeMutasi(
                        MutasiKuotaCuti::JENIS_RESERVASI,
                        $kuota->getKey(),
                        'pengajuan-' . $pengajuanId
                    );

                $mutasiExisting = MutasiKuotaCuti::query()
                    ->where('kode_mutasi', $kodeMutasi)
                    ->lockForUpdate()
                    ->first();

                if ($mutasiExisting) {
                    if (
                        (int) $mutasiExisting->pengajuan_cuti_id
                        !== $pengajuanId
                        || (int) $mutasiExisting->jumlah_hari
                        !== $jumlahHari
                        || ($mutasiExisting->metadata['tanggal'] ?? [])
                        !== $tanggal
                        || $this->jumlahReservasiPengajuan(
                            $kuota->getKey(),
                            $pengajuanId
                        ) !== $jumlahHari
                    ) {
                        throw new LogicException(
                            'Reservasi sebelumnya tidak sesuai dengan pengajuan.'
                        );
                    }

                    $hasil[] = [
                        'kuota_id' => $kuota->getKey(),
                        'jumlah_hari' => $jumlahHari,
                        'sudah_tercatat' => true,
                        'saldo' => $kuota->snapshotSaldo(),
                    ];

                    continue;
                }

                if (!$kuota->is_active) {
                    throw new LogicException(
                        'Periode kuota tidak aktif.'
                    );
                }

                $saldoSebelum = $kuota->snapshotSaldo();

                $this->mutasiService->validasiSnapshot(
                    $saldoSebelum
                );

                if (
                    !$kuota->tanpa_batas
                    && $kuota->sisa_kuota < $jumlahHari
                ) {
                    throw new LogicException(
                        'Sisa kuota cuti tidak mencukupi.'
                    );
                }

                $kuota->kuota_direservasi += $jumlahHari;

                $kuota->save();

                $this->mutasiService->catat(
                    kuota: $kuota,
                    jenisMutasi: MutasiKuotaCuti::JENIS_RESERVASI,
                    jumlahHari: $jumlahHari,
                    saldoSebelum: $saldoSebelum,
                    kodeMutasi: $kodeMutasi,
                    keterangan: "Reservasi kuota pengajuan cuti #{$pengajuanId}.",
                    userId: $userId,
                    pengajuanId: $pengajuanId,
                    metadata: [
                        'tanggal' => $tanggal,
                        'kode_periode' => $kuota->kode_periode,
                    ]
                );

                $hasil[] = [
                    'kuota_id' => $kuota->getKey(),
                    'jumlah_hari' => $jumlahHari,
                    'sudah_tercatat' => false,
                    'saldo' => $kuota->snapshotSaldo(),
                ];
            }

            return $hasil;
        }, 3);
    }
    public function pelepasanReservasiPengajuan(
        int $pengajuanId,
        array $detailIds,
        int $persetujuanId,
        int $userId
    ): array {
        return DB::transaction(function () use ($pengajuanId, $detailIds, $persetujuanId, $userId) {
            $pengajuan = PengajuanCuti::query()->lockForUpdate()->findOrFail($pengajuanId);
            $persetujuan = PersetujuanCuti::query()->lockForUpdate()->findOrFail($persetujuanId);

            if (
                !$pengajuan->isPending() || $pengajuan->terkunciOlehSk()
                || (int) $persetujuan->pengajuan_cuti_id !== $pengajuanId
                || !$persetujuan->isPending()
                || (int) $pengajuan->current_approver_id !== (int) $persetujuan->approver_id
                || (int) $persetujuan->approver->user_id !== $userId
                || $pengajuan->status !== 'pending_' . $persetujuan->role_approver
            ) {
                throw new LogicException('Persetujuan tidak berwenang atau tidak aktif.');
            }

            if (!$detailIds || count(array_unique($detailIds)) !== count($detailIds)) {
                throw new InvalidArgumentException('Daftar detail kosong atau duplikat.');
            }
            foreach ($detailIds as $id) {
                if (!is_int($id) || $id <= 0) {
                    throw new InvalidArgumentException('ID detail harus berupa integer positif.');
                }
            }

            $details = DetailPengajuanCuti::query()->where('pengajuan_cuti_id', $pengajuanId)
                ->whereIn('id', $detailIds)->orderBy('id')->lockForUpdate()->get();
            if ($details->count() !== count($detailIds)) {
                throw new LogicException('Detail bukan milik pengajuan.');
            }
            foreach ($details as $detail) {
                if (!$detail->dihitung_kuota || $detail->kuota_cuti_id === null) {
                    throw new LogicException('Detail tidak memiliki reservasi.');
                }
            }

            $hasil = [];
            foreach ($details->groupBy('kuota_cuti_id')->sortKeys() as $kuotaId => $daftar) {
                $kuota = KuotaCuti::query()->lockForUpdate()->findOrFail($kuotaId);
                $this->validasiKepemilikanKuota($kuota, $pengajuan);
                $tanggal = $daftar->map(fn($d) => $d->tanggal->toDateString())->sort()->values()->all();
                $ids = $daftar->pluck('id')->map(fn($id) => (int) $id)->sort()->values()->all();
                $kode = $this->mutasiService->buatKodeMutasi(
                    MutasiKuotaCuti::JENIS_PELEPASAN_RESERVASI,
                    $kuota->id,
                    'pengajuan-' . $pengajuanId . '-persetujuan-' . $persetujuanId . '-detail-' . implode(',', $ids)
                );
                $existing = MutasiKuotaCuti::query()->where('kode_mutasi', $kode)->lockForUpdate()->first();
                if ($existing) {
                    if (($existing->metadata['detail_ids'] ?? []) !== $ids
                        || ($existing->metadata['tanggal'] ?? []) !== $tanggal
                        || (int) $existing->persetujuan_cuti_id !== $persetujuanId
                        || $daftar->contains(fn($d) => !$d->isRejected() || (int) $d->ditolak_oleh !== (int) $persetujuan->approver_id)
                    ) {
                        throw new LogicException('Riwayat pelepasan tidak konsisten.');
                    }
                    $hasil[] = ['kuota_id' => $kuota->id, 'jumlah_hari' => count($ids), 'sudah_tercatat' => true, 'saldo' => $kuota->snapshotSaldo()];
                    continue;
                }
                $this->validasiPelepasanReservasi($kuota, $pengajuan, $daftar, $persetujuan);
                $saldo = $kuota->snapshotSaldo();
                $kuota->kuota_direservasi -= count($ids);
                $kuota->save();
                $this->mutasiService->catat(
                    kuota: $kuota,
                    jenisMutasi: MutasiKuotaCuti::JENIS_PELEPASAN_RESERVASI,
                    jumlahHari: count($ids),
                    saldoSebelum: $saldo,
                    kodeMutasi: $kode,
                    keterangan: "Pengembalian reservasi pengajuan #{$pengajuanId}.",
                    userId: $userId,
                    pengajuanId: $pengajuanId,
                    persetujuanId: $persetujuanId,
                    metadata: ['detail_ids' => $ids, 'tanggal' => $tanggal, 'kode_periode' => $kuota->kode_periode]
                );
                foreach ($daftar as $detail) {
                    $detail->update(['status' => DetailPengajuanCuti::STATUS_REJECTED, 'ditolak_oleh' => $persetujuan->approver_id, 'ditolak_pada' => now()]);
                }
                $hasil[] = ['kuota_id' => $kuota->id, 'jumlah_hari' => count($ids), 'sudah_tercatat' => false, 'saldo' => $kuota->snapshotSaldo()];
            }
            return $hasil;
        }, 3);
    }

    protected function validasiPelepasanReservasi(KuotaCuti $kuota, PengajuanCuti $pengajuan, $daftar, PersetujuanCuti $persetujuan): void
    {
        $this->mutasiService->validasiSnapshot($kuota->snapshotSaldo());
        $jumlah = $daftar->count();
        if ($jumlah > $kuota->kuota_direservasi || $jumlah > $this->jumlahReservasiPengajuan($kuota->id, $pengajuan->id)) {
            throw new LogicException('Reservasi pengajuan tidak mencukupi.');
        }
        $mutasis = MutasiKuotaCuti::query()->where('kuota_cuti_id', $kuota->id)
            ->where('pengajuan_cuti_id', $pengajuan->id)->orderBy('id')->lockForUpdate()->get();
        $tersedia = [];
        foreach ($mutasis as $mutasi) {
            if (!in_array($mutasi->jenis_mutasi, [MutasiKuotaCuti::JENIS_RESERVASI, MutasiKuotaCuti::JENIS_PELEPASAN_RESERVASI, MutasiKuotaCuti::JENIS_PENGGUNAAN], true)) {
                continue;
            }
            $tanggal = $mutasi->metadata['tanggal'] ?? [];
            if (count(array_unique($tanggal)) !== (int) $mutasi->jumlah_hari || count($tanggal) !== (int) $mutasi->jumlah_hari) {
                throw new LogicException('Riwayat tanggal reservasi tidak lengkap.');
            }
            foreach ($tanggal as $hari) {
                if ($mutasi->jenis_mutasi === MutasiKuotaCuti::JENIS_RESERVASI) {
                    if (isset($tersedia[$hari])) {
                        throw new LogicException('Reservasi tanggal ganda.');
                    }
                    $tersedia[$hari] = true;
                } else {
                    if (!isset($tersedia[$hari])) {
                        throw new LogicException('Riwayat pelepasan tanggal tidak konsisten.');
                    }
                    unset($tersedia[$hari]);
                }
            }
        }
        foreach ($daftar as $detail) {
            $hari = $detail->tanggal->toDateString();
            if (!$detail->bisaDiproses() || !isset($tersedia[$hari]) || !in_array($hari, $persetujuan->tanggal_diterima ?? [], true)) {
                throw new LogicException('Tanggal tidak tersedia untuk dilepaskan pada tahap ini.');
            }
            $this->validasiTanggalKuota($kuota, $detail);
        }
    }

    public function pelepasanSeluruhReservasi(int $pengajuanId, int $persetujuanId, int $userId): array
    {
        return DB::transaction(function () use ($pengajuanId, $persetujuanId, $userId) {
            $pengajuan = PengajuanCuti::query()->lockForUpdate()->findOrFail($pengajuanId);
            $ids = $pengajuan->detailBisaDiproses()->lockForUpdate()->get()->pluck('id')->map(fn($id) => (int) $id)->all();
            if (!$ids) {
                throw new LogicException('Tidak ada detail reservasi tersisa.');
            }
            return $this->pelepasanReservasiPengajuan($pengajuanId, $ids, $persetujuanId, $userId);
        }, 3);
    }
}
