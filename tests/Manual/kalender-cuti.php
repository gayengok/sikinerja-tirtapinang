<?php

use App\Models\DetailPengajuanCuti;
use App\Models\JenisCuti;
use App\Models\Pegawai;
use App\Models\MasterJamKerja;
use Illuminate\Support\Facades\DB;
use App\Services\Cuti\KalenderCutiService;
use Carbon\Carbon;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Validation\ValidationException;

require __DIR__ . '/../../vendor/autoload.php';

$app = require __DIR__ . '/../../bootstrap/app.php';

$app->make(Kernel::class)->bootstrap();

$service = app(KalenderCutiService::class);

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

function assertSameValue(
    mixed $expected,
    mixed $actual
): void {
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
        if (! array_key_exists($field, $e->errors())) {
            throw new RuntimeException(
                "Field validasi {$field} tidak ditemukan."
            );
        }

        return;
    }

    throw new RuntimeException(
        'ValidationException seharusnya terjadi.'
    );
}

/*
|--------------------------------------------------------------------------
| Data pengujian
|--------------------------------------------------------------------------
|
| Menggunakan model sementara tanpa menyimpannya
| ke dalam database.
|
*/

$pegawai = new Pegawai([
    'jabatan_id' => null,
]);

$hariKerja = new JenisCuti([
    'metode_hitung_hari' => 'hari_kerja',
]);

$hariKalender = new JenisCuti([
    'metode_hitung_hari' => 'hari_kalender',
]);

echo PHP_EOL;
echo '========================================' . PHP_EOL;
echo ' PENGUJIAN KALENDER CUTI' . PHP_EOL;
echo '========================================' . PHP_EOL;
echo PHP_EOL;

/*
|--------------------------------------------------------------------------
| Pengujian penentuan hari kerja
|--------------------------------------------------------------------------
*/

testCase('Senin merupakan hari kerja', function () use (
    $service,
    $pegawai
) {
    assertSameValue(
        true,
        $service->isHariKerja($pegawai, '2026-11-23')
    );
});

testCase('Sabtu bukan hari kerja', function () use (
    $service,
    $pegawai
) {
    assertSameValue(
        false,
        $service->isHariKerja($pegawai, '2026-11-28')
    );
});

testCase('Minggu bukan hari kerja', function () use (
    $service,
    $pegawai
) {
    assertSameValue(
        false,
        $service->isHariKerja($pegawai, '2026-11-29')
    );
});

testCase('Hari libur nasional bukan hari kerja', function () use (
    $service,
    $pegawai
) {
    assertSameValue(
        false,
        $service->isHariKerja($pegawai, '2026-12-25')
    );
});

testCase('Libur nasional khusus tahun 2026', function () use (
    $service,
    $pegawai
) {
    assertSameValue(
        false,
        $service->isHariKerja($pegawai, '2026-08-25')
    );
});

/*
|--------------------------------------------------------------------------
| Pengujian metode perhitungan
|--------------------------------------------------------------------------
*/

testCase('Cuti satu hari kerja', function () use (
    $service,
    $pegawai,
    $hariKerja
) {
    assertSameValue(
        1,
        $service->hitungHari(
            $pegawai,
            $hariKerja,
            '2026-11-23',
            '2026-11-23'
        )
    );
});

testCase('Cuti hari kerja selama satu minggu', function () use (
    $service,
    $pegawai,
    $hariKerja
) {
    assertSameValue(
        5,
        $service->hitungHari(
            $pegawai,
            $hariKerja,
            '2026-11-23',
            '2026-11-29'
        )
    );
});

testCase('Cuti hari kalender selama satu minggu', function () use (
    $service,
    $pegawai,
    $hariKalender
) {
    assertSameValue(
        7,
        $service->hitungHari(
            $pegawai,
            $hariKalender,
            '2026-11-23',
            '2026-11-29'
        )
    );
});

testCase('Hari kerja melewati libur nasional', function () use (
    $service,
    $pegawai,
    $hariKerja
) {
    assertSameValue(
        2,
        $service->hitungHari(
            $pegawai,
            $hariKerja,
            '2026-12-24',
            '2026-12-28'
        )
    );
});

testCase('Hari kalender melewati libur nasional', function () use (
    $service,
    $pegawai,
    $hariKalender
) {
    assertSameValue(
        5,
        $service->hitungHari(
            $pegawai,
            $hariKalender,
            '2026-12-24',
            '2026-12-28'
        )
    );
});

testCase('Rentang hanya berisi akhir pekan', function () use (
    $service,
    $pegawai,
    $hariKerja
) {
    assertSameValue(
        0,
        $service->hitungHari(
            $pegawai,
            $hariKerja,
            '2026-11-28',
            '2026-11-29'
        )
    );
});

