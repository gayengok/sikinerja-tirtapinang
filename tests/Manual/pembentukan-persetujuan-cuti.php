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
use App\Services\Cuti\KuotaCutiService;
use App\Services\Cuti\PembentukanPersetujuanCutiService;
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

$service = app(
    PembentukanPersetujuanCutiService::class
);

$pengajuanService = app(
    PengajuanCutiService::class
);

$pemetaanService = app(
    PemetaanKuotaCutiService::class
);

$kuotaService = app(
    KuotaCutiService::class
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
                . PHP_EOL
                . 'Actual: '
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
        'surat' => SuratPermohonanCuti::count(),
        'sk' => SkCuti::count(),
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
            'nama' => 'Cuti Pengujian Persetujuan',
            'deskripsi' => 'Data sementara',
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

function daftarPersetujuan(
    PengajuanCuti $pengajuan
) {
    return PersetujuanCuti::query()
        ->where(
            'pengajuan_cuti_id',
            $pengajuan->getKey()
        )
        ->orderBy('urutan')
        ->get();
}

function denganRollback(
    callable $callback
): void {
    $level = DB::transactionLevel();

    DB::beginTransaction();

    try {
        $callback();
    } finally {
        if (DB::transactionLevel() > $level) {
            DB::rollBack($level);
        }
    }
}

echo PHP_EOL;
echo "========================================" . PHP_EOL;
echo " PENGUJIAN PEMBENTUKAN PERSETUJUAN CUTI" . PHP_EOL;
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
    'Pembentukan di luar transaksi ditolak',
    function () use ($service) {
        try {
            $service->bentuk(1, 1);
        } catch (LogicException $e) {
            assertSameValue(
                'Pembentukan persetujuan wajib berada dalam transaksi pengajuan cuti.',
                $e->getMessage()
            );

            return;
        }

        throw new RuntimeException(
            'LogicException tidak terjadi.'
        );
    }
);

$levelAwal = DB::transactionLevel();

DB::beginTransaction();

