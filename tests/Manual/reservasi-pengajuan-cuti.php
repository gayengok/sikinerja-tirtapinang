<?php

use App\Models\DetailPengajuanCuti;
use App\Models\JenisCuti;
use App\Models\KuotaCuti;
use App\Models\MutasiKuotaCuti;
use App\Models\Pegawai;
use App\Models\PengajuanCuti;
use App\Models\PersetujuanCuti;
use App\Services\Cuti\PemetaanKuotaCutiService;
use App\Services\Cuti\PengajuanCutiService;
use App\Services\Cuti\KuotaCutiService;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

require __DIR__ . '/../../vendor/autoload.php';

$app = require __DIR__ . '/../../bootstrap/app.php';

$app->make(Kernel::class)->bootstrap();

$pengajuanService = app(PengajuanCutiService::class);
$pemetaanService = app(PemetaanKuotaCutiService::class);
$kuotaService = app(KuotaCutiService::class);

$passed = 0;
$failed = 0;

function testCase(string $nama, callable $callback): void
{
    global $passed, $failed;

    try {
        $callback();

        $passed++;

        echo "[PASS] {$nama}" . PHP_EOL;
    } catch (Throwable $e) {
        $failed++;

        echo "[FAIL] {$nama}" . PHP_EOL;
        echo '       ' . $e->getMessage() . PHP_EOL;
    }
}

function assertSameValue($expected, $actual): void
{
    if ($expected !== $actual) {
        throw new RuntimeException(
            'Expected: ' . var_export($expected, true)
                . PHP_EOL
                . 'Actual: ' . var_export($actual, true)
        );
    }
}

function assertLogicException(callable $callback): void
{
    try {
        $callback();
    } catch (LogicException $e) {
        return;
    }

    throw new RuntimeException(
        'LogicException tidak ditemukan.'
    );
}

function jumlahData(): array
{
    return [
        'jenis_cuti' => JenisCuti::count(),
        'pengajuan' => PengajuanCuti::count(),
        'detail' => DetailPengajuanCuti::count(),
        'kuota' => KuotaCuti::count(),
        'mutasi' => MutasiKuotaCuti::count(),
        'persetujuan' => PersetujuanCuti::count(),
    ];
}

function buatJenisCuti(array $attributes = []): JenisCuti
{
    return JenisCuti::query()->create(
        array_merge([
            'kode' => 'T-' . Str::upper(Str::random(6)),
            'nama' => 'Jenis Cuti Pengujian Reservasi',
            'deskripsi' => 'Data sementara pengujian reservasi',
            'kuota_hari' => 12,
            'periode_kuota' => 'tahunan',
            'minimal_hari_pengajuan' => 0,
            'maksimal_hari_pengajuan' => null,
            'boleh_hari_h' => true,
            'metode_hitung_hari' => 'hari_kerja',
            'minimal_masa_kerja_bulan' => 0,
            'bisa_akumulasi' => false,
            'tambahan_akumulasi_hari' => 0,
            'maksimal_tahun_akumulasi' => 0,
            'tahun_awal_siklus' => null,
            'wajib_lampiran' => false,
            'is_active' => true,
        ], $attributes)
    );
}

function cariKuotaDraft(PengajuanCuti $draft): KuotaCuti
{
    $detail = $draft->details()
        ->where('dihitung_kuota', true)
        ->whereNotNull('kuota_cuti_id')
        ->firstOrFail();

    return KuotaCuti::query()
        ->findOrFail($detail->kuota_cuti_id);
}

function mutasiReservasi(int $pengajuanId)
{
    return MutasiKuotaCuti::query()
        ->where('pengajuan_cuti_id', $pengajuanId)
        ->where(
            'jenis_mutasi',
            MutasiKuotaCuti::JENIS_RESERVASI
        );
}

echo PHP_EOL;
echo "========================================" . PHP_EOL;
echo " PENGUJIAN RESERVASI PENGAJUAN CUTI" . PHP_EOL;
echo "========================================" . PHP_EOL;
echo PHP_EOL;

$dataAwal = jumlahData();

$waktuPengujian = CarbonImmutable::parse(
    '2026-11-23 09:00:00',
    'Asia/Jakarta'
);

Carbon::setTestNow($waktuPengujian);
CarbonImmutable::setTestNow($waktuPengujian);

$levelAwal = DB::transactionLevel();

DB::beginTransaction();