testCase('Perhitungan lintas bulan', function () use (
    $service,
    $pegawai,
    $hariKerja
) {
    assertSameValue(
        2,
        $service->hitungHari(
            $pegawai,
            $hariKerja,
            '2026-11-30',
            '2026-12-01'
        )
    );
});

testCase('Perhitungan lintas tahun', function () use (
    $service,
    $pegawai,
    $hariKerja
) {
    assertSameValue(
        2,
        $service->hitungHari(
            $pegawai,
            $hariKerja,
            '2026-12-31',
            '2027-01-04'
        )
    );
});

/*
|--------------------------------------------------------------------------
| Pengujian detail tanggal
|--------------------------------------------------------------------------
*/

testCase('Detail hari kerja berstatus pending', function () use (
    $service,
    $pegawai,
    $hariKerja
) {
    $details = $service->generateDetailTanggal(
        $pegawai,
        $hariKerja,
        '2026-11-23',
        '2026-11-23'
    );

    assertSameValue(1, count($details));

    assertSameValue([
        'tanggal' => '2026-11-23',
        'is_hari_kerja' => true,
        'dihitung_kuota' => true,
        'status' => DetailPengajuanCuti::STATUS_PENDING,
    ], $details[0]);
});

testCase('Detail hari libur berstatus excluded', function () use (
    $service,
    $pegawai,
    $hariKerja
) {
    $details = $service->generateDetailTanggal(
        $pegawai,
        $hariKerja,
        '2026-11-28',
        '2026-11-28'
    );

    assertSameValue([
        'tanggal' => '2026-11-28',
        'is_hari_kerja' => false,
        'dihitung_kuota' => false,
        'status' => DetailPengajuanCuti::STATUS_EXCLUDED,
    ], $details[0]);
});

testCase('Hari kalender tetap menghitung hari libur', function () use (
    $service,
    $pegawai,
    $hariKalender
) {
    $details = $service->generateDetailTanggal(
        $pegawai,
        $hariKalender,
        '2026-12-25',
        '2026-12-25'
    );

    assertSameValue([
        'tanggal' => '2026-12-25',
        'is_hari_kerja' => false,
        'dihitung_kuota' => true,
        'status' => DetailPengajuanCuti::STATUS_PENDING,
    ], $details[0]);
});

testCase('Ringkasan perhitungan konsisten', function () use (
    $service,
    $pegawai,
    $hariKerja
) {
    $hasil = $service->hitung(
        $pegawai,
        $hariKerja,
        '2026-12-24',
        '2026-12-28'
    );

    assertSameValue(5, $hasil['jumlah_hari_kalender']);
    assertSameValue(2, $hasil['jumlah_hari_kerja']);
    assertSameValue(3, $hasil['jumlah_hari_libur']);
    assertSameValue(2, $hasil['jumlah_hari_diajukan']);
    assertSameValue(3, $hasil['jumlah_hari_dikecualikan']);
    assertSameValue(5, count($hasil['details']));
});

/*
|--------------------------------------------------------------------------
| Pengujian validasi
|--------------------------------------------------------------------------
*/

testCase('Menolak tanggal selesai sebelum mulai', function () use (
    $service,
    $pegawai,
    $hariKerja
) {
    assertValidationException(
        fn() => $service->hitung(
            $pegawai,
            $hariKerja,
            '2026-11-25',
            '2026-11-23'
        ),
        'tanggal_selesai'
    );
});

testCase('Menolak format tanggal tidak valid', function () use (
    $service
) {
    assertValidationException(
        fn() => $service->validasiRentangTanggal(
            '23/11/2026',
            '2026-11-25'
        ),
        'tanggal_mulai'
    );
});

testCase('Menolak tanggal yang tidak ada', function () use (
    $service
) {
    assertValidationException(
        fn() => $service->validasiRentangTanggal(
            '2026-02-30',
            '2026-03-01'
        ),
        'tanggal_mulai'
    );
});

testCase('Menolak tanggal kosong', function () use (
    $service
) {
    assertValidationException(
        fn() => $service->validasiRentangTanggal(
            '',
            '2026-11-25'
        ),
        'tanggal_mulai'
    );
});

testCase('Menolak metode perhitungan tidak valid', function () use (
    $service,
    $pegawai
) {
    $jenis = new JenisCuti([
        'metode_hitung_hari' => 'tidak_valid',
    ]);

    assertValidationException(
        fn() => $service->hitung(
            $pegawai,
            $jenis,
            '2026-11-23',
            '2026-11-25'
        ),
        'metode_hitung_hari'
    );
});

