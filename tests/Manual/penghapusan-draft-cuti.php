<?php

use App\Models\DetailPengajuanCuti;
use App\Models\JenisCuti;
use App\Models\KuotaCuti;
use App\Models\MutasiKuotaCuti;
use App\Models\Pegawai;
use App\Models\PengajuanCuti;
use App\Models\PersetujuanCuti;
use App\Models\SkCuti;
use App\Models\SuratPermohonanCuti;
use App\Services\Cuti\PengajuanCutiService;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Database\Eloquent\ModelNotFoundException;
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

function assertModelNotFoundException(
    callable $callback
): void {
    try {
        $callback();
    } catch (ModelNotFoundException $e) {
        return;
    }

    throw new RuntimeException(
        'ModelNotFoundException seharusnya terjadi.'
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
        'surat_permohonan_cuti' => SuratPermohonanCuti::count(),
        'sk_cuti' => SkCuti::count(),
    ];
}

function buatJenisCuti(array $attributes = []): JenisCuti
{
    return JenisCuti::query()->create(array_merge([
        'kode' => 'T-' . Str::upper(Str::random(6)),
        'nama' => 'Jenis Cuti Pengujian',
        'deskripsi' => 'Data sementara penghapusan draft',
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

function pastikanDraftTerhapus(int $pengajuanId): void
{
    assertSameValue(
        false,
        PengajuanCuti::query()
            ->whereKey($pengajuanId)
            ->exists()
    );

    assertSameValue(
        0,
        DetailPengajuanCuti::query()
            ->where('pengajuan_cuti_id', $pengajuanId)
            ->count()
    );
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
echo ' PENGUJIAN PENGHAPUSAN DRAFT CUTI' . PHP_EOL;
echo '========================================' . PHP_EOL;
echo PHP_EOL;

DB::beginTransaction();

try {
    $pegawai = Pegawai::query()
        ->with('user')
        ->where('status', 'aktif')
        ->whereHas('user', function ($query) {
            $query->where('role', 'pegawai');
        })
        ->firstOrFail();

    $pegawaiLain = Pegawai::query()
        ->with('user')
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

    $jenisCuti = buatJenisCuti();

    $buatDraft = function (
        ?string $lampiran = null,
        ?int $jenisCutiId = null,
        ?int $userId = null
    ) use (
        $service,
        $pegawai,
        $jenisCuti
    ): PengajuanCuti {
        return $service->buatDraft(
            $userId ?? $pegawai->user_id,
            $jenisCutiId ?? $jenisCuti->getKey(),
            '2026-11-30',
            '2026-12-06',
            'Keperluan keluarga',
            $lampiran
        );
    };

    $hapusDraft = function (
        PengajuanCuti $draft,
        ?int $userId = null
    ) use (
        $service,
        $pegawai
    ): array {
        return $service->hapusDraft(
            $userId ?? $pegawai->user_id,
            $draft->getKey()
        );
    };

    $draft = $buatDraft(
        lampiran: 'cuti/lampiran/test.pdf'
    );

    $draftId = $draft->getKey();

    $jumlahPengajuanSebelum = PengajuanCuti::count();

    $jumlahDetailSebelum = DetailPengajuanCuti::count();

    $detailIdAwal = $draft->details()
        ->pluck('id')
        ->all();

    $hasil = null;

    testCase('Draft berhasil dihapus', function () use (
        $hapusDraft,
        $draft,
        &$hasil
    ) {
        $hasil = $hapusDraft($draft);

        assertSameValue(
            true,
            $hasil['deleted']
        );
    });

    testCase('Hasil penghapusan mengembalikan ID pengajuan', function () use (
        &$hasil,
        $draftId
    ) {
        assertSameValue(
            $draftId,
            $hasil['pengajuan_cuti_id']
        );
    });

    testCase('Hasil penghapusan mengembalikan ID pegawai', function () use (
        &$hasil,
        $pegawai
    ) {
        assertSameValue(
            $pegawai->getKey(),
            $hasil['pegawai_id']
        );
    });

    testCase('Hasil penghapusan mengembalikan jumlah detail', function () use (
        &$hasil
    ) {
        assertSameValue(
            7,
            $hasil['jumlah_detail_dihapus']
        );
    });

    testCase('Hasil penghapusan mengembalikan path lampiran', function () use (
        &$hasil
    ) {
        assertSameValue(
            'cuti/lampiran/test.pdf',
            $hasil['lampiran']
        );
    });

    testCase('Record pengajuan benar-benar terhapus', function () use (
        $draftId
    ) {
        assertSameValue(
            false,
            PengajuanCuti::query()
                ->whereKey($draftId)
                ->exists()
        );
    });

    testCase('Seluruh detail pengajuan terhapus', function () use (
        $draftId
    ) {
        assertSameValue(
            0,
            DetailPengajuanCuti::query()
                ->where('pengajuan_cuti_id', $draftId)
                ->count()
        );
    });

    testCase('Seluruh ID detail lama tidak ditemukan', function () use (
        $detailIdAwal
    ) {
        assertSameValue(
            0,
            DetailPengajuanCuti::query()
                ->whereIn('id', $detailIdAwal)
                ->count()
        );
    });

    testCase('Jumlah pengajuan berkurang satu', function () use (
        $jumlahPengajuanSebelum
    ) {
        assertSameValue(
            $jumlahPengajuanSebelum - 1,
            PengajuanCuti::count()
        );
    });

    testCase('Jumlah detail berkurang tujuh', function () use (
        $jumlahDetailSebelum
    ) {
        assertSameValue(
            $jumlahDetailSebelum - 7,
            DetailPengajuanCuti::count()
        );
    });

    testCase('Draft tanpa lampiran dapat dihapus', function () use (
        $buatDraft,
        $hapusDraft
    ) {
        $draftUji = $buatDraft();

        $hasil = $hapusDraft($draftUji);

        assertSameValue(
            null,
            $hasil['lampiran']
        );

        pastikanDraftTerhapus(
            $draftUji->getKey()
        );
    });

    testCase('Penghapusan berulang ditolak', function () use (
        $hapusDraft,
        $draft
    ) {
        assertModelNotFoundException(
            fn() => $hapusDraft($draft)
        );
    });

    testCase('Penghapusan tidak mengganggu draft lain', function () use (
        $buatDraft,
        $hapusDraft
    ) {
        $draftPertama = $buatDraft();

        $draftKedua = $buatDraft();

        $sebelum = kondisiDraft($draftKedua);

        $hapusDraft($draftPertama);

        pastikanDraftTerhapus(
            $draftPertama->getKey()
        );

        assertSameValue(
            $sebelum,
            kondisiDraft($draftKedua)
        );
    });

    testCase('Draft dengan jenis cuti nonaktif dapat dihapus', function () use (
        $buatDraft,
        $hapusDraft
    ) {
        $jenis = buatJenisCuti();

        $draftUji = $buatDraft(
            jenisCutiId: $jenis->getKey()
        );

        $jenis->update([
            'is_active' => false,
        ]);

        $hasil = $hapusDraft($draftUji);

        assertSameValue(
            true,
            $hasil['deleted']
        );

        pastikanDraftTerhapus(
            $draftUji->getKey()
        );
    });

    testCase('Draft dengan tanggal lampau dapat dihapus', function () use (
        $buatDraft,
        $hapusDraft
    ) {
        $draftUji = $buatDraft();

        CarbonImmutable::setTestNow(
            CarbonImmutable::parse(
                '2026-12-10 09:00:00',
                'Asia/Jakarta'
            )
        );

        try {
            $hasil = $hapusDraft($draftUji);

            assertSameValue(
                true,
                $hasil['deleted']
            );

            pastikanDraftTerhapus(
                $draftUji->getKey()
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

    testCase('Draft dengan metode hari kalender dapat dihapus', function () use (
        $buatDraft,
        $hapusDraft
    ) {
        $jenis = buatJenisCuti([
            'metode_hitung_hari' => 'hari_kalender',
        ]);

        $draftUji = $buatDraft(
            jenisCutiId: $jenis->getKey()
        );

        assertSameValue(
            7,
            $draftUji->jumlah_hari_diajukan
        );

        $hasil = $hapusDraft($draftUji);

        assertSameValue(
            7,
            $hasil['jumlah_detail_dihapus']
        );

        pastikanDraftTerhapus(
            $draftUji->getKey()
        );
    });

    testCase('Draft tanpa lampiran wajib dapat dihapus', function () use (
        $buatDraft,
        $hapusDraft
    ) {
        $jenis = buatJenisCuti([
            'wajib_lampiran' => true,
        ]);

        $draftUji = $buatDraft(
            jenisCutiId: $jenis->getKey()
        );

        $hapusDraft($draftUji);

        pastikanDraftTerhapus(
            $draftUji->getKey()
        );
    });

    testCase('Draft milik pegawai lain tidak dapat dihapus', function () use (
        $buatDraft,
        $hapusDraft,
        $pegawaiLain
    ) {
        $draftUji = $buatDraft();

        $sebelum = kondisiDraft($draftUji);

        assertValidationException(
            fn() => $hapusDraft(
                $draftUji,
                userId: $pegawaiLain->user_id
            ),
            'pengajuan_cuti_id'
        );

        assertSameValue(
            $sebelum,
            kondisiDraft($draftUji)
        );
    });

    foreach (['atasan', 'kepala_bagian'] as $role) {
        testCase(
            "Pemohon role {$role} dapat menghapus draft sendiri",
            function () use (
                $role,
                $buatDraft,
                $hapusDraft
            ) {
                $pemohon = Pegawai::query()
                    ->where('status', 'aktif')
                    ->whereHas('user', function ($query) use ($role) {
                        $query->where('role', $role);
                    })
                    ->firstOrFail();

                $draftUji = $buatDraft(
                    userId: $pemohon->user_id
                );

                $hasil = $hapusDraft(
                    $draftUji,
                    userId: $pemohon->user_id
                );

                assertSameValue(
                    true,
                    $hasil['deleted']
                );

                pastikanDraftTerhapus(
                    $draftUji->getKey()
                );
            }
        );
    }

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
            "Pengajuan status {$status} tidak dapat dihapus",
            function () use (
                $buatDraft,
                $hapusDraft,
                $status
            ) {
                $draftUji = $buatDraft();

                $draftUji->update([
                    'status' => $status,
                ]);

                $sebelum = kondisiDraft($draftUji);

                assertValidationException(
                    fn() => $hapusDraft($draftUji),
                    'status'
                );

                assertSameValue(
                    $sebelum,
                    kondisiDraft($draftUji)
                );
            }
        );
    }

    $riwayatTerlarang = [
        'nomor_pengajuan' =>
        'TEST-' . Str::upper(Str::random(8)),
        'tanggal_pengajuan' => '2026-11-23',
        'diajukan_pada' => '2026-11-23 09:00:00',
        'current_approver_id' => $pegawaiLain->getKey(),
        'diselesaikan_pada' => '2026-11-23 09:00:00',
        'dibatalkan_pada' => '2026-11-23 09:00:00',
        'alasan_pembatalan' => 'Simulasi pembatalan',
        'jumlah_hari_disetujui' => 1,
    ];

    foreach ($riwayatTerlarang as $field => $value) {
        testCase(
            "Draft dengan riwayat {$field} tidak dapat dihapus",
            function () use (
                $buatDraft,
                $hapusDraft,
                $field,
                $value
            ) {
                $draftUji = $buatDraft();

                $draftUji->update([
                    $field => $value,
                ]);

                $sebelum = kondisiDraft($draftUji);

                assertValidationException(
                    fn() => $hapusDraft($draftUji),
                    'status'
                );

                assertSameValue(
                    $sebelum,
                    kondisiDraft($draftUji)
                );
            }
        );
    }

    $statusDetailTerlarang = [
        DetailPengajuanCuti::STATUS_APPROVED,
        DetailPengajuanCuti::STATUS_REJECTED,
        DetailPengajuanCuti::STATUS_CANCELLED,
    ];

    foreach ($statusDetailTerlarang as $status) {
        testCase(
            "Draft dengan detail {$status} tidak dapat dihapus",
            function () use (
                $buatDraft,
                $hapusDraft,
                $status
            ) {
                $draftUji = $buatDraft();

                $draftUji->details()
                    ->firstOrFail()
                    ->update([
                        'status' => $status,
                    ]);

                $sebelum = kondisiDraft($draftUji);

                assertValidationException(
                    fn() => $hapusDraft($draftUji),
                    'status'
                );

                assertSameValue(
                    $sebelum,
                    kondisiDraft($draftUji)
                );
            }
        );
    }

    $riwayatDetailTerlarang = [
        'ditolak_oleh' => $pegawaiLain->getKey(),
        'ditolak_pada' => '2026-11-23 09:00:00',
        'disetujui_pada' => '2026-11-23 09:00:00',
    ];

    foreach ($riwayatDetailTerlarang as $field => $value) {
        testCase(
            "Draft dengan riwayat detail {$field} tidak dapat dihapus",
            function () use (
                $buatDraft,
                $hapusDraft,
                $field,
                $value
            ) {
                $draftUji = $buatDraft();

                $draftUji->details()
                    ->firstOrFail()
                    ->update([
                        $field => $value,
                    ]);

                $sebelum = kondisiDraft($draftUji);

                assertValidationException(
                    fn() => $hapusDraft($draftUji),
                    'status'
                );

                assertSameValue(
                    $sebelum,
                    kondisiDraft($draftUji)
                );
            }
        );
    }

    testCase('Draft dengan data persetujuan tidak dapat dihapus', function () use (
        $buatDraft,
        $hapusDraft
    ) {
        $draftUji = $buatDraft();

        $atasan = Pegawai::query()
            ->where('status', 'aktif')
            ->whereHas('user', function ($query) {
                $query->where('role', 'atasan');
            })
            ->firstOrFail();

        $persetujuan = PersetujuanCuti::query()->create([
            'pengajuan_cuti_id' => $draftUji->getKey(),
            'approver_id' => $atasan->getKey(),
            'role_approver' => 'atasan',
            'urutan' => 1,
            'status' => PersetujuanCuti::STATUS_WAITING,
            'jumlah_hari_diterima' => 0,
            'jumlah_hari_disetujui' => 0,
            'jumlah_hari_ditolak' => 0,
            'approver_snapshot' => [
                'pegawai_id' => $atasan->getKey(),
                'nama' => $atasan->nama,
                'role' => 'atasan',
            ],
        ]);

        $sebelum = kondisiDraft($draftUji);

        try {
            assertValidationException(
                fn() => $hapusDraft($draftUji),
                'status'
            );

            assertSameValue(
                $sebelum,
                kondisiDraft($draftUji)
            );

            assertSameValue(
                true,
                PersetujuanCuti::query()
                    ->whereKey($persetujuan->getKey())
                    ->exists()
            );
        } finally {
            $persetujuan->delete();
        }
    });

    testCase('Pegawai nonaktif tidak dapat menghapus draft', function () use (
        $buatDraft,
        $hapusDraft,
        $pegawai
    ) {
        $draftUji = $buatDraft();

        $sebelum = kondisiDraft($draftUji);

        DB::beginTransaction();

        try {
            $pegawai->update([
                'status' => 'nonaktif',
            ]);

            assertValidationException(
                fn() => $hapusDraft($draftUji),
                'pegawai_id'
            );

            assertSameValue(
                $sebelum,
                kondisiDraft($draftUji)
            );
        } finally {
            DB::rollBack();

            $pegawai->refresh();
        }
    });

    testCase('Penghapusan mendukung rollback transaction luar', function () use (
        $buatDraft,
        $hapusDraft
    ) {
        $draftUji = $buatDraft(
            lampiran: 'cuti/lampiran/rollback.pdf'
        );

        $sebelum = kondisiDraft($draftUji);

        $pengajuanSebelum = PengajuanCuti::count();

        $detailSebelum = DetailPengajuanCuti::count();

        DB::beginTransaction();

        try {
            $hapusDraft($draftUji);

            pastikanDraftTerhapus(
                $draftUji->getKey()
            );

            assertSameValue(
                $pengajuanSebelum - 1,
                PengajuanCuti::count()
            );

            assertSameValue(
                $detailSebelum - 7,
                DetailPengajuanCuti::count()
            );
        } finally {
            DB::rollBack();
        }

        assertSameValue(
            $sebelum,
            kondisiDraft($draftUji)
        );

        assertSameValue(
            $pengajuanSebelum,
            PengajuanCuti::count()
        );

        assertSameValue(
            $detailSebelum,
            DetailPengajuanCuti::count()
        );
    });

    testCase('Kegagalan penghapusan pengajuan mengembalikan detail', function () use (
        $buatDraft,
        $hapusDraft
    ) {
        $draftUji = $buatDraft();

        $sebelum = kondisiDraft($draftUji);

        $dispatcherAwal =
            PengajuanCuti::getEventDispatcher();

        $dispatcherUji = clone $dispatcherAwal;

        $detailSaatGagal = null;

        $dispatcherUji->listen(
            'eloquent.deleting: ' . PengajuanCuti::class,
            function (PengajuanCuti $model) use (
                $draftUji,
                &$detailSaatGagal
            ) {
                if (
                    (int) $model->getKey()
                    !== (int) $draftUji->getKey()
                ) {
                    return;
                }

                $detailSaatGagal = $model->details()
                    ->count();

                throw new RuntimeException(
                    'Simulasi kegagalan penghapusan pengajuan'
                );
            }
        );

        PengajuanCuti::setEventDispatcher(
            $dispatcherUji
        );

        $terjadiKegagalan = false;

        try {
            $hapusDraft($draftUji);
        } catch (RuntimeException $e) {
            if (
                $e->getMessage()
                !== 'Simulasi kegagalan penghapusan pengajuan'
            ) {
                throw $e;
            }

            $terjadiKegagalan = true;
        } finally {
            PengajuanCuti::setEventDispatcher(
                $dispatcherAwal
            );
        }

        assertSameValue(
            true,
            $terjadiKegagalan
        );

        assertSameValue(
            0,
            $detailSaatGagal
        );

        assertSameValue(
            $sebelum,
            kondisiDraft($draftUji)
        );
    });

    testCase('Pembatalan event deleting mengembalikan detail', function () use (
        $buatDraft,
        $hapusDraft
    ) {
        $draftUji = $buatDraft();

        $sebelum = kondisiDraft($draftUji);

        $dispatcherAwal =
            PengajuanCuti::getEventDispatcher();

        $dispatcherUji = clone $dispatcherAwal;

        $dispatcherUji->listen(
            'eloquent.deleting: ' . PengajuanCuti::class,
            function (PengajuanCuti $model) use ($draftUji) {
                if (
                    (int) $model->getKey()
                    === (int) $draftUji->getKey()
                ) {
                    return false;
                }
            }
        );

        PengajuanCuti::setEventDispatcher(
            $dispatcherUji
        );

        $terjadiKegagalan = false;

        try {
            $hapusDraft($draftUji);
        } catch (RuntimeException $e) {
            if (
                $e->getMessage()
                !== 'Draft pengajuan cuti gagal dihapus.'
            ) {
                throw $e;
            }

            $terjadiKegagalan = true;
        } finally {
            PengajuanCuti::setEventDispatcher(
                $dispatcherAwal
            );
        }

        assertSameValue(
            true,
            $terjadiKegagalan
        );

        assertSameValue(
            $sebelum,
            kondisiDraft($draftUji)
        );
    });

    testCase('Penghapusan tidak mengubah jumlah kuota', function () use (
        $dataAwal
    ) {
        assertSameValue(
            $dataAwal['kuota_cuti'],
            KuotaCuti::count()
        );
    });

    testCase('Penghapusan tidak membuat mutasi kuota', function () use (
        $dataAwal
    ) {
        assertSameValue(
            $dataAwal['mutasi_kuota_cuti'],
            MutasiKuotaCuti::count()
        );
    });

    testCase('Penghapusan tidak membuat data persetujuan', function () use (
        $dataAwal
    ) {
        assertSameValue(
            $dataAwal['persetujuan_cuti'],
            PersetujuanCuti::count()
        );
    });

    testCase('Penghapusan tidak membuat surat permohonan', function () use (
        $dataAwal
    ) {
        assertSameValue(
            $dataAwal['surat_permohonan_cuti'],
            SuratPermohonanCuti::count()
        );
    });

    testCase('Penghapusan tidak membuat SK cuti', function () use (
        $dataAwal
    ) {
        assertSameValue(
            $dataAwal['sk_cuti'],
            SkCuti::count()
        );
    });

    testCase('Penghapusan tidak menghapus master jenis cuti', function () use (
        $buatDraft,
        $hapusDraft,
        $jenisCuti
    ) {
        $draftUji = $buatDraft();

        $jumlahJenisSebelum = JenisCuti::count();

        $hapusDraft($draftUji);

        assertSameValue(
            $jumlahJenisSebelum,
            JenisCuti::count()
        );

        assertSameValue(
            true,
            JenisCuti::query()
                ->whereKey($jenisCuti->getKey())
                ->exists()
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