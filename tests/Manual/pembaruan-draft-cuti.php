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
        'deskripsi' => 'Data sementara pembaruan draft',
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

function kondisiDraft(PengajuanCuti $draft): array
{
    return $draft->fresh()
        ->load('details')
        ->toArray();
}

$dataAwal = jumlahData();

CarbonImmutable::setTestNow(
    CarbonImmutable::parse(
        '2026-11-23 09:00:00',
        'Asia/Jakarta'
    )
);

echo PHP_EOL;
echo '========================================' . PHP_EOL;
echo ' PENGUJIAN PEMBARUAN DRAFT CUTI' . PHP_EOL;
echo '========================================' . PHP_EOL;
echo PHP_EOL;

DB::beginTransaction();

try {
    $pegawai = Pegawai::query()
        ->with(['user', 'jabatan.divisi'])
        ->where('status', 'aktif')
        ->whereHas('user', function ($query) {
            $query->where('role', 'pegawai');
        })
        ->firstOrFail();

    $jenisHariKerja = buatJenisCuti();

    $jenisHariKalender = buatJenisCuti([
        'metode_hitung_hari' => 'hari_kalender',
    ]);

    $buatDraft = function () use (
        $service,
        $pegawai,
        $jenisHariKerja
    ): PengajuanCuti {
        return $service->buatDraft(
            $pegawai->user_id,
            $jenisHariKerja->getKey(),
            '2026-11-30',
            '2026-12-06',
            'Alasan awal',
            'cuti/lampiran/awal.pdf'
        );
    };

    $ubahDraft = function (
        PengajuanCuti $draft,
        ?int $jenisCutiId = null,
        string $mulai = '2026-12-03',
        string $selesai = '2026-12-07',
        string $alasan = 'Alasan diperbarui',
        ?string $lampiran = null,
        ?int $userId = null
    ) use (
        $service,
        $pegawai,
        $jenisHariKerja
    ): PengajuanCuti {
        return $service->ubahDraft(
            $userId ?? $pegawai->user_id,
            $draft->getKey(),
            $jenisCutiId ?? $jenisHariKerja->getKey(),
            $mulai,
            $selesai,
            $alasan,
            $lampiran
        );
    };

    $draft = $buatDraft();

    $idAwal = $draft->getKey();

    $detailIdAwal = $draft->details()
        ->pluck('id')
        ->all();

    $jumlahPengajuanAwal = PengajuanCuti::count();

    $hasil = null;

    testCase('Draft berhasil diperbarui', function () use (
        $ubahDraft,
        $draft,
        &$hasil
    ) {
        $hasil = $ubahDraft($draft);

        assertSameValue(true, $hasil->exists);
    });

    testCase('ID pengajuan tetap sama', function () use (
        &$hasil,
        $idAwal
    ) {
        assertSameValue(
            $idAwal,
            $hasil->getKey()
        );
    });

    testCase('Pembaruan tidak membuat pengajuan baru', function () use (
        $jumlahPengajuanAwal
    ) {
        assertSameValue(
            $jumlahPengajuanAwal,
            PengajuanCuti::count()
        );
    });

    testCase('Status tetap draft', function () use (
        &$hasil
    ) {
        assertSameValue(
            PengajuanCuti::STATUS_DRAFT,
            $hasil->status
        );
    });

    testCase('Tanggal mulai berhasil diperbarui', function () use (
        &$hasil
    ) {
        assertSameValue(
            '2026-12-03',
            $hasil->tanggal_mulai->toDateString()
        );
    });

    testCase('Tanggal selesai berhasil diperbarui', function () use (
        &$hasil
    ) {
        assertSameValue(
            '2026-12-07',
            $hasil->tanggal_selesai->toDateString()
        );
    });

    testCase('Jumlah hari kerja dihitung ulang', function () use (
        &$hasil
    ) {
        assertSameValue(
            3,
            $hasil->jumlah_hari_diajukan
        );
    });

    testCase('Alasan berhasil diperbarui', function () use (
        &$hasil
    ) {
        assertSameValue(
            'Alasan diperbarui',
            $hasil->alasan
        );
    });

    testCase('Lampiran dapat dikosongkan', function () use (
        &$hasil
    ) {
        assertSameValue(null, $hasil->lampiran);
    });

    testCase('Detail tanggal lama telah dihapus', function () use (
        $detailIdAwal
    ) {
        assertSameValue(
            0,
            DetailPengajuanCuti::query()
                ->whereIn('id', $detailIdAwal)
                ->count()
        );
    });

    testCase('Lima detail tanggal baru tersimpan', function () use (
        &$hasil
    ) {
        assertSameValue(
            5,
            $hasil->details()->count()
        );
    });

    testCase('Detail baru memiliki tanggal yang benar', function () use (
        &$hasil
    ) {
        $tanggal = $hasil->details()
            ->orderBy('tanggal')
            ->get()
            ->map(fn($detail) => $detail->tanggal->toDateString())
            ->all();

        assertSameValue([
            '2026-12-03',
            '2026-12-04',
            '2026-12-05',
            '2026-12-06',
            '2026-12-07',
        ], $tanggal);
    });

    testCase('Tiga detail hari kerja berstatus pending', function () use (
        &$hasil
    ) {
        assertSameValue(
            3,
            $hasil->details()
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
        &$hasil
    ) {
        assertSameValue(
            2,
            $hasil->details()
                ->where('is_hari_kerja', false)
                ->where('dihitung_kuota', false)
                ->where(
                    'status',
                    DetailPengajuanCuti::STATUS_EXCLUDED
                )
                ->count()
        );
    });

    testCase('Seluruh detail baru belum memiliki kuota', function () use (
        &$hasil
    ) {
        assertSameValue(
            5,
            $hasil->details()
                ->whereNull('kuota_cuti_id')
                ->count()
        );
    });

    testCase('Nomor dan tanggal pengajuan tetap kosong', function () use (
        &$hasil
    ) {
        assertSameValue(null, $hasil->nomor_pengajuan);
        assertSameValue(null, $hasil->tanggal_pengajuan);
        assertSameValue(null, $hasil->diajukan_pada);
    });

    testCase('Current approver tetap kosong', function () use (
        &$hasil
    ) {
        assertSameValue(
            null,
            $hasil->current_approver_id
        );
    });

    testCase('Jumlah hari disetujui tetap nol', function () use (
        &$hasil
    ) {
        assertSameValue(
            0,
            $hasil->jumlah_hari_disetujui
        );
    });

    testCase('Jenis cuti dapat diganti', function () use (
        $ubahDraft,
        $draft,
        $jenisHariKalender
    ) {
        $hasil = $ubahDraft(
            $draft,
            jenisCutiId: $jenisHariKalender->getKey()
        );

        assertSameValue(
            (int) $jenisHariKalender->getKey(),
            (int) $hasil->jenis_cuti_id
        );

        assertSameValue(
            5,
            $hasil->jumlah_hari_diajukan
        );
    });

    testCase('Perubahan metode menghitung seluruh hari kalender', function () use (
        $ubahDraft,
        $draft,
        $jenisHariKalender
    ) {
        $hasil = $ubahDraft(
            $draft,
            jenisCutiId: $jenisHariKalender->getKey()
        );

        assertSameValue(
            5,
            $hasil->details()
                ->where('dihitung_kuota', true)
                ->where(
                    'status',
                    DetailPengajuanCuti::STATUS_PENDING
                )
                ->count()
        );
    });

    testCase('Snapshot kebijakan diperbarui', function () use (
        $ubahDraft,
        $draft,
        $jenisHariKalender
    ) {
        $hasil = $ubahDraft(
            $draft,
            jenisCutiId: $jenisHariKalender->getKey()
        );

        $expected = app(
            \App\Services\Cuti\KuotaCutiService::class
        )->snapshotKebijakan($jenisHariKalender);

        $actual = $hasil->kebijakan_snapshot;

        ksort($expected);
        ksort($actual);

        assertSameValue(
            $expected,
            $actual
        );
    });

    testCase('Snapshot pemohon tetap sesuai', function () use (
        $ubahDraft,
        $draft,
        $pegawai
    ) {
        $hasil = $ubahDraft($draft);

        assertSameValue(
            (int) $pegawai->getKey(),
            (int) $hasil->pemohon_snapshot['pegawai_id']
        );

        assertSameValue(
            $pegawai->nama,
            $hasil->pemohon_snapshot['nama']
        );

        assertSameValue(
            $pegawai->nip,
            $hasil->pemohon_snapshot['nip']
        );
    });

    testCase('Waktu snapshot pemohon diperbarui', function () use (
        $ubahDraft,
        $draft
    ) {
        $snapshotAwal = $draft->fresh()
            ->pemohon_snapshot['snapshot_pada'];

        CarbonImmutable::setTestNow(
            CarbonImmutable::parse(
                '2026-11-23 10:00:00',
                'Asia/Jakarta'
            )
        );

        try {
            $hasil = $ubahDraft($draft);

            assertSameValue(
                false,
                $snapshotAwal ===
                    $hasil->pemohon_snapshot['snapshot_pada']
            );

            assertSameValue(
                '2026-11-23T10:00:00+07:00',
                $hasil->pemohon_snapshot['snapshot_pada']
            );
        } finally {
            CarbonImmutable::setTestNow(
                CarbonImmutable::parse(
                    '2026-11-23 09:00:00',
                    'Asia/Jakarta'
                )
            );
        }
    });

    testCase('Lampiran dapat diganti', function () use (
        $ubahDraft,
        $draft
    ) {
        $hasil = $ubahDraft(
            $draft,
            lampiran: 'cuti/lampiran/baru.pdf'
        );

        assertSameValue(
            'cuti/lampiran/baru.pdf',
            $hasil->lampiran
        );
    });

    testCase('Alasan dan lampiran dinormalisasi', function () use (
        $ubahDraft,
        $draft
    ) {
        $hasil = $ubahDraft(
            $draft,
            alasan: '  Alasan baru  ',
            lampiran: '  cuti/lampiran/baru.pdf  '
        );

        assertSameValue(
            'Alasan baru',
            $hasil->alasan
        );

        assertSameValue(
            'cuti/lampiran/baru.pdf',
            $hasil->lampiran
        );
    });

    testCase('Lampiran kosong menjadi null', function () use (
        $ubahDraft,
        $draft
    ) {
        $hasil = $ubahDraft(
            $draft,
            lampiran: '   '
        );

        assertSameValue(null, $hasil->lampiran);
    });

    testCase('Draft dapat diperbarui lintas tahun', function () use (
        $ubahDraft,
        $draft
    ) {
        $hasil = $ubahDraft(
            $draft,
            mulai: '2026-12-31',
            selesai: '2027-01-04'
        );

        assertSameValue(
            2,
            $hasil->jumlah_hari_diajukan
        );

        assertSameValue(
            5,
            $hasil->details()->count()
        );
    });

    testCase('Draft belum menerapkan minimal waktu pengajuan', function () use (
        $ubahDraft,
        $draft
    ) {
        $hasil = $ubahDraft(
            $draft,
            mulai: '2026-11-24',
            selesai: '2026-11-24'
        );

        assertSameValue(
            1,
            $hasil->jumlah_hari_diajukan
        );
    });

    testCase('Draft belum menerapkan maksimal durasi kebijakan', function () use (
        $ubahDraft,
        $draft
    ) {
        $jenis = buatJenisCuti([
            'maksimal_hari_pengajuan' => 1,
        ]);

        $hasil = $ubahDraft(
            $draft,
            jenisCutiId: $jenis->getKey()
        );

        assertSameValue(
            3,
            $hasil->jumlah_hari_diajukan
        );
    });

    testCase('Draft tanpa lampiran wajib masih dapat diperbarui', function () use (
        $ubahDraft,
        $draft
    ) {
        $jenis = buatJenisCuti([
            'wajib_lampiran' => true,
        ]);

        $hasil = $ubahDraft(
            $draft,
            jenisCutiId: $jenis->getKey()
        );

        assertSameValue(null, $hasil->lampiran);

        assertSameValue(
            PengajuanCuti::STATUS_DRAFT,
            $hasil->status
        );
    });

    testCase('Jenis cuti nonaktif ditolak', function () use (
        $ubahDraft,
        $draft
    ) {
        $jenis = buatJenisCuti([
            'is_active' => false,
        ]);

        assertValidationException(
            fn() => $ubahDraft(
                $draft,
                jenisCutiId: $jenis->getKey()
            ),
            'jenis_cuti_id'
        );
    });

    testCase('Alasan kosong ditolak', function () use (
        $ubahDraft,
        $draft
    ) {
        assertValidationException(
            fn() => $ubahDraft(
                $draft,
                alasan: '   '
            ),
            'alasan'
        );
    });

    testCase('Tanggal lampau ditolak', function () use (
        $ubahDraft,
        $draft
    ) {
        assertValidationException(
            fn() => $ubahDraft(
                $draft,
                mulai: '2026-11-22',
                selesai: '2026-11-23'
            ),
            'tanggal_mulai'
        );
    });

    testCase('Tanggal terbalik ditolak', function () use (
        $ubahDraft,
        $draft
    ) {
        assertValidationException(
            fn() => $ubahDraft(
                $draft,
                mulai: '2026-12-07',
                selesai: '2026-12-03'
            ),
            'tanggal_selesai'
        );
    });

    testCase('Tanggal tidak valid ditolak', function () use (
        $ubahDraft,
        $draft
    ) {
        assertValidationException(
            fn() => $ubahDraft(
                $draft,
                mulai: '2026-11-31'
            ),
            'tanggal_mulai'
        );
    });

    testCase('Rentang tanpa hari kerja ditolak', function () use (
        $ubahDraft,
        $draft
    ) {
        assertValidationException(
            fn() => $ubahDraft(
                $draft,
                mulai: '2026-12-05',
                selesai: '2026-12-06'
            ),
            'tanggal_selesai'
        );
    });

    testCase('Rentang tepat 366 hari diterima', function () use (
        $ubahDraft,
        $draft
    ) {
        $hasil = $ubahDraft(
            $draft,
            mulai: '2026-11-30',
            selesai: '2027-11-30'
        );

        assertSameValue(
            366,
            $hasil->details()->count()
        );
    });

    testCase('Rentang lebih dari 366 hari ditolak', function () use (
        $ubahDraft,
        $draft
    ) {
        assertValidationException(
            fn() => $ubahDraft(
                $draft,
                mulai: '2026-11-30',
                selesai: '2027-12-01'
            ),
            'tanggal_selesai'
        );
    });

    testCase('Draft milik pegawai lain tidak dapat diubah', function () use (
        $ubahDraft,
        $draft,
        $pegawai
    ) {
        $pegawaiLain = Pegawai::query()
            ->where('status', 'aktif')
            ->where('id', '!=', $pegawai->getKey())
            ->whereHas('user', function ($query) {
                $query->whereIn('role', [
                    'pegawai',
                    'atasan',
                    'kepala_bagian',
                ]);
            })
            ->firstOrFail();

        $sebelum = kondisiDraft($draft);

        assertValidationException(
            fn() => $ubahDraft(
                $draft,
                userId: $pegawaiLain->user_id
            ),
            'pengajuan_cuti_id'
        );

        assertSameValue(
            $sebelum,
            kondisiDraft($draft)
        );
    });

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
            "Pengajuan status {$status} tidak dapat diubah",
            function () use (
                $buatDraft,
                $ubahDraft,
                $status
            ) {
                $draftStatus = $buatDraft();

                $draftStatus->update([
                    'status' => $status,
                ]);

                $sebelum = kondisiDraft($draftStatus);

                assertValidationException(
                    fn() => $ubahDraft($draftStatus),
                    'status'
                );

                assertSameValue(
                    $sebelum,
                    kondisiDraft($draftStatus)
                );
            }
        );
    }

    testCase('Draft yang memiliki nomor pengajuan ditolak', function () use (
        $buatDraft,
        $ubahDraft
    ) {
        $draftRusak = $buatDraft();

        $draftRusak->update([
            'nomor_pengajuan' =>
            'TEST-' . Str::upper(Str::random(8)),
        ]);

        assertValidationException(
            fn() => $ubahDraft($draftRusak),
            'status'
        );
    });

    testCase('Draft yang memiliki tanggal pengajuan ditolak', function () use (
        $buatDraft,
        $ubahDraft
    ) {
        $draftRusak = $buatDraft();

        $draftRusak->update([
            'tanggal_pengajuan' => '2026-11-23',
        ]);

        assertValidationException(
            fn() => $ubahDraft($draftRusak),
            'status'
        );
    });

    testCase('Draft dengan jumlah hari disetujui ditolak', function () use (
        $buatDraft,
        $ubahDraft
    ) {
        $draftRusak = $buatDraft();

        $draftRusak->update([
            'jumlah_hari_disetujui' => 1,
        ]);

        assertValidationException(
            fn() => $ubahDraft($draftRusak),
            'status'
        );
    });

    testCase('Draft dengan detail yang telah disetujui ditolak', function () use (
        $buatDraft,
        $ubahDraft
    ) {
        $draftRusak = $buatDraft();

        $detail = $draftRusak->details()->firstOrFail();

        $detail->update([
            'status' => DetailPengajuanCuti::STATUS_APPROVED,
            'disetujui_pada' => '2026-11-23 09:00:00',
        ]);

        assertValidationException(
            fn() => $ubahDraft($draftRusak),
            'status'
        );
    });

    testCase('Draft dengan detail yang telah ditolak ditolak', function () use (
        $buatDraft,
        $ubahDraft
    ) {
        $draftRusak = $buatDraft();

        $detail = $draftRusak->details()->firstOrFail();

        $detail->update([
            'status' => DetailPengajuanCuti::STATUS_REJECTED,
            'ditolak_pada' => '2026-11-23 09:00:00',
        ]);

        assertValidationException(
            fn() => $ubahDraft($draftRusak),
            'status'
        );
    });

    testCase('Validasi gagal mempertahankan seluruh data draft', function () use (
        $buatDraft,
        $ubahDraft
    ) {
        $draftUji = $buatDraft();

        $sebelum = kondisiDraft($draftUji);

        assertValidationException(
            fn() => $ubahDraft(
                $draftUji,
                alasan: ''
            ),
            'alasan'
        );

        assertSameValue(
            $sebelum,
            kondisiDraft($draftUji)
        );
    });

    testCase('Pembaruan draft mendukung rollback transaction luar', function () use (
        $buatDraft,
        $ubahDraft
    ) {
        $draftUji = $buatDraft();

        $sebelum = kondisiDraft($draftUji);

        DB::beginTransaction();

        try {
            $hasil = $ubahDraft($draftUji);

            assertSameValue(
                3,
                $hasil->jumlah_hari_diajukan
            );

            assertSameValue(
                5,
                $hasil->details()->count()
            );
        } finally {
            DB::rollBack();
        }

        assertSameValue(
            $sebelum,
            kondisiDraft($draftUji)
        );
    });

    testCase('Kegagalan penyimpanan detail mengembalikan draft lama', function () use (
        $buatDraft,
        $ubahDraft
    ) {
        $draftUji = $buatDraft();

        $sebelum = kondisiDraft($draftUji);

        $dispatcherAwal =
            DetailPengajuanCuti::getEventDispatcher();

        $dispatcherUji = clone $dispatcherAwal;

        $jumlahDetailBaru = 0;

        $dispatcherUji->listen(
            'eloquent.creating: ' . DetailPengajuanCuti::class,
            function () use (&$jumlahDetailBaru) {
                $jumlahDetailBaru++;

                if ($jumlahDetailBaru === 2) {
                    throw new RuntimeException(
                        'Simulasi kegagalan detail'
                    );
                }
            }
        );

        DetailPengajuanCuti::setEventDispatcher(
            $dispatcherUji
        );

        $terjadiKegagalan = false;

        try {
            $ubahDraft($draftUji);
        } catch (RuntimeException $e) {
            if (
                $e->getMessage()
                !== 'Simulasi kegagalan detail'
            ) {
                throw $e;
            }

            $terjadiKegagalan = true;
        } finally {
            DetailPengajuanCuti::setEventDispatcher(
                $dispatcherAwal
            );
        }

        assertSameValue(
            true,
            $terjadiKegagalan
        );

        assertSameValue(
            2,
            $jumlahDetailBaru
        );

        assertSameValue(
            $sebelum,
            kondisiDraft($draftUji)
        );
    });

    testCase('Pembaruan tidak membuat kuota baru', function () use (
        $dataAwal
    ) {
        assertSameValue(
            $dataAwal['kuota_cuti'],
            KuotaCuti::count()
        );
    });

    testCase('Pembaruan tidak membuat mutasi kuota', function () use (
        $dataAwal
    ) {
        assertSameValue(
            $dataAwal['mutasi_kuota_cuti'],
            MutasiKuotaCuti::count()
        );
    });

    testCase('Pembaruan tidak membuat data persetujuan', function () use (
        $dataAwal
    ) {
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