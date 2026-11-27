<?php

use App\Models\DetailPengajuanCuti;
use App\Models\JenisCuti;
use App\Models\KuotaCuti;
use App\Models\MutasiKuotaCuti;
use App\Models\Pegawai;
use App\Models\PengajuanCuti;
use App\Models\PersetujuanCuti;
use App\Services\Cuti\BentrokCutiService;
use App\Services\Cuti\PengajuanCutiService;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

require __DIR__ . '/../../vendor/autoload.php';

$app = require __DIR__ . '/../../bootstrap/app.php';

$app->make(Kernel::class)->bootstrap();

$service = app(BentrokCutiService::class);

$pengajuanService = app(
    PengajuanCutiService::class
);

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
                "Field validasi {$field} tidak ditemukan."
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
            'nama' => 'Jenis Cuti Pengujian',
            'deskripsi' => 'Pengujian bentrok cuti',
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

function denganPerubahan(
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
echo " PENGUJIAN BENTROK TANGGAL CUTI" . PHP_EOL;
echo "========================================" . PHP_EOL;
echo PHP_EOL;

$dataAwal = jumlahData();

CarbonImmutable::setTestNow(
    CarbonImmutable::parse(
        '2026-11-23 09:00:00',
        'Asia/Jakarta'
    )
);

DB::beginTransaction();

try {
    $pegawai = Pegawai::findOrFail(2);

    $pegawaiLain = Pegawai::findOrFail(3);

    $jenisCuti = buatJenisCuti();

    $jenisCutiLain = buatJenisCuti();

    $draft = $pengajuanService->buatDraft(
        $pegawai->user_id,
        $jenisCuti->id,
        '2026-11-30',
        '2026-12-06',
        'Pengujian bentrok tanggal'
    );

    $pegawaiId = (int) $pegawai->id;

    $tanggal = '2026-12-01';

    $periksa = function (
        array $tanggal,
        ?int $kecualiId = null
    ) use (
        $service,
        $pegawaiId
    ): array {
        return $service->cariBentrok(
            $pegawaiId,
            $tanggal,
            $kecualiId
        );
    };

    testCase(
        'Tanggal tanpa pengajuan tidak bentrok',
        function () use ($periksa) {
            assertSameValue(
                [],
                $periksa(['2026-12-10'])
            );
        }
    );

    testCase(
        'Pengajuan draft tidak memblokir tanggal',
        function () use ($periksa, $tanggal) {
            assertSameValue(
                [],
                $periksa([$tanggal])
            );
        }
    );

    $statusAktif = [
        PengajuanCuti::STATUS_PENDING_ATASAN,
        PengajuanCuti::STATUS_PENDING_KEPALA_BAGIAN,
        PengajuanCuti::STATUS_PENDING_DIREKTUR,
        PengajuanCuti::STATUS_APPROVED,
        PengajuanCuti::STATUS_PARTIALLY_APPROVED,
    ];

    foreach ($statusAktif as $status) {
        testCase(
            "Status {$status} memblokir tanggal aktif",
            function () use (
                $draft,
                $status,
                $periksa,
                $tanggal
            ) {
                denganPerubahan(function () use (
                    $draft,
                    $status,
                    $periksa,
                    $tanggal
                ) {
                    $draft->fresh()->update([
                        'status' => $status,
                    ]);

                    $statusDetail = in_array(
                        $status,
                        [
                            PengajuanCuti::STATUS_APPROVED,
                            PengajuanCuti::STATUS_PARTIALLY_APPROVED,
                        ],
                        true
                    )
                        ? DetailPengajuanCuti::STATUS_APPROVED
                        : DetailPengajuanCuti::STATUS_PENDING;

                    $draft->details()
                        ->where('dihitung_kuota', true)
                        ->update([
                            'status' => $statusDetail,
                        ]);

                    $hasil = $periksa([$tanggal]);

                    assertSameValue(
                        1,
                        count($hasil)
                    );

                    assertSameValue(
                        $status,
                        $hasil[0]['status_pengajuan']
                    );

                    assertSameValue(
                        $statusDetail,
                        $hasil[0]['status_detail']
                    );
                });
            }
        );
    }

    foreach (
        [
            PengajuanCuti::STATUS_REJECTED,
            PengajuanCuti::STATUS_CANCELLED,
        ] as $status
    ) {
        testCase(
            "Status {$status} tidak memblokir tanggal",
            function () use (
                $draft,
                $status,
                $periksa,
                $tanggal
            ) {
                denganPerubahan(function () use (
                    $draft,
                    $status,
                    $periksa,
                    $tanggal
                ) {
                    $draft->fresh()->update([
                        'status' => $status,
                    ]);

                    assertSameValue(
                        [],
                        $periksa([$tanggal])
                    );
                });
            }
        );
    }

    $draft->refresh()->update([
        'status' =>
        PengajuanCuti::STATUS_PENDING_ATASAN,
    ]);

    testCase(
        'Detail pending memblokir tanggal',
        function () use ($periksa, $tanggal) {
            $hasil = $periksa([$tanggal]);

            assertSameValue(
                1,
                count($hasil)
            );

            assertSameValue(
                'pending',
                $hasil[0]['status_detail']
            );
        }
    );

    testCase(
        'Detail approved memblokir tanggal',
        function () use (
            $draft,
            $periksa,
            $tanggal
        ) {
            denganPerubahan(function () use (
                $draft,
                $periksa,
                $tanggal
            ) {
                $draft->details()
                    ->whereDate('tanggal', $tanggal)
                    ->update([
                        'status' =>
                        DetailPengajuanCuti::STATUS_APPROVED,
                    ]);

                assertSameValue(
                    1,
                    count($periksa([$tanggal]))
                );
            });
        }
    );

    foreach (
        [
            DetailPengajuanCuti::STATUS_REJECTED,
            DetailPengajuanCuti::STATUS_EXCLUDED,
            DetailPengajuanCuti::STATUS_CANCELLED,
        ] as $status
    ) {
        testCase(
            "Detail {$status} tidak memblokir tanggal",
            function () use (
                $draft,
                $status,
                $periksa,
                $tanggal
            ) {
                denganPerubahan(function () use (
                    $draft,
                    $status,
                    $periksa,
                    $tanggal
                ) {
                    $draft->details()
                        ->whereDate('tanggal', $tanggal)
                        ->update([
                            'status' => $status,
                        ]);

                    assertSameValue(
                        [],
                        $periksa([$tanggal])
                    );
                });
            }
        );
    }

    testCase(
        'Detail yang tidak dihitung kuota tidak bentrok',
        function () use (
            $draft,
            $periksa,
            $tanggal
        ) {
            denganPerubahan(function () use (
                $draft,
                $periksa,
                $tanggal
            ) {
                $draft->details()
                    ->whereDate('tanggal', $tanggal)
                    ->update([
                        'dihitung_kuota' => false,
                    ]);

                assertSameValue(
                    [],
                    $periksa([$tanggal])
                );
            });
        }
    );

    testCase(
        'Akhir pekan yang excluded tidak bentrok',
        function () use ($periksa) {
            assertSameValue(
                [],
                $periksa([
                    '2026-12-05',
                    '2026-12-06',
                ])
            );
        }
    );

    testCase(
        'Pegawai berbeda tidak mengalami bentrok',
        function () use (
            $service,
            $pegawaiLain,
            $tanggal
        ) {
            assertSameValue(
                [],
                $service->cariBentrok(
                    $pegawaiLain->id,
                    [$tanggal]
                )
            );
        }
    );

    testCase(
        'Pengajuan dapat dikecualikan dari pemeriksaan',
        function () use (
            $periksa,
            $draft,
            $tanggal
        ) {
            assertSameValue(
                [],
                $periksa(
                    [$tanggal],
                    $draft->id
                )
            );
        }
    );

    testCase(
        'Pengecualian ID lain tidak menghilangkan bentrok',
        function () use (
            $periksa,
            $draft,
            $tanggal
        ) {
            assertSameValue(
                1,
                count(
                    $periksa(
                        [$tanggal],
                        $draft->id + 100000
                    )
                )
            );
        }
    );

    testCase(
        'Bentrok mengembalikan identitas pengajuan',
        function () use (
            $periksa,
            $draft,
            $jenisCuti,
            $tanggal
        ) {
            $hasil = $periksa([$tanggal])[0];

            assertSameValue(
                (int) $draft->id,
                $hasil['pengajuan_cuti_id']
            );

            assertSameValue(
                (int) $jenisCuti->id,
                $hasil['jenis_cuti_id']
            );

            assertSameValue(
                $tanggal,
                $hasil['tanggal']
            );
        }
    );

    testCase(
        'Beberapa tanggal bentrok dikembalikan berurutan',
        function () use ($periksa) {
            $hasil = $periksa([
                '2026-12-03',
                '2026-12-01',
                '2026-12-02',
            ]);

            assertSameValue(
                [
                    '2026-12-01',
                    '2026-12-02',
                    '2026-12-03',
                ],
                array_column(
                    $hasil,
                    'tanggal'
                )
            );
        }
    );

    testCase(
        'Tanggal duplikat tidak menggandakan hasil',
        function () use ($periksa, $tanggal) {
            assertSameValue(
                1,
                count(
                    $periksa([
                        $tanggal,
                        $tanggal,
                    ])
                )
            );
        }
    );

    testCase(
        'Daftar tanggal kosong tidak bentrok',
        function () use ($periksa) {
            assertSameValue(
                [],
                $periksa([])
            );
        }
    );

    testCase(
        'Format tanggal tidak valid ditolak',
        function () use ($periksa) {
            assertValidationException(
                fn() => $periksa(['01-12-2026']),
                'tanggal'
            );
        }
    );

    testCase(
        'Tanggal kalender tidak valid ditolak',
        function () use ($periksa) {
            assertValidationException(
                fn() => $periksa(['2026-02-30']),
                'tanggal'
            );
        }
    );

    testCase(
        'Tanggal null ditolak',
        function () use ($periksa) {
            assertValidationException(
                fn() => $periksa([null]),
                'tanggal'
            );
        }
    );

    testCase(
        'Validasi menolak tanggal bentrok',
        function () use (
            $service,
            $pegawaiId,
            $tanggal
        ) {
            assertValidationException(
                fn() =>
                $service->validasiTidakBentrok(
                    $pegawaiId,
                    [$tanggal]
                ),
                'tanggal_mulai'
            );
        }
    );

    testCase(
        'Validasi menerima tanggal tanpa bentrok',
        function () use (
            $service,
            $pegawaiId
        ) {
            $service->validasiTidakBentrok(
                $pegawaiId,
                ['2026-12-10']
            );
        }
    );

    testCase(
        'Pesan validasi memuat tanggal bentrok',
        function () use (
            $service,
            $pegawaiId,
            $tanggal
        ) {
            try {
                $service->validasiTidakBentrok(
                    $pegawaiId,
                    [$tanggal]
                );
            } catch (ValidationException $e) {
                assertSameValue(
                    true,
                    str_contains(
                        implode(
                            ' ',
                            $e->errors()['tanggal_mulai']
                        ),
                        $tanggal
                    )
                );

                return;
            }

            throw new RuntimeException(
                'ValidationException tidak terjadi.'
            );
        }
    );

    testCase(
        'Persetujuan sebagian hanya memblokir tanggal aktif',
        function () use (
            $draft,
            $periksa
        ) {
            denganPerubahan(function () use (
                $draft,
                $periksa
            ) {
                $draft->fresh()->update([
                    'status' =>
                    PengajuanCuti::STATUS_PARTIALLY_APPROVED,
                ]);

                $draft->details()
                    ->where('dihitung_kuota', true)
                    ->update([
                        'status' =>
                        DetailPengajuanCuti::STATUS_REJECTED,
                    ]);

                $draft->details()
                    ->whereIn('tanggal', [
                        '2026-11-30',
                        '2026-12-01',
                        '2026-12-02',
                    ])
                    ->update([
                        'status' =>
                        DetailPengajuanCuti::STATUS_APPROVED,
                    ]);

                $hasil = $periksa([
                    '2026-11-30',
                    '2026-12-01',
                    '2026-12-02',
                    '2026-12-03',
                    '2026-12-04',
                ]);

                assertSameValue(
                    [
                        '2026-11-30',
                        '2026-12-01',
                        '2026-12-02',
                    ],
                    array_column(
                        $hasil,
                        'tanggal'
                    )
                );
            });
        }
    );

    testCase(
        'Bentrok berlaku lintas jenis cuti',
        function () use (
            $pengajuanService,
            $service,
            $pegawai,
            $jenisCutiLain,
            $draft,
            $tanggal
        ) {
            denganPerubahan(function () use (
                $pengajuanService,
                $service,
                $pegawai,
                $jenisCutiLain,
                $draft,
                $tanggal
            ) {
                $draftBaru =
                    $pengajuanService->buatDraft(
                        $pegawai->user_id,
                        $jenisCutiLain->id,
                        $tanggal,
                        $tanggal,
                        'Jenis cuti berbeda'
                    );

                $hasil = $service->cariBentrok(
                    $pegawai->id,
                    [$tanggal],
                    $draftBaru->id
                );

                assertSameValue(
                    1,
                    count($hasil)
                );

                assertSameValue(
                    (int) $draft->id,
                    $hasil[0]['pengajuan_cuti_id']
                );

                assertSameValue(
                    false,
                    (int) $draftBaru->jenis_cuti_id
                        === $hasil[0]['jenis_cuti_id']
                );
            });
        }
    );

    testCase(
        'Ekstraksi hanya mengambil tanggal dihitung kuota',
        function () use ($service) {
            $hasil = $service->ambilTanggalDihitung([
                [
                    'tanggal' => '2026-12-01',
                    'dihitung_kuota' => true,
                ],
                [
                    'tanggal' => '2026-12-02',
                    'dihitung_kuota' => true,
                ],
                [
                    'tanggal' => '2026-12-05',
                    'dihitung_kuota' => false,
                ],
            ]);

            assertSameValue(
                [
                    '2026-12-01',
                    '2026-12-02',
                ],
                $hasil
            );
        }
    );

    testCase(
        'Ekstraksi tanggal menghapus duplikasi',
        function () use ($service) {
            $hasil = $service->ambilTanggalDihitung([
                [
                    'tanggal' => '2026-12-01',
                    'dihitung_kuota' => true,
                ],
                [
                    'tanggal' => '2026-12-01',
                    'dihitung_kuota' => true,
                ],
            ]);

            assertSameValue(
                ['2026-12-01'],
                $hasil
            );
        }
    );

    testCase(
        'Ekstraksi tanggal mengurutkan hasil',
        function () use ($service) {
            $hasil = $service->ambilTanggalDihitung([
                [
                    'tanggal' => '2026-12-03',
                    'dihitung_kuota' => true,
                ],
                [
                    'tanggal' => '2026-12-01',
                    'dihitung_kuota' => true,
                ],
            ]);

            assertSameValue(
                [
                    '2026-12-01',
                    '2026-12-03',
                ],
                $hasil
            );
        }
    );

    testCase(
        'Ekstraksi tanpa tanggal dihitung menghasilkan array kosong',
        function () use ($service) {
            assertSameValue(
                [],
                $service->ambilTanggalDihitung([
                    [
                        'tanggal' => '2026-12-05',
                        'dihitung_kuota' => false,
                    ],
                ])
            );
        }
    );

    testCase(
        'Ekstraksi menolak tanggal dihitung yang tidak valid',
        function () use ($service) {
            assertValidationException(
                fn() =>
                $service->ambilTanggalDihitung([
                    [
                        'tanggal' => '2026-02-30',
                        'dihitung_kuota' => true,
                    ],
                ]),
                'tanggal'
            );
        }
    );

    testCase(
        'Pemeriksaan bentrok tidak mengubah data pengajuan',
        function () use (
            $draft,
            $periksa,
            $tanggal
        ) {
            $sebelum = $draft->fresh()
                ->load('details')
                ->toArray();

            $periksa([$tanggal]);

            $sesudah = $draft->fresh()
                ->load('details')
                ->toArray();

            assertSameValue(
                $sebelum,
                $sesudah
            );
        }
    );

    testCase(
        'Pemeriksaan bentrok tidak membuat kuota mutasi atau persetujuan',
        function () use ($dataAwal) {
            assertSameValue(
                $dataAwal['kuota'],
                KuotaCuti::count()
            );

            assertSameValue(
                $dataAwal['mutasi'],
                MutasiKuotaCuti::count()
            );

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