try {
    $pegawai = Pegawai::findOrFail(2);
    $atasan = Pegawai::findOrFail(1);
    $kepalaBagian = Pegawai::findOrFail(10);
    $pegawaiLain = Pegawai::findOrFail(3);

    $buatDraft = function (
        int $pegawaiId = 2,
        array $attributes = []
    ) use ($pengajuanService): PengajuanCuti {
        $pemohon = Pegawai::findOrFail(
            $pegawaiId
        );

        $pemohon->update([
            'tanggal_masuk' => '2020-01-01',
        ]);

        $jenis = buatJenisCuti($attributes);

        return $pengajuanService->buatDraft(
            $pemohon->user_id,
            $jenis->getKey(),
            '2026-11-30',
            '2026-12-06',
            'Pengujian pembentukan persetujuan'
        );
    };

    $petakan = function (
        PengajuanCuti $draft
    ) use ($pemetaanService): KuotaCuti {
        $pemohon = Pegawai::findOrFail(
            $draft->pegawai_id
        );

        $pemetaanService->petakan(
            $draft->getKey(),
            $pemohon->user_id
        );

        $detail = $draft->details()
            ->whereNotNull('kuota_cuti_id')
            ->firstOrFail();

        return KuotaCuti::findOrFail(
            $detail->kuota_cuti_id
        );
    };

    $siapkan = function (
        int $pegawaiId = 2,
        array $attributes = []
    ) use (
        $buatDraft,
        $petakan,
        $kuotaService
    ): array {
        $draft = $buatDraft(
            $pegawaiId,
            $attributes
        );

        $kuota = $petakan($draft);

        $pemohon = Pegawai::findOrFail(
            $pegawaiId
        );

        $kuotaService->reservasiPengajuan(
            $draft->getKey(),
            $pemohon->user_id
        );

        return [
            $draft,
            $kuota->refresh(),
        ];
    };

    $bentuk = function (
        PengajuanCuti $draft
    ) use ($service): array {
        $pemohon = Pegawai::findOrFail(
            $draft->pegawai_id
        );

        return $service->bentuk(
            $draft->getKey(),
            $pemohon->user_id
        );
    };

    [$draft, $kuota] = $siapkan();

    $saldoSebelum = $kuota->snapshotSaldo();

    $mutasiSebelum = MutasiKuotaCuti::count();

    $hasil = null;

    $tanggal = [
        '2026-11-30',
        '2026-12-01',
        '2026-12-02',
        '2026-12-03',
        '2026-12-04',
    ];

    testCase(
        'Pembentukan persetujuan pegawai berhasil',
        function () use (
            $draft,
            $bentuk,
            &$hasil
        ) {
            $hasil = $bentuk($draft);

            assertSameValue(
                3,
                $hasil['jumlah_tahapan']
            );
        }
    );

    testCase(
        'Hasil memuat identitas pengajuan',
        function () use ($draft, &$hasil) {
            assertSameValue(
                (int) $draft->getKey(),
                $hasil['pengajuan_cuti_id']
            );

            assertSameValue(
                2,
                $hasil['pegawai_id']
            );

            assertSameValue(
                'pegawai',
                $hasil['role_pemohon']
            );
        }
    );

    testCase(
        'Hasil memuat lima hari pengajuan',
        function () use (&$hasil) {
            assertSameValue(
                5,
                $hasil['jumlah_hari']
            );
        }
    );

    testCase(
        'Tiga record persetujuan tersimpan',
        function () use ($draft) {
            assertSameValue(
                3,
                daftarPersetujuan($draft)->count()
            );
        }
    );

    testCase(
        'Urutan approver sesuai hierarki',
        function () use ($draft) {
            assertSameValue(
                [1, 10, 11],
                daftarPersetujuan($draft)
                    ->pluck('approver_id')
                    ->map(fn($id) => (int) $id)
                    ->all()
            );
        }
    );

    testCase(
        'Role approver sesuai hierarki',
        function () use ($draft) {
            assertSameValue([
                'atasan',
                'kepala_bagian',
                'direktur',
            ], daftarPersetujuan($draft)
                ->pluck('role_approver')
                ->all());
        }
    );

    testCase(
        'Nomor urutan persetujuan sesuai',
        function () use ($draft) {
            assertSameValue(
                [1, 2, 3],
                daftarPersetujuan($draft)
                    ->pluck('urutan')
                    ->all()
            );
        }
    );

    testCase(
        'Hanya approver pertama berstatus pending',
        function () use ($draft) {
            assertSameValue([
                PersetujuanCuti::STATUS_PENDING,
                PersetujuanCuti::STATUS_WAITING,
                PersetujuanCuti::STATUS_WAITING,
            ], daftarPersetujuan($draft)
                ->pluck('status')
                ->all());
        }
    );

    testCase(
        'Current approver mengarah ke atasan langsung',
        function () use (&$hasil) {
            assertSameValue(
                1,
                (int) $hasil['current_approver_id']
            );
        }
    );

    testCase(
        'Status tujuan pengajuan adalah pending_atasan',
        function () use (&$hasil) {
            assertSameValue(
                PengajuanCuti::STATUS_PENDING_ATASAN,
                $hasil['status_pengajuan']
            );
        }
    );

    testCase(
        'Hasil memuat ID persetujuan pertama',
        function () use ($draft, &$hasil) {
            assertSameValue(
                (int) daftarPersetujuan($draft)
                    ->first()
                    ->getKey(),
                $hasil['persetujuan_pertama_id']
            );
        }
    );

    testCase(
        'Approver pertama menerima lima hari',
        function () use ($draft) {
            assertSameValue(
                5,
                daftarPersetujuan($draft)
                    ->first()
                    ->jumlah_hari_diterima
            );
        }
    );

    testCase(
        'Approver pertama menerima tanggal yang benar',
        function () use ($draft, $tanggal) {
            assertSameValue(
                $tanggal,
                daftarPersetujuan($draft)
                    ->first()
                    ->tanggal_diterima
            );
        }
    );

    testCase(
        'Tahapan waiting belum menerima hari',
        function () use ($draft) {
            foreach (
                daftarPersetujuan($draft)->skip(1)
                as $record
            ) {
                assertSameValue(
                    0,
                    $record->jumlah_hari_diterima
                );

                assertSameValue(
                    null,
                    $record->tanggal_diterima
                );
            }
        }
    );

    testCase(
        'Seluruh jumlah hari disetujui masih nol',
        function () use ($draft) {
            assertSameValue(
                [0, 0, 0],
                daftarPersetujuan($draft)
                    ->pluck('jumlah_hari_disetujui')
                    ->all()
            );
        }
    );

    testCase(
        'Seluruh jumlah hari ditolak masih nol',
        function () use ($draft) {
            assertSameValue(
                [0, 0, 0],
                daftarPersetujuan($draft)
                    ->pluck('jumlah_hari_ditolak')
                    ->all()
            );
        }
    );

    testCase(
        'Tanggal keputusan seluruh tahapan masih kosong',
        function () use ($draft) {
            foreach (
                daftarPersetujuan($draft)
                as $record
            ) {
                assertSameValue(
                    null,
                    $record->tanggal_disetujui
                );

                assertSameValue(
                    null,
                    $record->tanggal_ditolak
                );
            }
        }
    );

    testCase(
        'Approver pertama memiliki waktu penerimaan',
        function () use ($draft, $waktu) {
            $record = daftarPersetujuan(
                $draft
            )->first();

            assertSameValue(
                $waktu->getTimestamp(),
                $record->diterima_pada?->getTimestamp()
            );
        }
    );

    testCase(
        'Tahapan waiting belum memiliki waktu penerimaan',
        function () use ($draft) {
            foreach (
                daftarPersetujuan($draft)->skip(1)
                as $record
            ) {
                assertSameValue(
                    null,
                    $record->diterima_pada
                );
            }
        }
    );

    testCase(
        'Seluruh tahapan belum diproses',
        function () use ($draft) {
            foreach (
                daftarPersetujuan($draft)
                as $record
            ) {
                assertSameValue(
                    null,
                    $record->diproses_pada
                );
            }
        }
    );

    testCase(
        'Snapshot identitas approver sesuai',
        function () use ($draft) {
            foreach (
                daftarPersetujuan($draft)
                as $record
            ) {
                $snapshot = $record->approver_snapshot;

                assertSameValue(
                    (int) $record->approver_id,
                    (int) $snapshot['pegawai_id']
                );

                assertSameValue(
                    $record->role_approver,
                    $snapshot['role']
                );

                assertSameValue(
                    $record->approver->nama,
                    $snapshot['nama']
                );
            }
        }
    );

    testCase(
        'Snapshot tidak memuat password',
        function () use ($draft) {
            foreach (
                daftarPersetujuan($draft)
                as $record
            ) {
                assertSameValue(
                    false,
                    array_key_exists(
                        'password',
                        $record->approver_snapshot
                    )
                );
            }
        }
    );

    testCase(
        'Data tanda tangan dan verifikasi masih kosong',
        function () use ($draft) {
            foreach (
                daftarPersetujuan($draft)
                as $record
            ) {
                assertSameValue(
                    null,
                    $record->tanda_tangan_path
                );

                assertSameValue(
                    null,
                    $record->token_verifikasi
                );

                assertSameValue(
                    null,
                    $record->hash_verifikasi
                );

                assertSameValue(
                    null,
                    $record->catatan
                );
            }
        }
    );

    testCase(
        'Hanya tahapan pertama dapat diproses',
        function () use ($draft) {
            assertSameValue(
                [true, false, false],
                daftarPersetujuan($draft)
                    ->map(
                        fn($record) =>
                        $record->bisaDiproses()
                    )
                    ->all()
            );
        }
    );

    testCase(
        'Pembentukan tidak mengubah saldo kuota',
        function () use ($kuota, $saldoSebelum) {
            assertSameValue(
                $saldoSebelum,
                $kuota->fresh()->snapshotSaldo()
            );
        }
    );

    testCase(
        'Pembentukan tidak membuat mutasi baru',
        function () use ($mutasiSebelum) {
            assertSameValue(
                $mutasiSebelum,
                MutasiKuotaCuti::count()
            );
        }
    );

    testCase(
        'Status pengajuan masih draft',
        function () use ($draft) {
            assertSameValue(
                PengajuanCuti::STATUS_DRAFT,
                $draft->fresh()->status
            );
        }
    );

    testCase(
        'Current approver pengajuan belum disimpan',
        function () use ($draft) {
            assertSameValue(
                null,
                $draft->fresh()->current_approver_id
            );
        }
    );

    testCase(
        'Nomor dan tanggal pengajuan masih kosong',
        function () use ($draft) {
            $record = $draft->fresh();

            assertSameValue(
                null,
                $record->nomor_pengajuan
            );

            assertSameValue(
                null,
                $record->tanggal_pengajuan
            );

            assertSameValue(
                null,
                $record->diajukan_pada
            );
        }
    );

    testCase(
        'Pembentukan berulang ditolak',
        function () use ($draft, $bentuk) {
            assertValidationException(
                fn() => $bentuk($draft),
                'persetujuan'
            );

            assertSameValue(
                3,
                daftarPersetujuan($draft)->count()
            );
        }
    );

    testCase(
        'Pemohon atasan memiliki dua tahapan',
        function () use ($siapkan, $bentuk) {
            [$baru] = $siapkan(1);

            $hasil = $bentuk($baru);

            assertSameValue(
                2,
                $hasil['jumlah_tahapan']
            );

            assertSameValue(
                10,
                (int) $hasil['current_approver_id']
            );

            assertSameValue(
                PengajuanCuti::STATUS_PENDING_KEPALA_BAGIAN,
                $hasil['status_pengajuan']
            );

            assertSameValue(
                ['pending', 'waiting'],
                daftarPersetujuan($baru)
                    ->pluck('status')
                    ->all()
            );
        }
    );

    testCase(
        'Pemohon kepala bagian memiliki satu tahapan',
        function () use ($siapkan, $bentuk) {
            [$baru] = $siapkan(10);

            $hasil = $bentuk($baru);

            assertSameValue(
                1,
                $hasil['jumlah_tahapan']
            );

            assertSameValue(
                11,
                (int) $hasil['current_approver_id']
            );

            assertSameValue(
                PengajuanCuti::STATUS_PENDING_DIREKTUR,
                $hasil['status_pengajuan']
            );

            assertSameValue(
                ['pending'],
                daftarPersetujuan($baru)
                    ->pluck('status')
                    ->all()
            );
        }
    );

    testCase(
        'Kuota tanpa batas dapat membentuk persetujuan',
        function () use ($siapkan, $bentuk) {
            [$baru, $kuota] = $siapkan(2, [
                'periode_kuota' => 'tanpa_batas',
                'kuota_hari' => null,
            ]);

            $hasil = $bentuk($baru);

            assertSameValue(
                3,
                $hasil['jumlah_tahapan']
            );

            assertSameValue(
                5,
                $kuota->fresh()->kuota_direservasi
            );
        }
    );

    testCase(
        'Hari kalender meneruskan tujuh tanggal',
        function () use ($siapkan, $bentuk) {
            [$baru] = $siapkan(2, [
                'metode_hitung_hari' => 'hari_kalender',
            ]);

            $bentuk($baru);

            $pertama = daftarPersetujuan(
                $baru
            )->first();

            assertSameValue(
                7,
                $pertama->jumlah_hari_diterima
            );

            assertSameValue(
                7,
                count($pertama->tanggal_diterima)
            );
        }
    );

    testCase(
        'Draft tanpa pemetaan ditolak',
        function () use ($buatDraft, $bentuk) {
            $baru = $buatDraft();

            assertValidationException(
                fn() => $bentuk($baru),
                'kuota_cuti'
            );

            assertSameValue(
                0,
                daftarPersetujuan($baru)->count()
            );
        }
    );

    testCase(
        'Draft sudah dipetakan tanpa reservasi ditolak',
        function () use (
            $buatDraft,
            $petakan,
            $bentuk
        ) {
            $baru = $buatDraft();

            $petakan($baru);

            assertValidationException(
                fn() => $bentuk($baru),
                'kuota_cuti'
            );

            assertSameValue(
                0,
                daftarPersetujuan($baru)->count()
            );
        }
    );

    testCase(
        'Pengajuan pegawai lain ditolak',
        function () use (
            $service,
            $siapkan,
            $pegawaiLain
        ) {
            [$baru] = $siapkan();

            assertValidationException(
                fn() => $service->bentuk(
                    $baru->getKey(),
                    $pegawaiLain->user_id
                ),
                'pengajuan_cuti_id'
            );
        }
    );

    testCase(
        'Pegawai nonaktif ditolak',
        function () use ($siapkan, $bentuk) {
            denganRollback(function () use (
                $siapkan,
                $bentuk
            ) {
                [$baru] = $siapkan();

                Pegawai::findOrFail(2)->update([
                    'status' => 'nonaktif',
                ]);

                assertValidationException(
                    fn() => $bentuk($baru),
                    'pegawai_id'
                );
            });
        }
    );

    testCase(
        'Role pemohon tidak konsisten ditolak',
        function () use ($siapkan, $bentuk) {
            [$baru] = $siapkan();

            $baru->update([
                'role_pemohon' => 'atasan',
            ]);

            assertValidationException(
                fn() => $bentuk($baru),
                'role_pemohon'
            );
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
            "Status {$status} tidak dapat membentuk persetujuan awal",
            function () use (
                $siapkan,
                $bentuk,
                $status
            ) {
                [$baru] = $siapkan();

                $baru->update([
                    'status' => $status,
                ]);

                assertValidationException(
                    fn() => $bentuk($baru),
                    'status'
                );

                assertSameValue(
                    0,
                    daftarPersetujuan($baru)->count()
                );
            }
        );
    }

    testCase(
        'Draft dengan nomor pengajuan ditolak',
        function () use ($siapkan, $bentuk) {
            [$baru] = $siapkan();

            $baru->update([
                'nomor_pengajuan' =>
                'TEST-' . Str::uuid(),
            ]);

            assertValidationException(
                fn() => $bentuk($baru),
                'status'
            );
        }
    );

    testCase(
        'Jumlah hari pengajuan tidak konsisten ditolak',
        function () use ($siapkan, $bentuk) {
            [$baru] = $siapkan();

            $baru->update([
                'jumlah_hari_diajukan' => 6,
            ]);

            assertValidationException(
                fn() => $bentuk($baru),
                'tanggal'
            );
        }
    );

    testCase(
        'Detail tanggal tidak lengkap ditolak',
        function () use ($siapkan, $bentuk) {
            [$baru] = $siapkan();

            $baru->details()
                ->firstOrFail()
                ->delete();

            assertValidationException(
                fn() => $bentuk($baru),
                'tanggal'
            );
        }
    );

    testCase(
        'Detail yang sudah disetujui ditolak',
        function () use ($siapkan, $bentuk) {
            [$baru] = $siapkan();

            $baru->details()
                ->where('dihitung_kuota', true)
                ->firstOrFail()
                ->update([
                    'status' =>
                    DetailPengajuanCuti::STATUS_APPROVED,
                ]);

            assertValidationException(
                fn() => $bentuk($baru),
                'status'
            );
        }
    );

    testCase(
        'Detail excluded dengan status pending ditolak',
        function () use ($siapkan, $bentuk) {
            [$baru] = $siapkan();

            $baru->details()
                ->where('dihitung_kuota', false)
                ->firstOrFail()
                ->update([
                    'status' =>
                    DetailPengajuanCuti::STATUS_PENDING,
                ]);

            assertValidationException(
                fn() => $bentuk($baru),
                'status'
            );
        }
    );

    testCase(
        'Detail excluded yang memiliki kuota ditolak',
        function () use ($siapkan, $bentuk) {
            [$baru, $kuota] = $siapkan();

            $baru->details()
                ->where('dihitung_kuota', false)
                ->firstOrFail()
                ->update([
                    'kuota_cuti_id' => $kuota->getKey(),
                ]);

            assertValidationException(
                fn() => $bentuk($baru),
                'status'
            );
        }
    );

    testCase(
        'Kuota nonaktif ditolak',
        function () use ($siapkan, $bentuk) {
            [$baru, $kuota] = $siapkan();

            $kuota->update([
                'is_active' => false,
            ]);

            assertValidationException(
                fn() => $bentuk($baru),
                'kuota_cuti'
            );
        }
    );

    testCase(
        'Saldo reservasi tidak konsisten ditolak',
        function () use ($siapkan, $bentuk) {
            [$baru, $kuota] = $siapkan();

            $kuota->update([
                'kuota_direservasi' => 2,
            ]);

            assertValidationException(
                fn() => $bentuk($baru),
                'kuota_cuti'
            );
        }
    );

    testCase(
        'Kuota dengan jenis cuti berbeda ditolak',
        function () use ($siapkan, $bentuk) {
            [$pertama, $kuotaPertama] = $siapkan();

            [$kedua] = $siapkan();

            $kedua->details()
                ->where('dihitung_kuota', true)
                ->update([
                    'kuota_cuti_id' =>
                    $kuotaPertama->getKey(),
                ]);

            assertValidationException(
                fn() => $bentuk($kedua),
                'kuota_cuti'
            );
        }
    );

    testCase(
        'Tanggal di luar periode kuota ditolak',
        function () use ($siapkan, $bentuk) {
            [$baru, $kuota] = $siapkan();

            $kuota->update([
                'tanggal_akhir_periode' =>
                '2026-11-29',
            ]);

            assertValidationException(
                fn() => $bentuk($baru),
                'kuota_cuti'
            );
        }
    );

    testCase(
        'Hierarki tidak lengkap ditolak',
        function () use ($siapkan, $bentuk) {
            denganRollback(function () use (
                $siapkan,
                $bentuk
            ) {
                [$baru] = $siapkan();

                Pegawai::findOrFail(1)->update([
                    'atasan_id' => null,
                ]);

                assertValidationException(
                    fn() => $bentuk($baru),
                    'hierarki'
                );

                assertSameValue(
                    0,
                    daftarPersetujuan($baru)->count()
                );
            });
        }
    );

    testCase(
        'Approver nonaktif ditolak',
        function () use ($siapkan, $bentuk) {
            denganRollback(function () use (
                $siapkan,
                $bentuk
            ) {
                [$baru] = $siapkan();

                Pegawai::findOrFail(1)->update([
                    'status' => 'nonaktif',
                ]);

                assertValidationException(
                    fn() => $bentuk($baru),
                    'hierarki'
                );

                assertSameValue(
                    0,
                    daftarPersetujuan($baru)->count()
                );
            });
        }
    );

    testCase(
        'Pembentukan mendukung rollback transaksi luar',
        function () use ($siapkan, $bentuk) {
            [$baru] = $siapkan();

            $sebelum = jumlahData();

            denganRollback(function () use (
                $baru,
                $bentuk
            ) {
                $bentuk($baru);

                assertSameValue(
                    3,
                    daftarPersetujuan($baru)->count()
                );
            });

            assertSameValue(
                $sebelum,
                jumlahData()
            );

            assertSameValue(
                0,
                daftarPersetujuan($baru)->count()
            );
        }
    );

    testCase(
        'Kegagalan tahapan kedua membatalkan seluruh persetujuan',
        function () use ($siapkan, $bentuk) {
            [$baru, $kuota] = $siapkan();

            $saldoAwal = $kuota->snapshotSaldo();

            $dispatcherAsli =
                PersetujuanCuti::getEventDispatcher();

            $dispatcher = clone $dispatcherAsli;

            $dispatcher->listen(
                'eloquent.creating: '
                    . PersetujuanCuti::class,
                function (PersetujuanCuti $record) {
                    if ((int) $record->urutan === 2) {
                        throw new RuntimeException(
                            'Simulasi kegagalan tahapan kedua'
                        );
                    }
                }
            );

            PersetujuanCuti::setEventDispatcher(
                $dispatcher
            );

            $gagal = false;

            try {
                $bentuk($baru);
            } catch (RuntimeException $e) {
                if (
                    $e->getMessage()
                    !== 'Simulasi kegagalan tahapan kedua'
                ) {
                    throw $e;
                }

                $gagal = true;
            } finally {
                PersetujuanCuti::setEventDispatcher(
                    $dispatcherAsli
                );
            }

            assertSameValue(true, $gagal);

            assertSameValue(
                0,
                daftarPersetujuan($baru)->count()
            );

            assertSameValue(
                $saldoAwal,
                $kuota->fresh()->snapshotSaldo()
            );
        }
    );

    testCase(
        'Pemetaan reservasi dan persetujuan dapat di-rollback bersama',
        function () use (
            $buatDraft,
            $petakan,
            $kuotaService,
            $bentuk,
            $pegawai
        ) {
            $baru = $buatDraft();

            $sebelum = jumlahData();

            denganRollback(function () use (
                $baru,
                $petakan,
                $kuotaService,
                $bentuk,
                $pegawai
            ) {
                $kuota = $petakan($baru);

                $kuotaService->reservasiPengajuan(
                    $baru->getKey(),
                    $pegawai->user_id
                );

                $bentuk($baru);

                assertSameValue(
                    3,
                    daftarPersetujuan($baru)->count()
                );

                assertSameValue(
                    5,
                    $kuota->fresh()->kuota_direservasi
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

            assertSameValue(
                0,
                daftarPersetujuan($baru)->count()
            );
        }
    );

    testCase(
        'Pembentukan tidak membuat surat permohonan',
        function () use ($dataAwal) {
            assertSameValue(
                $dataAwal['surat'],
                SuratPermohonanCuti::count()
            );
        }
    );

    testCase(
        'Pembentukan tidak membuat SK cuti',
        function () use ($dataAwal) {
            assertSameValue(
                $dataAwal['sk'],
                SkCuti::count()
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