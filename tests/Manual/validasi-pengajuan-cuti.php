<?php

use App\Models\JenisCuti;
use App\Models\Pegawai;
use App\Models\User;
use App\Services\Cuti\PengajuanCutiService;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Validation\ValidationException;

require __DIR__ . '/../../vendor/autoload.php';

$app = require __DIR__ . '/../../bootstrap/app.php';

$app->make(Kernel::class)->bootstrap();

$service = app(PengajuanCutiService::class);

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
        echo "       {$e->getMessage()}" . PHP_EOL;
    }
}

function assertSameValue(mixed $expected, mixed $actual): void
{
    if ($expected !== $actual) {
        throw new RuntimeException(
            'Expected: ' . var_export($expected, true)
                . ' | Actual: ' . var_export($actual, true)
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
        if (!array_key_exists($field, $e->errors())) {
            throw new RuntimeException(
                "Field {$field} tidak ditemukan: "
                    . json_encode($e->errors())
            );
        }

        return;
    }

    throw new RuntimeException(
        'ValidationException seharusnya terjadi.'
    );
}

function buatUser(array $attributes = []): User
{
    $user = new User();

    $user->forceFill(array_merge([
        'id' => 10001,
        'role' => 'pegawai',
    ], $attributes));

    $user->exists = true;

    return $user;
}

function buatPegawai(array $attributes = []): Pegawai
{
    $pegawai = new Pegawai();

    $pegawai->forceFill(array_merge([
        'id' => 10001,
        'user_id' => 10001,
        'jabatan_id' => null,
        'status' => 'aktif',
        'tanggal_masuk' => '2024-01-01',
    ], $attributes));

    $pegawai->exists = true;

    return $pegawai;
}

function buatJenisCuti(array $attributes = []): JenisCuti
{
    $jenis = new JenisCuti();

    $jenis->forceFill(array_merge([
        'id' => 10001,
        'kode' => 'CT',
        'nama' => 'Cuti Tahunan',
        'periode_kuota' => 'tahunan',
        'kuota_hari' => 12,
        'minimal_hari_pengajuan' => 7,
        'maksimal_hari_pengajuan' => null,
        'boleh_hari_h' => false,
        'minimal_masa_kerja_bulan' => 12,
        'metode_hitung_hari' => 'hari_kerja',
        'wajib_lampiran' => false,
        'is_active' => true,
    ], $attributes));

    $jenis->exists = true;

    return $jenis;
}

CarbonImmutable::setTestNow(
    CarbonImmutable::parse(
        '2026-11-23 09:00:00',
        'Asia/Jakarta'
    )
);

$validasi = function (
    array $userAttributes = [],
    array $pegawaiAttributes = [],
    array $jenisAttributes = [],
    string $mulai = '2026-11-30',
    string $selesai = '2026-12-02',
    string $alasan = 'Keperluan keluarga',
    ?string $lampiran = null
) use ($service): array {
    return $service->validasiKebijakan(
        buatUser($userAttributes),
        buatPegawai($pegawaiAttributes),
        buatJenisCuti($jenisAttributes),
        $mulai,
        $selesai,
        $alasan,
        $lampiran
    );
};

echo PHP_EOL;
echo '========================================' . PHP_EOL;
echo ' VALIDASI PENGAJUAN CUTI' . PHP_EOL;
echo '========================================' . PHP_EOL;
echo PHP_EOL;

try {
    testCase('Pengajuan pegawai valid', function () use ($validasi) {
        $hasil = $validasi();

        assertSameValue(3, $hasil['jumlah_hari_diajukan']);
    });

    testCase('Pengajuan atasan valid', function () use ($validasi) {
        $hasil = $validasi(
            userAttributes: ['role' => 'atasan']
        );

        assertSameValue(3, $hasil['jumlah_hari_diajukan']);
    });

    testCase('Pengajuan kepala bagian valid', function () use ($validasi) {
        $hasil = $validasi(
            userAttributes: ['role' => 'kepala_bagian']
        );

        assertSameValue(3, $hasil['jumlah_hari_diajukan']);
    });

    testCase('Master tidak dapat mengajukan cuti', function () use ($validasi) {
        assertValidationException(
            fn() => $validasi(
                userAttributes: ['role' => 'master']
            ),
            'role_pemohon'
        );
    });

    testCase('Direktur tidak dapat mengajukan cuti', function () use ($validasi) {
        assertValidationException(
            fn() => $validasi(
                userAttributes: ['role' => 'direktur']
            ),
            'role_pemohon'
        );
    });

    testCase('Akun bukan milik pegawai ditolak', function () use ($validasi) {
        assertValidationException(
            fn() => $validasi(
                pegawaiAttributes: ['user_id' => 99999]
            ),
            'pegawai_id'
        );
    });

    testCase('Pegawai nonaktif ditolak', function () use ($validasi) {
        assertValidationException(
            fn() => $validasi(
                pegawaiAttributes: ['status' => 'nonaktif']
            ),
            'pegawai_id'
        );
    });

    testCase('Jenis cuti nonaktif ditolak', function () use ($validasi) {
        assertValidationException(
            fn() => $validasi(
                jenisAttributes: ['is_active' => false]
            ),
            'jenis_cuti_id'
        );
    });

    testCase('Periode kuota tidak valid ditolak', function () use ($validasi) {
        assertValidationException(
            fn() => $validasi(
                jenisAttributes: ['periode_kuota' => 'invalid']
            ),
            'jenis_cuti_id'
        );
    });

    testCase('Masa kerja kurang dari minimum ditolak', function () use ($validasi) {
        assertValidationException(
            fn() => $validasi(
                pegawaiAttributes: [
                    'tanggal_masuk' => '2026-06-01',
                ]
            ),
            'jenis_cuti_id'
        );
    });

    testCase('Masa kerja tepat 12 bulan diterima', function () use ($validasi) {
        $hasil = $validasi(
            pegawaiAttributes: [
                'tanggal_masuk' => '2025-11-23',
            ]
        );

        assertSameValue(3, $hasil['jumlah_hari_diajukan']);
    });

    testCase('Masa kerja kurang satu hari ditolak', function () use ($validasi) {
        assertValidationException(
            fn() => $validasi(
                pegawaiAttributes: [
                    'tanggal_masuk' => '2025-11-24',
                ]
            ),
            'jenis_cuti_id'
        );
    });

    testCase('Tanggal masuk kosong ditolak', function () use ($validasi) {
        assertValidationException(
            fn() => $validasi(
                pegawaiAttributes: ['tanggal_masuk' => null]
            ),
            'tanggal_masuk'
        );
    });

    testCase('Tanggal masuk masa depan ditolak', function () use ($validasi) {
        assertValidationException(
            fn() => $validasi(
                pegawaiAttributes: [
                    'tanggal_masuk' => '2027-01-01',
                ]
            ),
            'tanggal_masuk'
        );
    });

    testCase('Pengajuan tepat H-7 diterima', function () use ($validasi) {
        $hasil = $validasi(
            mulai: '2026-11-30',
            selesai: '2026-11-30'
        );

        assertSameValue(1, $hasil['jumlah_hari_diajukan']);
    });

    testCase('Pengajuan kurang dari H-7 ditolak', function () use ($validasi) {
        assertValidationException(
            fn() => $validasi(
                mulai: '2026-11-29',
                selesai: '2026-11-30'
            ),
            'tanggal_mulai'
        );
    });

    testCase('Pengajuan tanggal lampau ditolak', function () use ($validasi) {
        assertValidationException(
            fn() => $validasi(
                mulai: '2026-11-22',
                selesai: '2026-11-23'
            ),
            'tanggal_mulai'
        );
    });

    testCase('Pengajuan hari H tanpa izin ditolak', function () use ($validasi) {
        assertValidationException(
            fn() => $validasi(
                jenisAttributes: [
                    'minimal_hari_pengajuan' => 0,
                    'boleh_hari_h' => false,
                ],
                mulai: '2026-11-23',
                selesai: '2026-11-23'
            ),
            'tanggal_mulai'
        );
    });

    testCase('Pengajuan hari H dengan izin diterima', function () use ($validasi) {
        $hasil = $validasi(
            jenisAttributes: [
                'minimal_hari_pengajuan' => 0,
                'boleh_hari_h' => true,
            ],
            mulai: '2026-11-23',
            selesai: '2026-11-23'
        );

        assertSameValue(1, $hasil['jumlah_hari_diajukan']);
    });

    testCase('Hari H tetap mengikuti minimal waktu pengajuan', function () use ($validasi) {
        assertValidationException(
            fn() => $validasi(
                jenisAttributes: [
                    'minimal_hari_pengajuan' => 7,
                    'boleh_hari_h' => true,
                ],
                mulai: '2026-11-23',
                selesai: '2026-11-23'
            ),
            'tanggal_mulai'
        );
    });

    testCase('Durasi melebihi maksimum ditolak', function () use ($validasi) {
        assertValidationException(
            fn() => $validasi(
                jenisAttributes: [
                    'maksimal_hari_pengajuan' => 2,
                ]
            ),
            'tanggal_selesai'
        );
    });

    testCase('Durasi tepat maksimum diterima', function () use ($validasi) {
        $hasil = $validasi(
            jenisAttributes: [
                'maksimal_hari_pengajuan' => 3,
            ]
        );

        assertSameValue(3, $hasil['jumlah_hari_diajukan']);
    });

    testCase('Rentang tanpa hari kerja ditolak', function () use ($validasi) {
        assertValidationException(
            fn() => $validasi(
                mulai: '2026-12-05',
                selesai: '2026-12-06'
            ),
            'tanggal_selesai'
        );
    });

    testCase('Hari kalender menghitung akhir pekan', function () use ($validasi) {
        $hasil = $validasi(
            jenisAttributes: [
                'metode_hitung_hari' => 'hari_kalender',
            ],
            mulai: '2026-12-05',
            selesai: '2026-12-06'
        );

        assertSameValue(2, $hasil['jumlah_hari_diajukan']);
    });

    testCase('Alasan kosong ditolak', function () use ($validasi) {
        assertValidationException(
            fn() => $validasi(alasan: '   '),
            'alasan'
        );
    });

    testCase('Lampiran wajib tetapi kosong ditolak', function () use ($validasi) {
        assertValidationException(
            fn() => $validasi(
                jenisAttributes: [
                    'wajib_lampiran' => true,
                ]
            ),
            'lampiran'
        );
    });

    testCase('Lampiran wajib dan terisi diterima', function () use ($validasi) {
        $hasil = $validasi(
            jenisAttributes: [
                'wajib_lampiran' => true,
            ],
            lampiran: 'cuti/lampiran/dokumen.pdf'
        );

        assertSameValue(3, $hasil['jumlah_hari_diajukan']);
    });

    testCase('Lampiran opsional dapat dikosongkan', function () use ($validasi) {
        $hasil = $validasi(lampiran: null);

        assertSameValue(3, $hasil['jumlah_hari_diajukan']);
    });

    testCase('Rentang lintas tahun dapat dihitung', function () use ($validasi) {
        $hasil = $validasi(
            mulai: '2026-12-31',
            selesai: '2027-01-04'
        );

        assertSameValue(2, $hasil['jumlah_hari_diajukan']);
    });

    testCase('Tanggal selesai sebelum mulai ditolak', function () use ($validasi) {
        assertValidationException(
            fn() => $validasi(
                mulai: '2026-12-02',
                selesai: '2026-11-30'
            ),
            'tanggal_selesai'
        );
    });

    testCase('Tanggal tidak valid ditolak', function () use ($validasi) {
        assertValidationException(
            fn() => $validasi(
                mulai: '2026-11-31'
            ),
            'tanggal_mulai'
        );
    });
} finally {
    CarbonImmutable::setTestNow();
}

echo PHP_EOL;
echo '========================================' . PHP_EOL;
echo 'Total pengujian : ' . ($passed + $failed) . PHP_EOL;
echo "PASS            : {$passed}" . PHP_EOL;
echo "FAIL            : {$failed}" . PHP_EOL;
echo '========================================' . PHP_EOL;

exit($failed > 0 ? 1 : 0);