testCase('Objek Carbon input tidak berubah', function () use (
    $service,
    $pegawai,
    $hariKerja
) {
    $mulai = Carbon::parse(
        '2026-11-23 10:30:00',
        'Asia/Jakarta'
    );

    $selesai = Carbon::parse(
        '2026-11-25 15:00:00',
        'Asia/Jakarta'
    );

    $service->hitung(
        $pegawai,
        $hariKerja,
        $mulai,
        $selesai
    );

    assertSameValue(
        '2026-11-23 10:30:00',
        $mulai->toDateTimeString()
    );

    assertSameValue(
        '2026-11-25 15:00:00',
        $selesai->toDateTimeString()
    );
});

testCase('Tanggal kabisat valid', function () use (
    $service,
    $pegawai,
    $hariKalender
) {
    assertSameValue(
        3,
        $service->hitungHari(
            $pegawai,
            $hariKalender,
            '2028-02-28',
            '2028-03-01'
        )
    );
});

testCase('Menolak tanggal kabisat pada tahun biasa', function () use (
    $service
) {
    assertValidationException(
        fn() => $service->validasiRentangTanggal(
            '2027-02-29',
            '2027-03-01'
        ),
        'tanggal_mulai'
    );
});

$jumlahJadwalAwal = MasterJamKerja::count();

DB::beginTransaction();

try {
    MasterJamKerja::query()->delete();

    testCase('Fallback Senin tanpa master jam kerja', function () use (
        $service,
        $pegawai
    ) {
        assertSameValue(
            true,
            $service->isHariKerja($pegawai, '2026-11-23')
        );
    });

    testCase('Fallback Sabtu tanpa master jam kerja', function () use (
        $service,
        $pegawai
    ) {
        assertSameValue(
            false,
            $service->isHariKerja($pegawai, '2026-11-28')
        );
    });

    testCase('Fallback Minggu tanpa master jam kerja', function () use (
        $service,
        $pegawai
    ) {
        assertSameValue(
            false,
            $service->isHariKerja($pegawai, '2026-11-29')
        );
    });

    $buatJadwal = function (string $hari): MasterJamKerja {
        return MasterJamKerja::create([
            'jabatan_id' => null,
            'hari' => $hari,
            'jam_masuk' => '07:30:00',
            'jam_pulang' => '16:00:00',
            'toleransi_menit' => 15,
            'jam_buka_absen_masuk' => '06:30:00',
            'batas_absen_masuk' => '07:45:00',
            'jam_buka_absen_pulang' => '16:00:00',
            'jam_tutup_absen_pulang' => '18:00:00',
        ]);
    };

    testCase('Jadwal Sabtu tidak mengesampingkan hari libur', function () use (
        $service,
        $pegawai,
        $buatJadwal
    ) {
        $buatJadwal('Sabtu');

        assertSameValue(
            false,
            $service->isHariKerja($pegawai, '2026-11-28')
        );
    });

    testCase('Jadwal Minggu tidak mengesampingkan hari libur', function () use (
        $service,
        $pegawai,
        $buatJadwal
    ) {
        $buatJadwal('Minggu');

        assertSameValue(
            false,
            $service->isHariKerja($pegawai, '2026-11-29')
        );
    });

    testCase('Libur nasional diprioritaskan dari jadwal kerja', function () use (
        $service,
        $pegawai,
        $buatJadwal
    ) {
        $buatJadwal('Jumat');

        assertSameValue(
            false,
            $service->isHariKerja($pegawai, '2026-12-25')
        );
    });

    testCase('Jadwal umum Kerja mengikuti aturan hari libur', function () use (
        $service,
        $pegawai,
        $buatJadwal
    ) {
        $buatJadwal('Kerja');

        assertSameValue(
            true,
            $service->isHariKerja($pegawai, '2026-11-23')
        );

        assertSameValue(
            false,
            $service->isHariKerja($pegawai, '2026-11-28')
        );

        assertSameValue(
            false,
            $service->isHariKerja($pegawai, '2026-12-25')
        );
    });
} finally {
    DB::rollBack();
}

testCase('Data master jam kerja kembali setelah rollback', function () use (
    $jumlahJadwalAwal
) {
    assertSameValue(
        $jumlahJadwalAwal,
        MasterJamKerja::count()
    );
});

/*
|--------------------------------------------------------------------------
| Ringkasan
|--------------------------------------------------------------------------
*/

echo PHP_EOL;
echo '========================================' . PHP_EOL;
echo "Total pengujian : " . ($passed + $failed) . PHP_EOL;
echo "PASS            : {$passed}" . PHP_EOL;
echo "FAIL            : {$failed}" . PHP_EOL;
echo '========================================' . PHP_EOL;

exit($failed > 0 ? 1 : 0);