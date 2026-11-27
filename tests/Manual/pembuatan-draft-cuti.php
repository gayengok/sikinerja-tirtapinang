<?php

use App\Models\DetailPengajuanCuti;
use App\Models\JenisCuti;
use App\Models\KuotaCuti;
use App\Models\MutasiKuotaCuti;
use App\Models\Pegawai;
use App\Models\PengajuanCuti;
use App\Models\PersetujuanCuti;
use App\Services\Cuti\PengajuanCutiService;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
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

function jumlahData(): array
{
    return [
        'jenis_cuti' => JenisCuti::count(),
        'pengajuan_cuti' => PengajuanCuti::count(),
        'detail_pengajuan_cuti' => DetailPengajuanCuti::count(),
        'kuota_cuti' => KuotaCuti::count(),
        'mutasi_kuota_cuti' => MutasiKuotaCuti::count(),
        'persetujuan_cuti' => PersetujuanCuti::count(),
    ];
}

function buatJenisCuti(array $attributes = []): JenisCuti
{
    return JenisCuti::query()->create(array_merge([
        'kode' => 'T-' . Str::upper(Str::random(6)),
        'nama' => 'Jenis Cuti Pengujian',
        'deskripsi' => 'Data sementara pengujian draft',
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
    ], $attributes));
}

$pegawai = Pegawai::query()
    ->with(['user', 'jabatan.divisi'])
    ->where('status', 'aktif')
    ->whereHas('user', function ($query) {
        $query->where('role', 'pegawai');
    })
    ->firstOrFail();

$dataAwal = jumlahData();

CarbonImmutable::setTestNow(
    CarbonImmutable::parse(
        '2026-11-23 09:00:00',
        'Asia/Jakarta'
    )
);

echo PHP_EOL;
echo '========================================' . PHP_EOL;
echo ' PENGUJIAN PEMBUATAN DRAFT CUTI' . PHP_EOL;
echo '========================================' . PHP_EOL;
echo PHP_EOL;

DB::beginTransaction();

