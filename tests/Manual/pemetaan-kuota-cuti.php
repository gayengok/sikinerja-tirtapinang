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
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

require __DIR__ . '/../../vendor/autoload.php';

$app = require __DIR__ . '/../../bootstrap/app.php';

$app->make(Kernel::class)->bootstrap();

$service = app(PemetaanKuotaCutiService::class);
$pengajuanService = app(PengajuanCutiService::class);

$passed = 0;
$failed = 0;

function testCase(
    string $nama,
    callable $callback
): void {
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

function assertSameValue(
    mixed $expected,
    mixed $actual
): void {
    if ($expected !== $actual) {
        throw new RuntimeException(
            'Expected: '
                . var_export($expected, true)
                . ' | Actual: '
                . var_export($actual, true)
        );
    }
}

function assertValidationException(
    callable $callback,
    string $field
): void {
    try {
        $callback();
    } catch (ValidationException $e) {
        if (!isset($e->errors()[$field])) {
            throw new RuntimeException(
                "Field validasi {$field} tidak ditemukan: "
                    . $e->getMessage()
            );
        }

        return;
    }

    throw new RuntimeException(
        'ValidationException tidak terjadi.'
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

function buatJenisCuti(
    array $attributes = []
): JenisCuti {
    return JenisCuti::query()->create(
        array_merge([
            'kode' => 'T-' . Str::upper(
                Str::random(6)
            ),
            'nama' => 'Cuti Pengujian',
            'deskripsi' => 'Pengujian pemetaan kuota',
            'kuota_hari' => 12,
            'periode_kuota' => 'tahunan',
            'minimal_hari_pengajuan' => 7,
            'maksimal_hari_pengajuan' => null,
            'boleh_hari_h' => false,
            'metode_hitung_hari' => 'hari_kerja',
            'minimal_masa_kerja_bulan' => 12,
            'bisa_akumulasi' => false,
            'tambahan_akumulasi_hari' => 0,
            'maksimal_tahun_akumulasi' => 0,
            'tahun_awal_siklus' => null,
            'wajib_lampiran' => false,
            'is_active' => true,
        ], $attributes)
    );
}

function denganRollback(
    callable $callback
): void {
    DB::beginTransaction();

    try {
        $callback();
    } finally {
        DB::rollBack();
    }
}

echo PHP_EOL;
echo "========================================" . PHP_EOL;
echo " PENGUJIAN PEMETAAN KUOTA CUTI" . PHP_EOL;
echo "========================================" . PHP_EOL;
echo PHP_EOL;

$dataAwal = jumlahData();

$waktu = CarbonImmutable::parse(
    '2026-11-23 09:00:00',
    'Asia/Jakarta'
);

Carbon::setTestNow($waktu);
CarbonImmutable::setTestNow($waktu);

testCase(
    'Pemetaan di luar transaksi ditolak',
    function () use ($service) {
        try {
            $service->petakan(1, 1);
        } catch (LogicException $e) {
            assertSameValue(
                'Pemetaan kuota wajib berada dalam transaksi pengajuan cuti.',
                $e->getMessage()
            );

            return;
        }

        throw new RuntimeException(
            'LogicException tidak terjadi.'
        );
    }
);

DB::beginTransaction();

try {
    $pegawai = Pegawai::findOrFail(2);
    $pegawaiLain = Pegawai::findOrFail(3);

    $pegawai->update([
        'tanggal_masuk' => '2020-01-01',
    ]);

    $userId = (int) $pegawai->user_id;

    $buatDraft = function (
        JenisCuti $jenisCuti,
        string $mulai = '2026-11-30',
        string $selesai = '2026-12-06'
    ) use (
        $pengajuanService,
        $userId
    ): PengajuanCuti {
        return $pengajuanService->buatDraft(
            $userId,
            $jenisCuti->id,
            $mulai,
            $selesai,
            'Pengujian pemetaan kuota'
        );
    };

    $jenisTahunan = buatJenisCuti();

    $draft = $buatDraft($jenisTahunan);

    $hasil = null;

    testCase(
        'Pemetaan kuota tahunan berhasil',
        function () use (
            $service,
            $draft,
            $userId,
            &$hasil
        ) {
            $hasil = $service->petakan(
                $draft->id,
                $userId
            );

            assertSameValue(
                'tahunan',
                $hasil['periode_kuota']
            );
        }
    );

    testCase(
        'Hasil pemetaan memuat ID pengajuan',
        function () use ($hasil, $draft) {
            assertSameValue(
                (int) $draft->id,
                $hasil['pengajuan_cuti_id']
            );
        }
    );

    testCase(
        'Pemetaan tahunan menggunakan periode 2026',
        function () use ($hasil) {
            assertSameValue(
                '2026',
                $hasil['pemetaan'][0]['kode_periode']
            );
        }
    );

    testCase(
        'Lima hari kerja dipetakan',
        function () use ($hasil) {
            assertSameValue(
                5,
                $hasil['jumlah_hari']
            );
        }
    );

    testCase(
        'Pemetaan menggunakan satu kuota tahunan',
        function () use ($hasil) {
            assertSameValue(
                1,
                $hasil['jumlah_kuota']
            );
        }
    );

    testCase(
        'Seluruh detail dihitung terhubung ke kuota',
        function () use ($draft, $hasil) {
            assertSameValue(
                5,
                $draft->details()
                    ->where(
                        'kuota_cuti_id',
                        $hasil['pemetaan'][0]['kuota_id']
                    )
                    ->count()
            );
        }
    );

    testCase(
        'Detail excluded tidak terhubung ke kuota',
        function () use ($draft) {
            assertSameValue(
                2,
                $draft->details()
                    ->where('dihitung_kuota', false)
                    ->whereNull('kuota_cuti_id')
                    ->count()
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
        'Pemetaan belum melakukan reservasi',
        function () use ($hasil) {
            $kuota = KuotaCuti::findOrFail(
                $hasil['pemetaan'][0]['kuota_id']
            );

            assertSameValue(
                0,
                $kuota->kuota_direservasi
            );

            assertSameValue(
                0,
                $kuota->kuota_terpakai
            );

            assertSameValue(
                12,
                $kuota->sisa_kuota
            );
        }
    );

    testCase(
        'Pemberian kuota baru menghasilkan mutasi',
        function () use ($hasil) {
            assertSameValue(
                1,
                MutasiKuotaCuti::query()
                    ->where(
                        'kuota_cuti_id',
                        $hasil['pemetaan'][0]['kuota_id']
                    )
                    ->where(
                        'jenis_mutasi',
                        MutasiKuotaCuti::JENIS_PEMBERIAN_KUOTA
                    )
                    ->count()
            );
        }
    );

    testCase(
        'Pemetaan tidak menghasilkan mutasi reservasi',
        function () use ($draft) {
            assertSameValue(
                0,
                MutasiKuotaCuti::query()
                    ->where(
                        'pengajuan_cuti_id',
                        $draft->id
                    )
                    ->where(
                        'jenis_mutasi',
                        MutasiKuotaCuti::JENIS_RESERVASI
                    )
                    ->count()
            );
        }
    );

    testCase(
        'Pemetaan berulang pada draft yang sama ditolak',
        function () use (
            $service,
            $draft,
            $userId
        ) {
            assertValidationException(
                fn() => $service->petakan(
                    $draft->id,
                    $userId
                ),
                'status'
            );
        }
    );

    testCase(
        'Kuota tahunan yang tersedia digunakan kembali',
        function () use (
            $service,
            $buatDraft,
            $jenisTahunan,
            $userId,
            $hasil
        ) {
            $baru = $buatDraft($jenisTahunan);

            $pemetaan = $service->petakan(
                $baru->id,
                $userId
            );

            assertSameValue(
                $hasil['pemetaan'][0]['kuota_id'],
                $pemetaan['pemetaan'][0]['kuota_id']
            );
        }
    );

    testCase(
        'Kuota per kejadian berhasil dipetakan',
        function () use (
            $service,
            $buatDraft,
            $userId
        ) {
            $jenis = buatJenisCuti([
                'periode_kuota' => 'per_kejadian',
                'kuota_hari' => 5,
            ]);

            $baru = $buatDraft($jenis);

            $pemetaan = $service->petakan(
                $baru->id,
                $userId
            );

            assertSameValue(
                true,
                Str::isUuid(
                    $pemetaan['referensi_kejadian']
                )
            );

            assertSameValue(
                'KEJADIAN-'
                    . $pemetaan['referensi_kejadian'],
                $pemetaan['pemetaan'][0]['kode_periode']
            );

            $baruKedua = $buatDraft($jenis);

            $pemetaanKedua = $service->petakan(
                $baruKedua->id,
                $userId
            );

            assertSameValue(
                false,
                $pemetaan['referensi_kejadian']
                    === $pemetaanKedua['referensi_kejadian']
            );

            assertSameValue(
                false,
                $pemetaan['pemetaan'][0]['kuota_id']
                    === $pemetaanKedua['pemetaan'][0]['kuota_id']
            );
        }
    );

    testCase(
        'Kuota seumur kerja digunakan kembali',
        function () use (
            $service,
            $buatDraft,
            $userId
        ) {
            $jenis = buatJenisCuti([
                'periode_kuota' => 'seumur_kerja',
                'kuota_hari' => 30,
            ]);

            $pertama = $buatDraft($jenis);
            $kedua = $buatDraft($jenis);

            $hasilPertama = $service->petakan(
                $pertama->id,
                $userId
            );

            $hasilKedua = $service->petakan(
                $kedua->id,
                $userId
            );

            assertSameValue(
                'SEUMUR-KERJA',
                $hasilPertama['pemetaan'][0]['kode_periode']
            );

            assertSameValue(
                $hasilPertama['pemetaan'][0]['kuota_id'],
                $hasilKedua['pemetaan'][0]['kuota_id']
            );
        }
    );

    testCase(
        'Kuota tanpa batas berhasil dipetakan',
        function () use (
            $service,
            $buatDraft,
            $userId
        ) {
            $jenis = buatJenisCuti([
                'periode_kuota' => 'tanpa_batas',
                'kuota_hari' => null,
            ]);

            $baru = $buatDraft($jenis);

            $pemetaan = $service->petakan(
                $baru->id,
                $userId
            );

            assertSameValue(
                'TANPA-BATAS',
                $pemetaan['pemetaan'][0]['kode_periode']
            );

            assertSameValue(
                true,
                $pemetaan['pemetaan'][0]['tanpa_batas']
            );

            assertSameValue(
                null,
                $pemetaan['pemetaan'][0]['saldo']['sisa_kuota']
            );
        }
    );

    testCase(
        'Metode hari kalender memetakan akhir pekan',
        function () use (
            $service,
            $buatDraft,
            $userId
        ) {
            $jenis = buatJenisCuti([
                'metode_hitung_hari' => 'hari_kalender',
            ]);

            $baru = $buatDraft($jenis);

            $pemetaan = $service->petakan(
                $baru->id,
                $userId
            );

            assertSameValue(
                7,
                $pemetaan['jumlah_hari']
            );

            assertSameValue(
                7,
                $baru->details()
                    ->whereNotNull('kuota_cuti_id')
                    ->count()
            );
        }
    );

    testCase(
        'Kuota tidak mencukupi ditolak dan di-rollback',
        function () use (
            $service,
            $buatDraft,
            $userId
        ) {
            $jenis = buatJenisCuti([
                'kuota_hari' => 2,
            ]);

            $baru = $buatDraft($jenis);

            $sebelum = jumlahData();

            assertValidationException(
                fn() => $service->petakan(
                    $baru->id,
                    $userId
                ),
                'kuota_cuti'
            );

            assertSameValue(
                $sebelum,
                jumlahData()
            );

            assertSameValue(
                0,
                $baru->details()
                    ->whereNotNull('kuota_cuti_id')
                    ->count()
            );
        }
    );

    testCase(
        'Pengajuan lintas tahun mendatang ditolak secara atomik',
        function () use (
            $service,
            $buatDraft,
            $userId
        ) {
            $jenis = buatJenisCuti();

            $baru = $buatDraft(
                $jenis,
                '2026-12-30',
                '2027-01-05'
            );

            $sebelum = jumlahData();

            assertValidationException(
                fn() => $service->petakan(
                    $baru->id,
                    $userId
                ),
                'kuota_cuti'
            );

            assertSameValue(
                $sebelum,
                jumlahData()
            );

            assertSameValue(
                0,
                $baru->details()
                    ->whereNotNull('kuota_cuti_id')
                    ->count()
            );
        }
    );

    testCase(
        'Pengajuan pegawai lain tidak dapat dipetakan',
        function () use (
            $service,
            $buatDraft,
            $jenisTahunan,
            $pegawaiLain
        ) {
            $baru = $buatDraft($jenisTahunan);

            assertValidationException(
                fn() => $service->petakan(
                    $baru->id,
                    $pegawaiLain->user_id
                ),
                'pengajuan_cuti_id'
            );
        }
    );

    testCase(
        'Jenis cuti nonaktif ditolak',
        function () use (
            $service,
            $buatDraft,
            $userId
        ) {
            $jenis = buatJenisCuti();

            $baru = $buatDraft($jenis);

            $jenis->update([
                'is_active' => false,
            ]);

            assertValidationException(
                fn() => $service->petakan(
                    $baru->id,
                    $userId
                ),
                'jenis_cuti_id'
            );
        }
    );

    testCase(
        'Pegawai nonaktif ditolak',
        function () use (
            $service,
            $buatDraft,
            $jenisTahunan,
            $pegawai,
            $userId
        ) {
            denganRollback(function () use (
                $service,
                $buatDraft,
                $jenisTahunan,
                $pegawai,
                $userId
            ) {
                $baru = $buatDraft($jenisTahunan);

                $pegawai->fresh()->update([
                    'status' => 'nonaktif',
                ]);

                assertValidationException(
                    fn() => $service->petakan(
                        $baru->id,
                        $userId
                    ),
                    'pegawai_id'
                );
            });
        }
    );

    foreach (
        [
            PengajuanCuti::STATUS_PENDING_ATASAN,
            PengajuanCuti::STATUS_PENDING_KEPALA_BAGIAN,
            PengajuanCuti::STATUS_PENDING_DIREKTUR,
            PengajuanCuti::STATUS_APPROVED,
            PengajuanCuti::STATUS_PARTIALLY_APPROVED,
            PengajuanCuti::STATUS_REJECTED,
            PengajuanCuti::STATUS_CANCELLED,
        ] as $status
    ) {
        testCase(
            "Status {$status} tidak dapat dipetakan",
            function () use (
                $service,
                $buatDraft,
                $jenisTahunan,
                $userId,
                $status
            ) {
                denganRollback(function () use (
                    $service,
                    $buatDraft,
                    $jenisTahunan,
                    $userId,
                    $status
                ) {
                    $baru = $buatDraft($jenisTahunan);

                    $baru->update([
                        'status' => $status,
                    ]);

                    assertValidationException(
                        fn() => $service->petakan(
                            $baru->id,
                            $userId
                        ),
                        'status'
                    );
                });
            }
        );
    }

    testCase(
        'Jumlah hari pengajuan tidak konsisten ditolak',
        function () use (
            $service,
            $buatDraft,
            $jenisTahunan,
            $userId
        ) {
            $baru = $buatDraft($jenisTahunan);

            $baru->update([
                'jumlah_hari_diajukan' => 10,
            ]);

            assertValidationException(
                fn() => $service->petakan(
                    $baru->id,
                    $userId
                ),
                'tanggal_selesai'
            );
        }
    );

    testCase(
        'Detail tanggal tidak lengkap ditolak',
        function () use (
            $service,
            $buatDraft,
            $jenisTahunan,
            $userId
        ) {
            $baru = $buatDraft($jenisTahunan);

            $baru->details()->firstOrFail()->delete();

            assertValidationException(
                fn() => $service->petakan(
                    $baru->id,
                    $userId
                ),
                'tanggal_selesai'
            );
        }
    );

    testCase(
        'Detail yang sudah disetujui ditolak',
        function () use (
            $service,
            $buatDraft,
            $jenisTahunan,
            $userId
        ) {
            $baru = $buatDraft($jenisTahunan);

            $baru->details()
                ->where('dihitung_kuota', true)
                ->firstOrFail()
                ->update([
                    'status' =>
                    DetailPengajuanCuti::STATUS_APPROVED,
                ]);

            assertValidationException(
                fn() => $service->petakan(
                    $baru->id,
                    $userId
                ),
                'status'
            );
        }
    );

    testCase(
        'Kuota nonaktif tidak dapat digunakan',
        function () use (
            $service,
            $buatDraft,
            $jenisTahunan,
            $userId,
            $hasil
        ) {
            denganRollback(function () use (
                $service,
                $buatDraft,
                $jenisTahunan,
                $userId,
                $hasil
            ) {
                KuotaCuti::findOrFail(
                    $hasil['pemetaan'][0]['kuota_id']
                )->update([
                    'is_active' => false,
                ]);

                $baru = $buatDraft($jenisTahunan);

                assertValidationException(
                    fn() => $service->petakan(
                        $baru->id,
                        $userId
                    ),
                    'kuota_cuti'
                );
            });
        }
    );

    testCase(
        'Saldo kuota yang sudah direservasi diperhitungkan',
        function () use (
            $service,
            $buatDraft,
            $jenisTahunan,
            $userId,
            $hasil
        ) {
            denganRollback(function () use (
                $service,
                $buatDraft,
                $jenisTahunan,
                $userId,
                $hasil
            ) {
                KuotaCuti::findOrFail(
                    $hasil['pemetaan'][0]['kuota_id']
                )->update([
                    'kuota_direservasi' => 10,
                ]);

                $baru = $buatDraft($jenisTahunan);

                assertValidationException(
                    fn() => $service->petakan(
                        $baru->id,
                        $userId
                    ),
                    'kuota_cuti'
                );
            });
        }
    );

    testCase(
        'Pemetaan mendukung rollback transaksi luar',
        function () use (
            $service,
            $buatDraft,
            $userId
        ) {
            $jenis = buatJenisCuti();

            $baru = $buatDraft($jenis);

            $sebelum = jumlahData();

            denganRollback(function () use (
                $service,
                $baru,
                $userId
            ) {
                $service->petakan(
                    $baru->id,
                    $userId
                );

                assertSameValue(
                    5,
                    $baru->details()
                        ->whereNotNull('kuota_cuti_id')
                        ->count()
                );
            });

            assertSameValue(
                $sebelum,
                jumlahData()
            );

            assertSameValue(
                0,
                $baru->details()
                    ->whereNotNull('kuota_cuti_id')
                    ->count()
            );
        }
    );

    testCase(
        'Pemetaan tidak membentuk data persetujuan',
        function () use ($dataAwal) {
            assertSameValue(
                $dataAwal['persetujuan'],
                PersetujuanCuti::count()
            );
        }
    );
} catch (Throwable $e) {
    $failed++;

    echo '[FAIL] Pengujian utama: '
        . $e->getMessage()
        . PHP_EOL;
} finally {
    DB::rollBack();

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
echo "Total pengujian : " . ($passed + $failed) . PHP_EOL;
echo "PASS            : {$passed}" . PHP_EOL;
echo "FAIL            : {$failed}" . PHP_EOL;
echo "========================================" . PHP_EOL;

exit($failed > 0 ? 1 : 0);