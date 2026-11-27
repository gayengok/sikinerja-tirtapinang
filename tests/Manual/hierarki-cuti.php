<?php

use App\Models\Pegawai;
use App\Models\User;
use App\Models\PengajuanCuti;
use App\Models\PersetujuanCuti;
use App\Models\KuotaCuti;
use App\Models\MutasiKuotaCuti;
use App\Services\Cuti\HierarkiCutiService;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

require __DIR__ . '/../../vendor/autoload.php';

$app = require __DIR__ . '/../../bootstrap/app.php';

$app->make(Kernel::class)->bootstrap();

$service = app(HierarkiCutiService::class);

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
    string $pesan = ''
): void {
    try {
        $callback();
    } catch (ValidationException $e) {
        if (!isset($e->errors()['hierarki'])) {
            throw new RuntimeException(
                'Field validasi hierarki tidak ditemukan.'
            );
        }

        if (
            $pesan !== ''
            && !str_contains(
                implode(' ', $e->errors()['hierarki']),
                $pesan
            )
        ) {
            throw new RuntimeException(
                'Pesan validasi tidak sesuai: '
                    . implode(
                        ' ',
                        $e->errors()['hierarki']
                    )
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
        'pengajuan' => PengajuanCuti::count(),
        'persetujuan' => PersetujuanCuti::count(),
        'kuota' => KuotaCuti::count(),
        'mutasi' => MutasiKuotaCuti::count(),
    ];
}

function snapshotData(): array
{
    return [
        'pegawai' => Pegawai::query()
            ->orderBy('id')
            ->get()
            ->map(
                fn($pegawai) =>
                $pegawai->getAttributes()
            )
            ->all(),
        'user' => User::query()
            ->orderBy('id')
            ->get()
            ->map(
                fn($user) =>
                $user->getAttributes()
            )
            ->all(),
        'jumlah_data' => jumlahData(),
    ];
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
echo " PENGUJIAN HIERARKI PERSETUJUAN CUTI" . PHP_EOL;
echo "========================================" . PHP_EOL;
echo PHP_EOL;

$dataAwal = snapshotData();

$hasil = null;

DB::beginTransaction();

try {
    testCase(
        'Hierarki pemohon pegawai berhasil ditentukan',
        function () use ($service, &$hasil) {
            $hasil = $service->tentukanHierarki(2);

            assertSameValue(
                2,
                (int) $hasil['pegawai_id']
            );
        }
    );

    testCase(
        'Role pemohon adalah pegawai',
        function () use (&$hasil) {
            assertSameValue(
                'pegawai',
                $hasil['role_pemohon']
            );
        }
    );

    testCase(
        'Pemohon pegawai memiliki tiga tahapan',
        function () use (&$hasil) {
            assertSameValue(
                3,
                $hasil['jumlah_tahapan']
            );
        }
    );

    testCase(
        'Current approver adalah atasan langsung',
        function () use (&$hasil) {
            assertSameValue(
                1,
                (int) $hasil['current_approver_id']
            );
        }
    );

    testCase(
        'Status awal adalah pending_atasan',
        function () use (&$hasil) {
            assertSameValue(
                'pending_atasan',
                $hasil['status_pengajuan']
            );
        }
    );

    testCase(
        'Urutan approver sesuai hierarki',
        function () use (&$hasil) {
            assertSameValue(
                [1, 10, 11],
                array_map(
                    'intval',
                    array_column(
                        $hasil['hierarki'],
                        'approver_id'
                    )
                )
            );
        }
    );

    testCase(
        'Urutan role approver sesuai',
        function () use (&$hasil) {
            assertSameValue(
                [
                    'atasan',
                    'kepala_bagian',
                    'direktur',
                ],
                array_column(
                    $hasil['hierarki'],
                    'role_approver'
                )
            );
        }
    );

    testCase(
        'Nomor urutan tahapan sesuai',
        function () use (&$hasil) {
            assertSameValue(
                [1, 2, 3],
                array_column(
                    $hasil['hierarki'],
                    'urutan'
                )
            );
        }
    );

    testCase(
        'ID akun approver sesuai',
        function () use (&$hasil) {
            assertSameValue(
                [3, 12, 2],
                array_map(
                    'intval',
                    array_column(
                        $hasil['hierarki'],
                        'user_id'
                    )
                )
            );
        }
    );

    testCase(
        'Snapshot seluruh approver tersedia',
        function () use (&$hasil) {
            foreach ($hasil['hierarki'] as $tahap) {
                assertSameValue(
                    true,
                    !empty($tahap['approver_snapshot'])
                );
            }
        }
    );

    testCase(
        'Snapshot identitas approver sesuai',
        function () use (&$hasil) {
            foreach ($hasil['hierarki'] as $tahap) {
                $pegawai = Pegawai::findOrFail(
                    $tahap['approver_id']
                );

                $snapshot =
                    $tahap['approver_snapshot'];

                assertSameValue(
                    (int) $pegawai->id,
                    (int) $snapshot['pegawai_id']
                );

                assertSameValue(
                    $pegawai->nama,
                    $snapshot['nama']
                );

                assertSameValue(
                    $pegawai->nip,
                    $snapshot['nip']
                );

                assertSameValue(
                    $tahap['role_approver'],
                    $snapshot['role']
                );
            }
        }
    );

    testCase(
        'Snapshot jabatan dan divisi sesuai',
        function () use (&$hasil) {
            foreach ($hasil['hierarki'] as $tahap) {
                $pegawai = Pegawai::with(
                    'jabatan.divisi'
                )->findOrFail(
                    $tahap['approver_id']
                );

                $snapshot =
                    $tahap['approver_snapshot'];

                assertSameValue(
                    $pegawai->jabatan?->nama_jabatan,
                    $snapshot['nama_jabatan']
                );

                assertSameValue(
                    $pegawai->jabatan?->divisi?->nama_divisi,
                    $snapshot['nama_divisi']
                );
            }
        }
    );

    testCase(
        'Snapshot tidak memuat password akun',
        function () use (&$hasil) {
            foreach ($hasil['hierarki'] as $tahap) {
                assertSameValue(
                    false,
                    array_key_exists(
                        'password',
                        $tahap['approver_snapshot']
                    )
                );
            }
        }
    );

    testCase(
        'Snapshot memiliki waktu pembuatan',
        function () use (&$hasil) {
            foreach ($hasil['hierarki'] as $tahap) {
                assertSameValue(
                    true,
                    !empty($tahap['approver_snapshot']['snapshot_pada'])
                );
            }
        }
    );

    testCase(
        'Pemohon atasan memiliki dua tahapan',
        function () use ($service) {
            $hasil = $service->tentukanHierarki(1);

            assertSameValue(
                2,
                $hasil['jumlah_tahapan']
            );

            assertSameValue(
                [10, 11],
                array_map(
                    'intval',
                    array_column(
                        $hasil['hierarki'],
                        'approver_id'
                    )
                )
            );
        }
    );

    testCase(
        'Status awal pemohon atasan sesuai',
        function () use ($service) {
            $hasil = $service->tentukanHierarki(1);

            assertSameValue(
                'pending_kepala_bagian',
                $hasil['status_pengajuan']
            );

            assertSameValue(
                10,
                (int) $hasil['current_approver_id']
            );
        }
    );

    testCase(
        'Pemohon kepala bagian memiliki satu tahapan',
        function () use ($service) {
            $hasil = $service->tentukanHierarki(10);

            assertSameValue(
                1,
                $hasil['jumlah_tahapan']
            );

            assertSameValue(
                11,
                (int) $hasil['current_approver_id']
            );
        }
    );

    testCase(
        'Status awal pemohon kepala bagian sesuai',
        function () use ($service) {
            $hasil = $service->tentukanHierarki(10);

            assertSameValue(
                'pending_direktur',
                $hasil['status_pengajuan']
            );
        }
    );

    testCase(
        'Seluruh pemohon dengan hierarki lengkap diterima',
        function () use ($service) {
            foreach ([1, 2, 3, 10, 12] as $id) {
                $hasil =
                    $service->tentukanHierarki($id);

                assertSameValue(
                    true,
                    $hasil['jumlah_tahapan'] > 0
                );
            }
        }
    );

    testCase(
        'Direktur tidak dapat menjadi pemohon',
        function () use ($service) {
            assertValidationException(
                fn() =>
                $service->tentukanHierarki(11),
                'Role pemohon'
            );
        }
    );

    testCase(
        'Master tidak dapat menjadi pemohon',
        function () use ($service) {
            denganPerubahan(function () use (
                $service
            ) {
                User::findOrFail(6)->update([
                    'role' => 'master',
                ]);

                assertValidationException(
                    fn() =>
                    $service->tentukanHierarki(2),
                    'Role pemohon'
                );
            });
        }
    );

    testCase(
        'Atasan Teknik tanpa kepala bagian ditolak',
        function () use ($service) {
            assertValidationException(
                fn() =>
                $service->tentukanHierarki(4),
                'belum memiliki atasan'
            );
        }
    );

    testCase(
        'Atasan Produksi tanpa kepala bagian ditolak',
        function () use ($service) {
            assertValidationException(
                fn() =>
                $service->tentukanHierarki(7),
                'belum memiliki atasan'
            );
        }
    );

    testCase(
        'Pegawai dengan hierarki tidak lengkap ditolak',
        function () use ($service) {
            foreach ([5, 6, 8, 9, 13] as $id) {
                assertValidationException(
                    fn() =>
                    $service->tentukanHierarki($id),
                    'belum memiliki atasan'
                );
            }
        }
    );

    testCase(
        'Pegawai tanpa atasan langsung ditolak',
        function () use ($service) {
            denganPerubahan(function () use (
                $service
            ) {
                Pegawai::findOrFail(2)->update([
                    'atasan_id' => null,
                ]);

                assertValidationException(
                    fn() =>
                    $service->tentukanHierarki(2),
                    'belum memiliki atasan'
                );
            });
        }
    );

    testCase(
        'Kepala bagian tanpa direktur ditolak',
        function () use ($service) {
            denganPerubahan(function () use (
                $service
            ) {
                Pegawai::findOrFail(10)->update([
                    'atasan_id' => null,
                ]);

                assertValidationException(
                    fn() =>
                    $service->tentukanHierarki(2),
                    'belum memiliki atasan'
                );
            });
        }
    );

    testCase(
        'Pegawai tidak boleh menjadi atasan dirinya sendiri',
        function () use ($service) {
            denganPerubahan(function () use (
                $service
            ) {
                Pegawai::findOrFail(2)->update([
                    'atasan_id' => 2,
                ]);

                assertValidationException(
                    fn() =>
                    $service->tentukanHierarki(2),
                    'berputar'
                );
            });
        }
    );

    testCase(
        'Hierarki berputar dua pegawai ditolak',
        function () use ($service) {
            denganPerubahan(function () use (
                $service
            ) {
                Pegawai::findOrFail(1)->update([
                    'atasan_id' => 2,
                ]);

                assertValidationException(
                    fn() =>
                    $service->tentukanHierarki(2),
                    'berputar'
                );
            });
        }
    );

    testCase(
        'Hierarki berputar tiga pegawai ditolak',
        function () use ($service) {
            denganPerubahan(function () use (
                $service
            ) {
                Pegawai::findOrFail(10)->update([
                    'atasan_id' => 2,
                ]);

                assertValidationException(
                    fn() =>
                    $service->tentukanHierarki(2),
                    'berputar'
                );
            });
        }
    );

    testCase(
        'Direktur tidak boleh memiliki atasan',
        function () use ($service) {
            denganPerubahan(function () use (
                $service
            ) {
                Pegawai::findOrFail(11)->update([
                    'atasan_id' => 1,
                ]);

                assertValidationException(
                    fn() =>
                    $service->tentukanHierarki(2),
                    'Direktur sebagai approver terakhir'
                );
            });
        }
    );

    testCase(
        'Pegawai tidak boleh melewati tahap atasan',
        function () use ($service) {
            denganPerubahan(function () use (
                $service
            ) {
                Pegawai::findOrFail(2)->update([
                    'atasan_id' => 10,
                ]);

                assertValidationException(
                    fn() =>
                    $service->tentukanHierarki(2),
                    'Role yang diperlukan adalah atasan'
                );
            });
        }
    );

    testCase(
        'Atasan tidak boleh melewati kepala bagian',
        function () use ($service) {
            denganPerubahan(function () use (
                $service
            ) {
                Pegawai::findOrFail(1)->update([
                    'atasan_id' => 11,
                ]);

                assertValidationException(
                    fn() =>
                    $service->tentukanHierarki(2),
                    'Role yang diperlukan adalah kepala_bagian'
                );
            });
        }
    );

    testCase(
        'Role direktur yang tidak sesuai ditolak',
        function () use ($service) {
            denganPerubahan(function () use (
                $service
            ) {
                User::findOrFail(2)->update([
                    'role' => 'pegawai',
                ]);

                assertValidationException(
                    fn() =>
                    $service->tentukanHierarki(2),
                    'Role yang diperlukan adalah direktur'
                );
            });
        }
    );

    testCase(
        'Pemohon nonaktif ditolak',
        function () use ($service) {
            denganPerubahan(function () use (
                $service
            ) {
                Pegawai::findOrFail(2)->update([
                    'status' => 'nonaktif',
                ]);

                assertValidationException(
                    fn() =>
                    $service->tentukanHierarki(2),
                    'Pegawai tidak aktif'
                );
            });
        }
    );

    testCase(
        'Atasan nonaktif ditolak',
        function () use ($service) {
            denganPerubahan(function () use (
                $service
            ) {
                Pegawai::findOrFail(1)->update([
                    'status' => 'nonaktif',
                ]);

                assertValidationException(
                    fn() =>
                    $service->tentukanHierarki(2),
                    'tidak berstatus aktif'
                );
            });
        }
    );

    testCase(
        'Kepala bagian nonaktif ditolak',
        function () use ($service) {
            denganPerubahan(function () use (
                $service
            ) {
                Pegawai::findOrFail(10)->update([
                    'status' => 'nonaktif',
                ]);

                assertValidationException(
                    fn() =>
                    $service->tentukanHierarki(2),
                    'tidak berstatus aktif'
                );
            });
        }
    );

    testCase(
        'Direktur nonaktif ditolak',
        function () use ($service) {
            denganPerubahan(function () use (
                $service
            ) {
                Pegawai::findOrFail(11)->update([
                    'status' => 'nonaktif',
                ]);

                assertValidationException(
                    fn() =>
                    $service->tentukanHierarki(2),
                    'tidak berstatus aktif'
                );
            });
        }
    );

    testCase(
        'Pegawai yang tidak ditemukan ditolak',
        function () use ($service) {
            $id = (int) Pegawai::max('id') + 1;

            try {
                $service->tentukanHierarki($id);
            } catch (ModelNotFoundException $e) {
                return;
            }

            throw new RuntimeException(
                'ModelNotFoundException tidak terjadi.'
            );
        }
    );

    testCase(
        'Hierarki membaca perubahan atasan terbaru',
        function () use ($service) {
            denganPerubahan(function () use (
                $service
            ) {
                Pegawai::findOrFail(4)->update([
                    'atasan_id' => 10,
                ]);

                $hasil =
                    $service->tentukanHierarki(5);

                assertSameValue(
                    [4, 10, 11],
                    array_map(
                        'intval',
                        array_column(
                            $hasil['hierarki'],
                            'approver_id'
                        )
                    )
                );
            });
        }
    );

    testCase(
        'Penentuan hierarki tidak mengubah data modul cuti',
        function () use ($dataAwal) {
            assertSameValue(
                $dataAwal['jumlah_data'],
                jumlahData()
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
}

testCase(
    'Seluruh perubahan data pengujian berhasil di-rollback',
    function () use ($dataAwal) {
        assertSameValue(
            $dataAwal,
            snapshotData()
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