try {
    $jenisCuti = buatJenisCuti();

    $buatDraft = function (
        ?int $jenisCutiId = null,
        string $mulai = '2026-11-30',
        string $selesai = '2026-12-06',
        string $alasan = 'Keperluan keluarga',
        ?string $lampiran = null
    ) use ($service, $pegawai, $jenisCuti): PengajuanCuti {
        return $service->buatDraft(
            $pegawai->user_id,
            $jenisCutiId ?? $jenisCuti->getKey(),
            $mulai,
            $selesai,
            $alasan,
            $lampiran
        );
    };

    $draft = null;

    testCase('Draft berhasil disimpan ke database', function () use (
        $buatDraft,
        &$draft
    ) {
        $draft = $buatDraft();

        assertSameValue(true, $draft->exists);

        assertSameValue(
            true,
            PengajuanCuti::query()
                ->whereKey($draft->getKey())
                ->exists()
        );
    });

    testCase('Status pengajuan adalah draft', function () use (
        &$draft
    ) {
        assertSameValue(
            PengajuanCuti::STATUS_DRAFT,
            $draft->status
        );
    });

    testCase('Nomor pengajuan belum diterbitkan', function () use (
        &$draft
    ) {
        assertSameValue(null, $draft->nomor_pengajuan);
    });

    testCase('Tanggal pengajuan belum ditentukan', function () use (
        &$draft
    ) {
        assertSameValue(null, $draft->tanggal_pengajuan);
        assertSameValue(null, $draft->diajukan_pada);
    });

    testCase('Current approver masih kosong', function () use (
        &$draft
    ) {
        assertSameValue(null, $draft->current_approver_id);
    });

    testCase('Identitas pemohon tersimpan dengan benar', function () use (
        &$draft,
        $pegawai
    ) {
        assertSameValue(
            (int) $pegawai->getKey(),
            (int) $draft->pegawai_id
        );

        assertSameValue(
            'pegawai',
            $draft->role_pemohon
        );
    });

    testCase('Jenis cuti tersimpan dengan benar', function () use (
        &$draft,
        $jenisCuti
    ) {
        assertSameValue(
            (int) $jenisCuti->getKey(),
            (int) $draft->jenis_cuti_id
        );
    });

    testCase('Rentang tanggal tersimpan dengan benar', function () use (
        &$draft
    ) {
        assertSameValue(
            '2026-11-30',
            $draft->tanggal_mulai->toDateString()
        );

        assertSameValue(
            '2026-12-06',
            $draft->tanggal_selesai->toDateString()
        );
    });

    testCase('Jumlah hari diajukan adalah lima hari kerja', function () use (
        &$draft
    ) {
        assertSameValue(5, $draft->jumlah_hari_diajukan);
    });

    testCase('Jumlah hari disetujui masih nol', function () use (
        &$draft
    ) {
        assertSameValue(0, $draft->jumlah_hari_disetujui);
    });

    testCase('Seluruh tujuh detail tanggal tersimpan', function () use (
        &$draft
    ) {
        assertSameValue(7, $draft->details()->count());
    });

    testCase('Lima detail hari kerja berstatus pending', function () use (
        &$draft
    ) {
        assertSameValue(
            5,
            $draft->details()
                ->where('is_hari_kerja', true)
                ->where('dihitung_kuota', true)
                ->where(
                    'status',
                    DetailPengajuanCuti::STATUS_PENDING
                )
                ->count()
        );
    });

    testCase('Dua detail akhir pekan berstatus excluded', function () use (
        &$draft
    ) {
        assertSameValue(
            2,
            $draft->details()
                ->where('is_hari_kerja', false)
                ->where('dihitung_kuota', false)
                ->where(
                    'status',
                    DetailPengajuanCuti::STATUS_EXCLUDED
                )
                ->count()
        );
    });

    testCase('Seluruh detail belum terhubung dengan kuota', function () use (
        &$draft
    ) {
        assertSameValue(
            7,
            $draft->details()
                ->whereNull('kuota_cuti_id')
                ->count()
        );
    });

    testCase('Snapshot kebijakan tersimpan', function () use (
        &$draft
    ) {
        assertSameValue(
            true,
            is_array($draft->kebijakan_snapshot)
        );

        assertSameValue(
            true,
            count($draft->kebijakan_snapshot) > 0
        );
    });

    testCase('Snapshot identitas pemohon sesuai', function () use (
        &$draft,
        $pegawai
    ) {
        $snapshot = $draft->pemohon_snapshot;

        assertSameValue(
            (int) $pegawai->getKey(),
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
            $pegawai->user->role,
            $snapshot['role']
        );
    });

    testCase('Snapshot jabatan dan divisi sesuai', function () use (
        &$draft,
        $pegawai
    ) {
        $snapshot = $draft->pemohon_snapshot;

        assertSameValue(
            $pegawai->jabatan?->nama_jabatan,
            $snapshot['nama_jabatan']
        );

        assertSameValue(
            $pegawai->jabatan?->divisi?->nama_divisi,
            $snapshot['nama_divisi']
        );
    });

    testCase('Draft tidak membuat kuota baru', function () use (
        $dataAwal
    ) {
        assertSameValue(
            $dataAwal['kuota_cuti'],
            KuotaCuti::count()
        );
    });

    testCase('Draft tidak membuat mutasi kuota', function () use (
        $dataAwal
    ) {
        assertSameValue(
            $dataAwal['mutasi_kuota_cuti'],
            MutasiKuotaCuti::count()
        );
    });

    testCase('Draft tidak membuat data persetujuan', function () use (
        &$draft
    ) {
        assertSameValue(
            0,
            PersetujuanCuti::query()
                ->where(
                    'pengajuan_cuti_id',
                    $draft->getKey()
                )
                ->count()
        );
    });

    testCase('Draft hari kalender menghitung akhir pekan', function () use (
        $buatDraft
    ) {
        $jenis = buatJenisCuti([
            'metode_hitung_hari' => 'hari_kalender',
        ]);

        $hasil = $buatDraft(
            jenisCutiId: $jenis->getKey()
        );

        assertSameValue(
            7,
            $hasil->jumlah_hari_diajukan
        );

        assertSameValue(
            7,
            $hasil->details()
                ->where('dihitung_kuota', true)
                ->where(
                    'status',
                    DetailPengajuanCuti::STATUS_PENDING
                )
                ->count()
        );
    });

    testCase('Draft tanpa lampiran wajib tetap dapat disimpan', function () use (
        $buatDraft
    ) {
        $jenis = buatJenisCuti([
            'wajib_lampiran' => true,
        ]);

        $hasil = $buatDraft(
            jenisCutiId: $jenis->getKey()
        );

        assertSameValue(null, $hasil->lampiran);

        assertSameValue(
            PengajuanCuti::STATUS_DRAFT,
            $hasil->status
        );
    });

    testCase('Draft belum menerapkan minimal waktu pengajuan', function () use (
        $buatDraft
    ) {
        $hasil = $buatDraft(
            mulai: '2026-11-24',
            selesai: '2026-11-24'
        );

        assertSameValue(
            PengajuanCuti::STATUS_DRAFT,
            $hasil->status
        );

        assertSameValue(1, $hasil->jumlah_hari_diajukan);
    });

    testCase('Draft belum menerapkan maksimal durasi kebijakan', function () use (
        $buatDraft
    ) {
        $jenis = buatJenisCuti([
            'maksimal_hari_pengajuan' => 2,
        ]);

        $hasil = $buatDraft(
            jenisCutiId: $jenis->getKey()
        );

        assertSameValue(5, $hasil->jumlah_hari_diajukan);
    });

    testCase('Draft dapat menyimpan rentang lintas tahun', function () use (
        $buatDraft
    ) {
        $hasil = $buatDraft(
            mulai: '2026-12-31',
            selesai: '2027-01-04'
        );

        assertSameValue(2, $hasil->jumlah_hari_diajukan);
        assertSameValue(5, $hasil->details()->count());
    });

    testCase('Alasan draft dinormalisasi', function () use (
        $buatDraft
    ) {
        $hasil = $buatDraft(
            alasan: '  Keperluan keluarga  '
        );

        assertSameValue(
            'Keperluan keluarga',
            $hasil->alasan
        );
    });

    testCase('Lampiran draft dinormalisasi', function () use (
        $buatDraft
    ) {
        $hasil = $buatDraft(
            lampiran: '  cuti/lampiran/test.pdf  '
        );

        assertSameValue(
            'cuti/lampiran/test.pdf',
            $hasil->lampiran
        );
    });

    testCase('Lampiran kosong disimpan sebagai null', function () use (
        $buatDraft
    ) {
        $hasil = $buatDraft(
            lampiran: '   '
        );

        assertSameValue(null, $hasil->lampiran);
    });

    testCase('Jenis cuti nonaktif ditolak', function () use (
        $buatDraft
    ) {
        $jenis = buatJenisCuti([
            'is_active' => false,
        ]);

        assertValidationException(
            fn() => $buatDraft(
                jenisCutiId: $jenis->getKey()
            ),
            'jenis_cuti_id'
        );
    });

    testCase('Draft dengan alasan kosong ditolak', function () use (
        $buatDraft
    ) {
        assertValidationException(
            fn() => $buatDraft(
                alasan: '   '
            ),
            'alasan'
        );
    });

    testCase('Draft dengan tanggal lampau ditolak', function () use (
        $buatDraft
    ) {
        assertValidationException(
            fn() => $buatDraft(
                mulai: '2026-11-22',
                selesai: '2026-11-23'
            ),
            'tanggal_mulai'
        );
    });

    testCase('Draft dengan tanggal terbalik ditolak', function () use (
        $buatDraft
    ) {
        assertValidationException(
            fn() => $buatDraft(
                mulai: '2026-12-06',
                selesai: '2026-11-30'
            ),
            'tanggal_selesai'
        );
    });

    testCase('Draft dengan tanggal tidak valid ditolak', function () use (
        $buatDraft
    ) {
        assertValidationException(
            fn() => $buatDraft(
                mulai: '2026-11-31'
            ),
            'tanggal_mulai'
        );
    });

    testCase('Draft tanpa hari yang dihitung ditolak', function () use (
        $buatDraft
    ) {
        assertValidationException(
            fn() => $buatDraft(
                mulai: '2026-12-05',
                selesai: '2026-12-06'
            ),
            'tanggal_selesai'
        );
    });

    testCase('Draft dengan rentang tepat 366 hari diterima', function () use (
        $buatDraft
    ) {
        $hasil = $buatDraft(
            mulai: '2026-11-30',
            selesai: '2027-11-30'
        );

        assertSameValue(
            366,
            $hasil->details()->count()
        );
    });

    testCase('Draft dengan rentang lebih dari 366 hari ditolak', function () use (
        $buatDraft
    ) {
        assertValidationException(
            fn() => $buatDraft(
                mulai: '2026-11-30',
                selesai: '2027-12-01'
            ),
            'tanggal_selesai'
        );
    });

    testCase('Validasi pengiriman juga membatasi rentang tanggal', function () use (
        $service,
        $pegawai,
        $jenisCuti
    ) {
        assertValidationException(
            fn() => $service->validasiKebijakan(
                $pegawai->user,
                $pegawai,
                $jenisCuti,
                '2026-11-30',
                '2027-12-01',
                'Keperluan keluarga'
            ),
            'tanggal_selesai'
        );
    });

    testCase('Validasi gagal tidak menambah pengajuan atau detail', function () use (
        $buatDraft
    ) {
        $pengajuanAwal = PengajuanCuti::count();
        $detailAwal = DetailPengajuanCuti::count();

        assertValidationException(
            fn() => $buatDraft(
                alasan: ''
            ),
            'alasan'
        );

        assertSameValue(
            $pengajuanAwal,
            PengajuanCuti::count()
        );

        assertSameValue(
            $detailAwal,
            DetailPengajuanCuti::count()
        );
    });

    testCase('Pembuatan draft mendukung rollback transaction luar', function () use (
        $buatDraft
    ) {
        $pengajuanAwal = PengajuanCuti::count();
        $detailAwal = DetailPengajuanCuti::count();

        DB::beginTransaction();

        try {
            $hasil = $buatDraft();

            assertSameValue(
                true,
                PengajuanCuti::query()
                    ->whereKey($hasil->getKey())
                    ->exists()
            );

            assertSameValue(
                $pengajuanAwal + 1,
                PengajuanCuti::count()
            );

            assertSameValue(
                $detailAwal + 7,
                DetailPengajuanCuti::count()
            );
        } finally {
            DB::rollBack();
        }

        assertSameValue(
            $pengajuanAwal,
            PengajuanCuti::count()
        );

        assertSameValue(
            $detailAwal,
            DetailPengajuanCuti::count()
        );
    });

    testCase('Seluruh draft tidak mengubah kuota dan mutasi', function () use (
        $dataAwal
    ) {
        assertSameValue(
            $dataAwal['kuota_cuti'],
            KuotaCuti::count()
        );

        assertSameValue(
            $dataAwal['mutasi_kuota_cuti'],
            MutasiKuotaCuti::count()
        );

        assertSameValue(
            $dataAwal['persetujuan_cuti'],
            PersetujuanCuti::count()
        );
    });
} catch (Throwable $e) {
    $failed++;

    echo '[FAIL] Persiapan atau eksekusi pengujian' . PHP_EOL;
    echo "       {$e->getMessage()}" . PHP_EOL;
} finally {
    DB::rollBack();

    CarbonImmutable::setTestNow();
}

testCase('Seluruh data pengujian berhasil di-rollback', function () use (
    $dataAwal
) {
    assertSameValue(
        $dataAwal,
        jumlahData()
    );
});

echo PHP_EOL;
echo '========================================' . PHP_EOL;
echo 'Total pengujian : ' . ($passed + $failed) . PHP_EOL;
echo "PASS            : {$passed}" . PHP_EOL;
echo "FAIL            : {$failed}" . PHP_EOL;
echo '========================================' . PHP_EOL;

exit($failed > 0 ? 1 : 0);