try {
    $pegawai = Pegawai::query()
        ->where('status', 'aktif')
        ->whereNotNull('tanggal_masuk')
        ->whereDate('tanggal_masuk', '<=', '2026-11-23')
        ->whereHas('user', function ($query) {
            $query->where('role', 'pegawai');
        })
        ->firstOrFail();

    $buatDraft = function (
        JenisCuti $jenisCuti,
        string $mulai = '2026-11-30',
        string $selesai = '2026-12-06'
    ) use (
        $pengajuanService,
        $pegawai
    ): PengajuanCuti {
        return $pengajuanService->buatDraft(
            $pegawai->user_id,
            $jenisCuti->getKey(),
            $mulai,
            $selesai,
            'Keperluan pengujian reservasi'
        );
    };

    $petakan = function (
        PengajuanCuti $draft
    ) use (
        $pemetaanService,
        $pegawai
    ): KuotaCuti {
        $pemetaanService->petakan(
            $draft->getKey(),
            $pegawai->user_id
        );

        return cariKuotaDraft($draft);
    };

    $reservasi = function (
        PengajuanCuti $draft
    ) use (
        $kuotaService,
        $pegawai
    ): array {
        return $kuotaService->reservasiPengajuan(
            $draft->getKey(),
            $pegawai->user_id
        );
    };

    $jenisTahunan = buatJenisCuti();

    $draft = $buatDraft($jenisTahunan);

    $kuota = $petakan($draft);

    $saldoSebelum = $kuota->snapshotSaldo();

    $hasil = null;

    testCase(
        'Kuota sebelum reservasi memiliki saldo 12 hari',
        function () use ($kuota) {
            assertSameValue(12, $kuota->sisa_kuota);
            assertSameValue(0, $kuota->kuota_direservasi);
        }
    );

    testCase(
        'Reservasi lima hari kerja berhasil',
        function () use ($draft, $reservasi, &$hasil) {
            $hasil = $reservasi($draft);

            assertSameValue(1, count($hasil));
            assertSameValue(5, $hasil[0]['jumlah_hari']);
            assertSameValue(false, $hasil[0]['sudah_tercatat']);
        }
    );

    testCase(
        'Hasil reservasi mengembalikan ID kuota',
        function () use ($kuota, &$hasil) {
            assertSameValue(
                $kuota->getKey(),
                $hasil[0]['kuota_id']
            );
        }
    );

    testCase(
        'Kuota dasar tetap 12 hari',
        function () use ($kuota) {
            assertSameValue(
                12,
                $kuota->fresh()->kuota_dasar
            );
        }
    );

    testCase(
        'Kuota direservasi menjadi lima hari',
        function () use ($kuota) {
            assertSameValue(
                5,
                $kuota->fresh()->kuota_direservasi
            );
        }
    );

    testCase(
        'Sisa kuota menjadi tujuh hari',
        function () use ($kuota) {
            assertSameValue(
                7,
                $kuota->fresh()->sisa_kuota
            );
        }
    );

    testCase(
        'Kuota terpakai tetap nol',
        function () use ($kuota) {
            assertSameValue(
                0,
                $kuota->fresh()->kuota_terpakai
            );
        }
    );

    testCase(
        'Kuota hangus tetap nol',
        function () use ($kuota) {
            assertSameValue(
                0,
                $kuota->fresh()->kuota_hangus
            );
        }
    );

    testCase(
        'Status pengajuan tetap draft',
        function () use ($draft) {
            assertSameValue(
                PengajuanCuti::STATUS_DRAFT,
                $draft->fresh()->status
            );
        }
    );

    testCase(
        'Detail hari kerja tetap berstatus pending',
        function () use ($draft) {
            assertSameValue(
                5,
                $draft->details()
                    ->where(
                        'status',
                        DetailPengajuanCuti::STATUS_PENDING
                    )
                    ->count()
            );
        }
    );

    testCase(
        'Detail excluded tidak ikut direservasi',
        function () use ($draft) {
            assertSameValue(
                2,
                $draft->details()
                    ->where(
                        'status',
                        DetailPengajuanCuti::STATUS_EXCLUDED
                    )
                    ->whereNull('kuota_cuti_id')
                    ->count()
            );
        }
    );

    testCase(
        'Reservasi menghasilkan satu mutasi',
        function () use ($draft) {
            assertSameValue(
                1,
                mutasiReservasi($draft->getKey())->count()
            );
        }
    );

    testCase(
        'Mutasi mencatat jumlah lima hari',
        function () use ($draft) {
            $mutasi = mutasiReservasi(
                $draft->getKey()
            )->firstOrFail();

            assertSameValue(5, $mutasi->jumlah_hari);
        }
    );

    testCase(
        'Mutasi mencatat pengurangan saldo lima hari',
        function () use ($draft) {
            $mutasi = mutasiReservasi(
                $draft->getKey()
            )->firstOrFail();

            assertSameValue(-5, $mutasi->perubahan_sisa);
        }
    );

    testCase(
        'Mutasi mencatat akun pemohon',
        function () use ($draft, $pegawai) {
            $mutasi = mutasiReservasi(
                $draft->getKey()
            )->firstOrFail();

            assertSameValue(
                (int) $pegawai->user_id,
                (int) $mutasi->user_id
            );
        }
    );

    testCase(
        'Snapshot saldo sebelum reservasi sesuai',
        function () use ($draft, $saldoSebelum) {
            $expected = $saldoSebelum;

            $actual = mutasiReservasi(
                $draft->getKey()
            )->firstOrFail()->saldo_sebelum;

            ksort($expected);
            ksort($actual);

            assertSameValue($expected, $actual);
        }
    );

    testCase(
        'Snapshot saldo sesudah reservasi sesuai',
        function () use ($draft, $kuota) {
            $expected = $kuota->fresh()->snapshotSaldo();

            $actual = mutasiReservasi(
                $draft->getKey()
            )->firstOrFail()->saldo_sesudah;

            ksort($expected);
            ksort($actual);

            assertSameValue($expected, $actual);
        }
    );

    testCase(
        'Metadata mutasi mencatat tanggal yang benar',
        function () use ($draft) {
            $mutasi = mutasiReservasi(
                $draft->getKey()
            )->firstOrFail();

            assertSameValue([
                '2026-11-30',
                '2026-12-01',
                '2026-12-02',
                '2026-12-03',
                '2026-12-04',
            ], $mutasi->metadata['tanggal']);
        }
    );

    testCase(
        'Metadata mutasi mencatat periode kuota',
        function () use ($draft) {
            $mutasi = mutasiReservasi(
                $draft->getKey()
            )->firstOrFail();

            assertSameValue(
                '2026',
                $mutasi->metadata['kode_periode']
            );
        }
    );

    testCase(
        'Jumlah reservasi pengajuan dapat dihitung',
        function () use ($draft, $kuota, $kuotaService) {
            assertSameValue(
                5,
                $kuotaService->jumlahReservasiPengajuan(
                    $kuota->getKey(),
                    $draft->getKey()
                )
            );
        }
    );

    testCase(
        'Reservasi berulang bersifat idempotent',
        function () use ($draft, $reservasi) {
            $hasil = $reservasi($draft);

            assertSameValue(
                true,
                $hasil[0]['sudah_tercatat']
            );

            assertSameValue(
                5,
                $hasil[0]['jumlah_hari']
            );
        }
    );

    testCase(
        'Reservasi berulang tidak menggandakan saldo',
        function () use ($kuota) {
            assertSameValue(
                5,
                $kuota->fresh()->kuota_direservasi
            );

            assertSameValue(
                7,
                $kuota->fresh()->sisa_kuota
            );
        }
    );

    testCase(
        'Reservasi berulang tidak menggandakan mutasi',
        function () use ($draft) {
            assertSameValue(
                1,
                mutasiReservasi($draft->getKey())->count()
            );
        }
    );

    testCase(
        'Reservasi tidak membentuk data persetujuan',
        function () use ($draft) {
            assertSameValue(
                0,
                PersetujuanCuti::query()
                    ->where(
                        'pengajuan_cuti_id',
                        $draft->getKey()
                    )
                    ->count()
            );
        }
    );

    testCase(
        'Draft tanpa pemetaan kuota ditolak',
        function () use ($buatDraft, $reservasi) {
            $jenis = buatJenisCuti();

            $draft = $buatDraft($jenis);

            assertLogicException(
                fn() => $reservasi($draft)
            );

            assertSameValue(
                0,
                mutasiReservasi($draft->getKey())->count()
            );
        }
    );

    testCase(
        'Reservasi kuota per kejadian berhasil',
        function () use ($buatDraft, $petakan, $reservasi) {
            $jenis = buatJenisCuti([
                'periode_kuota' => 'per_kejadian',
                'kuota_hari' => 10,
            ]);

            $draft = $buatDraft($jenis);

            $kuota = $petakan($draft);

            $reservasi($draft);

            $kuota->refresh();

            assertSameValue(
                true,
                str_starts_with(
                    $kuota->kode_periode,
                    'KEJADIAN-'
                )
            );

            assertSameValue(5, $kuota->kuota_direservasi);
            assertSameValue(5, $kuota->sisa_kuota);
        }
    );

    testCase(
        'Reservasi kuota seumur kerja berhasil',
        function () use ($buatDraft, $petakan, $reservasi) {
            $jenis = buatJenisCuti([
                'periode_kuota' => 'seumur_kerja',
                'kuota_hari' => 20,
            ]);

            $draft = $buatDraft($jenis);

            $kuota = $petakan($draft);

            $reservasi($draft);

            $kuota->refresh();

            assertSameValue(
                'SEUMUR-KERJA',
                $kuota->kode_periode
            );

            assertSameValue(5, $kuota->kuota_direservasi);
            assertSameValue(15, $kuota->sisa_kuota);
        }
    );

    testCase(
        'Reservasi kuota tanpa batas berhasil',
        function () use ($buatDraft, $petakan, $reservasi) {
            $jenis = buatJenisCuti([
                'periode_kuota' => 'tanpa_batas',
                'kuota_hari' => null,
            ]);

            $draft = $buatDraft($jenis);

            $kuota = $petakan($draft);

            $reservasi($draft);

            $kuota->refresh();

            assertSameValue(true, $kuota->tanpa_batas);
            assertSameValue(null, $kuota->sisa_kuota);
            assertSameValue(5, $kuota->kuota_direservasi);
            assertSameValue(0, $kuota->kuota_terpakai);
        }
    );

    testCase(
        'Mutasi kuota tanpa batas memiliki perubahan sisa null',
        function () use ($buatDraft, $petakan, $reservasi) {
            $jenis = buatJenisCuti([
                'periode_kuota' => 'tanpa_batas',
                'kuota_hari' => null,
            ]);

            $draft = $buatDraft($jenis);

            $petakan($draft);

            $reservasi($draft);

            $mutasi = mutasiReservasi(
                $draft->getKey()
            )->firstOrFail();

            assertSameValue(null, $mutasi->perubahan_sisa);
            assertSameValue(5, $mutasi->jumlah_hari);
        }
    );

    testCase(
        'Reservasi hari kalender menghitung tujuh hari',
        function () use ($buatDraft, $petakan, $reservasi) {
            $jenis = buatJenisCuti([
                'metode_hitung_hari' => 'hari_kalender',
            ]);

            $draft = $buatDraft($jenis);

            $kuota = $petakan($draft);

            $reservasi($draft);

            $kuota->refresh();

            assertSameValue(7, $kuota->kuota_direservasi);
            assertSameValue(5, $kuota->sisa_kuota);
        }
    );

    testCase(
        'Reservasi dapat menghabiskan saldo tepat nol',
        function () use ($buatDraft, $petakan, $reservasi) {
            $jenis = buatJenisCuti([
                'kuota_hari' => 5,
            ]);

            $draft = $buatDraft($jenis);

            $kuota = $petakan($draft);

            $reservasi($draft);

            $kuota->refresh();

            assertSameValue(5, $kuota->kuota_direservasi);
            assertSameValue(0, $kuota->sisa_kuota);
        }
    );

    testCase(
        'Saldo tidak mencukupi ditolak saat reservasi',
        function () use ($buatDraft, $petakan, $reservasi) {
            $jenis = buatJenisCuti([
                'kuota_hari' => 8,
            ]);

            $draftPertama = $buatDraft($jenis);

            $draftKedua = $buatDraft(
                $jenis,
                '2026-12-07',
                '2026-12-13'
            );

            $kuota = $petakan($draftPertama);

            $petakan($draftKedua);

            $reservasi($draftPertama);

            assertLogicException(
                fn() => $reservasi($draftKedua)
            );

            assertSameValue(
                5,
                $kuota->fresh()->kuota_direservasi
            );

            assertSameValue(
                3,
                $kuota->fresh()->sisa_kuota
            );

            assertSameValue(
                0,
                mutasiReservasi(
                    $draftKedua->getKey()
                )->count()
            );
        }
    );

    testCase(
        'Kuota nonaktif tidak dapat direservasi',
        function () use ($buatDraft, $petakan, $reservasi) {
            $jenis = buatJenisCuti();

            $draft = $buatDraft($jenis);

            $kuota = $petakan($draft);

            $kuota->update([
                'is_active' => false,
            ]);

            assertLogicException(
                fn() => $reservasi($draft)
            );

            assertSameValue(
                0,
                $kuota->fresh()->kuota_direservasi
            );
        }
    );

    testCase(
        'Kuota dengan jenis cuti berbeda ditolak',
        function () use ($buatDraft, $petakan, $reservasi) {
            $jenisPertama = buatJenisCuti();

            $jenisKedua = buatJenisCuti();

            $draftPertama = $buatDraft($jenisPertama);

            $draftKedua = $buatDraft($jenisKedua);

            $kuotaPertama = $petakan($draftPertama);

            $petakan($draftKedua);

            $draftKedua->details()
                ->where('dihitung_kuota', true)
                ->update([
                    'kuota_cuti_id' => $kuotaPertama->getKey(),
                ]);

            assertLogicException(
                fn() => $reservasi($draftKedua)
            );

            assertSameValue(
                0,
                $kuotaPertama->fresh()->kuota_direservasi
            );
        }
    );

    testCase(
        'Tanggal di luar periode kuota ditolak',
        function () use ($buatDraft, $petakan, $reservasi) {
            $jenis = buatJenisCuti();

            $draft = $buatDraft($jenis);

            $kuota = $petakan($draft);

            $kuota->update([
                'tanggal_akhir_periode' => '2026-11-29',
            ]);

            assertLogicException(
                fn() => $reservasi($draft)
            );

            assertSameValue(
                0,
                $kuota->fresh()->kuota_direservasi
            );
        }
    );

    testCase(
        'Jumlah hari pengajuan tidak konsisten ditolak',
        function () use ($buatDraft, $petakan, $reservasi) {
            $jenis = buatJenisCuti();

            $draft = $buatDraft($jenis);

            $kuota = $petakan($draft);

            $draft->update([
                'jumlah_hari_diajukan' => 6,
            ]);

            assertLogicException(
                fn() => $reservasi($draft)
            );

            assertSameValue(
                0,
                $kuota->fresh()->kuota_direservasi
            );
        }
    );

    testCase(
        'Detail yang telah disetujui tidak dapat direservasi',
        function () use ($buatDraft, $petakan, $reservasi) {
            $jenis = buatJenisCuti();

            $draft = $buatDraft($jenis);

            $kuota = $petakan($draft);

            $draft->details()
                ->where('dihitung_kuota', true)
                ->firstOrFail()
                ->update([
                    'status' => DetailPengajuanCuti::STATUS_APPROVED,
                ]);

            assertLogicException(
                fn() => $reservasi($draft)
            );

            assertSameValue(
                0,
                $kuota->fresh()->kuota_direservasi
            );
        }
    );

    testCase(
        'Detail tidak dihitung dengan status pending ditolak',
        function () use ($buatDraft, $petakan, $reservasi) {
            $jenis = buatJenisCuti();

            $draft = $buatDraft($jenis);

            $kuota = $petakan($draft);

            $draft->details()
                ->where('dihitung_kuota', false)
                ->firstOrFail()
                ->update([
                    'status' => DetailPengajuanCuti::STATUS_PENDING,
                ]);

            assertLogicException(
                fn() => $reservasi($draft)
            );

            assertSameValue(
                0,
                $kuota->fresh()->kuota_direservasi
            );
        }
    );

    $statusTerlarang = [
        PengajuanCuti::STATUS_PENDING_ATASAN,
        PengajuanCuti::STATUS_PENDING_KEPALA_BAGIAN,
        PengajuanCuti::STATUS_PENDING_DIREKTUR,
        PengajuanCuti::STATUS_APPROVED,
        PengajuanCuti::STATUS_PARTIALLY_APPROVED,
        PengajuanCuti::STATUS_REJECTED,
        PengajuanCuti::STATUS_CANCELLED,
    ];

    foreach ($statusTerlarang as $status) {
        testCase(
            "Pengajuan status {$status} tidak dapat direservasi",
            function () use (
                $status,
                $buatDraft,
                $petakan,
                $reservasi
            ) {
                $jenis = buatJenisCuti();

                $draft = $buatDraft($jenis);

                $kuota = $petakan($draft);

                $draft->update([
                    'status' => $status,
                ]);

                assertLogicException(
                    fn() => $reservasi($draft)
                );

                assertSameValue(
                    0,
                    $kuota->fresh()->kuota_direservasi
                );

                assertSameValue(
                    0,
                    mutasiReservasi(
                        $draft->getKey()
                    )->count()
                );
            }
        );
    }

    testCase(
        'Reservasi mendukung rollback transaksi luar',
        function () use ($buatDraft, $petakan, $reservasi) {
            $jenis = buatJenisCuti();

            $draft = $buatDraft($jenis);

            $kuota = $petakan($draft);

            $saldoAwal = $kuota->snapshotSaldo();

            $level = DB::transactionLevel();

            DB::beginTransaction();

            try {
                $reservasi($draft);

                assertSameValue(
                    5,
                    $kuota->fresh()->kuota_direservasi
                );

                assertSameValue(
                    1,
                    mutasiReservasi(
                        $draft->getKey()
                    )->count()
                );
            } finally {
                DB::rollBack($level);
            }

            assertSameValue(
                $saldoAwal,
                $kuota->fresh()->snapshotSaldo()
            );

            assertSameValue(
                0,
                mutasiReservasi(
                    $draft->getKey()
                )->count()
            );
        }
    );

    testCase(
        'Kegagalan pencatatan mutasi mengembalikan saldo',
        function () use ($buatDraft, $petakan, $reservasi) {
            $jenis = buatJenisCuti();

            $draft = $buatDraft($jenis);

            $kuota = $petakan($draft);

            $saldoAwal = $kuota->snapshotSaldo();

            $dispatcherAsli = MutasiKuotaCuti::getEventDispatcher();

            $dispatcher = clone $dispatcherAsli;

            $dispatcher->listen(
                'eloquent.creating: ' . MutasiKuotaCuti::class,
                function (MutasiKuotaCuti $mutasi) {
                    if (
                        $mutasi->jenis_mutasi
                        === MutasiKuotaCuti::JENIS_RESERVASI
                    ) {
                        throw new RuntimeException(
                            'Simulasi kegagalan mutasi reservasi'
                        );
                    }
                }
            );

            MutasiKuotaCuti::setEventDispatcher(
                $dispatcher
            );

            $gagal = false;

            try {
                $reservasi($draft);
            } catch (RuntimeException $e) {
                if (
                    $e->getMessage()
                    !== 'Simulasi kegagalan mutasi reservasi'
                ) {
                    throw $e;
                }

                $gagal = true;
            } finally {
                MutasiKuotaCuti::setEventDispatcher(
                    $dispatcherAsli
                );
            }

            assertSameValue(true, $gagal);

            assertSameValue(
                $saldoAwal,
                $kuota->fresh()->snapshotSaldo()
            );

            assertSameValue(
                0,
                mutasiReservasi(
                    $draft->getKey()
                )->count()
            );
        }
    );

    testCase(
        'Pemetaan dan reservasi dapat di-rollback bersama',
        function () use ($buatDraft, $petakan, $reservasi) {
            $jenis = buatJenisCuti();

            $draft = $buatDraft($jenis);

            $dataSebelum = jumlahData();

            $level = DB::transactionLevel();

            DB::beginTransaction();

            try {
                $kuota = $petakan($draft);

                $reservasi($draft);

                assertSameValue(
                    5,
                    $kuota->fresh()->kuota_direservasi
                );
            } finally {
                DB::rollBack($level);
            }

            assertSameValue(
                $dataSebelum,
                jumlahData()
            );

            assertSameValue(
                0,
                $draft->details()
                    ->whereNotNull('kuota_cuti_id')
                    ->count()
            );

            assertSameValue(
                PengajuanCuti::STATUS_DRAFT,
                $draft->fresh()->status
            );
        }
    );

    testCase(
        'Seluruh reservasi tidak membentuk data persetujuan',
        function () use ($dataAwal) {
            assertSameValue(
                $dataAwal['persetujuan'],
                PersetujuanCuti::count()
            );
        }
    );
} catch (Throwable $e) {
    $failed++;

    echo '[FAIL] Pengujian utama gagal' . PHP_EOL;
    echo '       ' . $e->getMessage() . PHP_EOL;
} finally {
    if (DB::transactionLevel() > $levelAwal) {
        DB::rollBack($levelAwal);
    }

    Carbon::setTestNow();
    CarbonImmutable::setTestNow();
}

testCase(
    'Seluruh data pengujian berhasil di-rollback',
    function () use ($dataAwal) {
        assertSameValue(
            $dataAwal,
            jumlahData()
        );
    }
);

echo PHP_EOL;
echo "========================================" . PHP_EOL;
echo 'Total pengujian : ' . ($passed + $failed) . PHP_EOL;
echo 'PASS            : ' . $passed . PHP_EOL;
echo 'FAIL            : ' . $failed . PHP_EOL;
echo "========================================" . PHP_EOL;

exit($failed > 0 ? 1 : 0);