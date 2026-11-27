<?php

use App\Models\TemplateSuratCuti;
use App\Models\User;
use App\Services\Cuti\LogoTemplateCutiService;
use App\Services\Cuti\SnapshotTemplateSuratCutiService;
use App\Services\Cuti\TemplateSuratCutiService;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

require __DIR__ . '/../../vendor/autoload.php';

$app = require __DIR__ . '/../../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

if (
    !app()->environment(['local', 'testing'])
    || DB::transactionLevel() !== 0
    || TemplateSuratCuti::count() !== 0
    || DB::table('surat_permohonan_cuti')->exists()
) {
    fwrite(STDERR, "Lingkungan pengujian tidak aman.\n");
    exit(1);
}

$master = User::where('role', 'master')->firstOrFail();

$rootAwal = config('filesystems.disks.local.root');
$root = storage_path('framework/testing/snapshot-template-' . Str::uuid());

config(['filesystems.disks.local.root' => $root]);
Storage::forgetDisk('local');

$disk = Storage::disk('local');

$templateService = app(TemplateSuratCutiService::class);
$logoService = app(LogoTemplateCutiService::class);
$service = app(SnapshotTemplateSuratCutiService::class);

$data = [
    'nama_template' => 'Template Snapshot Uji',
    'nama_perusahaan' => 'PDAM Tirta Pinang',
    'alamat_perusahaan' => 'Alamat Pengujian',
    'telepon_perusahaan' => '0717-123456',
    'email_perusahaan' => 'pengujian@example.com',
    'website_perusahaan' => 'https://example.com',
    'judul_surat' => 'SURAT PERMOHONAN CUTI',
    'format_nomor_surat' => '{id_pengajuan}/CUTI/{tahun}',
    'isi_pembuka' => 'Dengan hormat',
    'isi_penutup' => 'Demikian surat permohonan ini.',
    'tempat_terbit' => 'Pangkalpinang',
    'footer_surat' => 'PDAM Tirta Pinang',
];

$ids = [];
$pass = 0;
$fail = 0;
$templateTanpaLogo = null;
$templateDenganLogo = null;
$templateBaru = null;
$snapshotTanpaLogo = null;
$snapshotLogo = null;
$fileLogoAsli = null;
$pathLogo = null;

function pastikan(bool $nilai): void
{
    if (!$nilai) {
        throw new RuntimeException('Hasil tidak sesuai.');
    }
}

function ditolak(
    callable $aksi,
    string $kelas = ValidationException::class
): void {
    try {
        $aksi();
    } catch (Throwable $e) {
        if ($e instanceof $kelas) {
            return;
        }

        throw $e;
    }

    throw new RuntimeException('Operasi seharusnya ditolak.');
}

$uji = function (
    string $nama,
    callable $aksi
) use (&$pass, &$fail): void {
    try {
        $aksi();
        $pass++;
        echo "PASS - $nama\n";
    } catch (Throwable $e) {
        $fail++;
        echo "FAIL - $nama: " . $e->getMessage() . "\n";
    }
};

try {
    $uji('Template aktif tidak tersedia ditolak', function () use ($service) {
        ditolak(fn() => $service->ambilTemplateAktif());
    });

    $uji('Pembuatan template tanpa logo', function () use (
        $templateService,
        $master,
        $data,
        &$templateTanpaLogo,
        &$ids
    ) {
        $templateTanpaLogo = $templateService->buatTemplate(
            $master->id,
            $data
        );

        $ids[] = $templateTanpaLogo->id;

        pastikan(
            $templateTanpaLogo->logo_path === null
                && !$templateTanpaLogo->is_active
        );
    });

    $uji('Pengambilan template aktif', function () use (
        $templateTanpaLogo,
        $service
    ) {
        $templateTanpaLogo->update([
            'is_active' => true,
            'diaktifkan_pada' => now(),
        ]);

        $template = $service->ambilTemplateAktif();

        pastikan(
            $template->id === $templateTanpaLogo->id
                && $template->isAktif()
                && $template->kode_template === TemplateSuratCuti::KODE_PERMOHONAN_CUTI
        );
    });

    $uji('Snapshot template tanpa logo', function () use (
        $service,
        &$snapshotTanpaLogo,
        $templateTanpaLogo
    ) {
        $snapshotTanpaLogo = $service->buatSnapshotAktif();

        pastikan(
            $snapshotTanpaLogo['template_id'] === $templateTanpaLogo->id
                && $snapshotTanpaLogo['nama_template'] === $templateTanpaLogo->nama_template
                && $snapshotTanpaLogo['nama_perusahaan'] === $templateTanpaLogo->nama_perusahaan
                && $snapshotTanpaLogo['logo_path'] === null
                && $snapshotTanpaLogo['logo_metadata'] === null
        );
    });

    $uji('Struktur snapshot dan pengaturan efektif', function () use (
        $snapshotTanpaLogo
    ) {
        $wajib = [
            'template_id',
            'kode_template',
            'nama_template',
            'versi',
            'nama_perusahaan',
            'alamat_perusahaan',
            'telepon_perusahaan',
            'email_perusahaan',
            'website_perusahaan',
            'logo_path',
            'logo_metadata',
            'judul_surat',
            'format_nomor_surat',
            'isi_pembuka',
            'isi_penutup',
            'tempat_terbit',
            'footer_surat',
            'pengaturan_tampilan',
        ];

        pastikan(
            array_diff($wajib, array_keys($snapshotTanpaLogo)) === []
                && $snapshotTanpaLogo['pengaturan_tampilan']['ukuran_kertas'] === 'A4'
                && $snapshotTanpaLogo['pengaturan_tampilan']['tampilkan_qr_code'] === true
        );
    });

    $uji('Pembuatan template dengan logo bawaan', function () use (
        $templateService,
        $master,
        $data,
        &$templateTanpaLogo,
        &$templateDenganLogo,
        &$ids,
        $disk
    ) {
        $templateDenganLogo = $templateService->buatTemplate(
            $master->id,
            $data,
            'bawaan'
        );

        $ids[] = $templateDenganLogo->id;

        $templateTanpaLogo->update([
            'is_active' => false,
            'dinonaktifkan_pada' => now(),
        ]);

        $templateDenganLogo->update([
            'is_active' => true,
            'diaktifkan_pada' => now(),
        ]);

        pastikan(
            filled($templateDenganLogo->logo_path)
                && $disk->exists($templateDenganLogo->logo_path)
        );
    });

    $uji('Snapshot template dengan logo', function () use (
        $service,
        &$snapshotLogo,
        &$pathLogo,
        &$fileLogoAsli,
        $templateDenganLogo,
        $disk
    ) {
        $snapshotLogo = $service->buatSnapshotAktif();

        $pathLogo = $snapshotLogo['logo_path'];
        $fileLogoAsli = $disk->get($pathLogo);

        pastikan(
            $snapshotLogo['template_id'] === $templateDenganLogo->id
                && $snapshotLogo['logo_metadata']['file_path'] === $pathLogo
                && $snapshotLogo['logo_metadata']['mime_type'] === 'image/png'
                && $snapshotLogo['logo_metadata']['file_hash'] === hash('sha256', $fileLogoAsli)
                && $snapshotLogo['logo_metadata']['ukuran_file'] === strlen($fileLogoAsli)
                && $snapshotLogo['logo_metadata']['lebar'] > 0
                && $snapshotLogo['logo_metadata']['tinggi'] > 0
        );
    });

    $uji('Metadata logo sesuai metadata storage', function () use (
        $logoService,
        $snapshotLogo,
        $pathLogo
    ) {
        $metadata = $logoService->metadata($pathLogo);

        foreach (
            [
                'file_path',
                'file_hash',
                'mime_type',
                'ukuran_file',
                'lebar',
                'tinggi',
            ] as $field
        ) {
            pastikan(
                $snapshotLogo['logo_metadata'][$field] === $metadata[$field]
            );
        }
    });

    $uji('Pembacaan logo historis', function () use (
        $service,
        $snapshotLogo,
        $fileLogoAsli
    ) {
        $isi = $service->bacaLogoSnapshot($snapshotLogo);

        pastikan(
            is_string($isi)
                && hash_equals(
                    hash('sha256', $fileLogoAsli),
                    hash('sha256', $isi)
                )
        );
    });

    $uji('Konversi logo menjadi Data URI', function () use (
        $service,
        $snapshotLogo,
        $fileLogoAsli
    ) {
        $dataUri = $service->logoDataUriSnapshot($snapshotLogo);

        pastikan(
            str_starts_with($dataUri, 'data:image/png;base64,')
                && base64_decode(
                    substr($dataUri, strlen('data:image/png;base64,')),
                    true
                ) === $fileLogoAsli
        );
    });

    $uji('Snapshot tanpa logo menghasilkan null', function () use (
        $service,
        $snapshotTanpaLogo
    ) {
        pastikan(
            $service->bacaLogoSnapshot($snapshotTanpaLogo) === null
                && $service->logoDataUriSnapshot($snapshotTanpaLogo) === null
        );
    });

    $uji('Logo hilang ditolak', function () use (
        $disk,
        $pathLogo,
        $service,
        $snapshotLogo,
        $fileLogoAsli
    ) {
        pastikan($disk->delete($pathLogo));

        try {
            ditolak(fn() => $service->bacaLogoSnapshot($snapshotLogo));
        } finally {
            pastikan($disk->put($pathLogo, $fileLogoAsli));
        }
    });

    $uji('Logo rusak ditolak', function () use (
        $disk,
        $pathLogo,
        $service,
        $snapshotLogo,
        $fileLogoAsli
    ) {
        pastikan($disk->put($pathLogo, 'file-logo-rusak'));

        try {
            ditolak(fn() => $service->bacaLogoSnapshot($snapshotLogo));
        } finally {
            pastikan($disk->put($pathLogo, $fileLogoAsli));
        }
    });

    $uji('Perubahan hash logo ditolak', function () use (
        $disk,
        $pathLogo,
        $service,
        $snapshotLogo,
        $fileLogoAsli,
        $logoService
    ) {
        $logoPengganti = $logoService->dariAsetBawaan(
            'img/logo.png'
        )['isi_file'];

        pastikan($disk->put($pathLogo, $logoPengganti));

        try {
            ditolak(
                fn() => $service->bacaLogoSnapshot($snapshotLogo),
                RuntimeException::class
            );
        } finally {
            pastikan($disk->put($pathLogo, $fileLogoAsli));
        }
    });

    $uji('Metadata snapshot yang dimanipulasi ditolak', function () use (
        $service,
        $snapshotLogo
    ) {
        $snapshotRusak = $snapshotLogo;
        $snapshotRusak['logo_metadata']['file_hash'] = str_repeat('a', 64);

        ditolak(
            fn() => $service->bacaLogoSnapshot($snapshotRusak),
            RuntimeException::class
        );
    });


    $uji('Path logo snapshot tidak valid ditolak', function () use (
        $service,
        $snapshotLogo
    ) {
        $snapshotRusak = $snapshotLogo;
        $snapshotRusak['logo_path'] = '../../logo.png';
        $snapshotRusak['logo_metadata']['file_path'] = '../../logo.png';

        ditolak(
            fn() => $service->bacaLogoSnapshot($snapshotRusak),
            ValidationException::class
        );
    });


    $uji('Snapshot lama tetap valid setelah template diganti', function () use (
        $templateService,
        $master,
        $data,
        $templateDenganLogo,
        $service,
        $snapshotLogo,
        $fileLogoAsli,
        &$templateBaru,
        &$ids,
        $disk
    ) {
        $templateBaru = $templateService->buatTemplate(
            $master->id,
            $data
        );

        $ids[] = $templateBaru->id;

        $templateDenganLogo->update([
            'is_active' => false,
            'dinonaktifkan_pada' => now(),
        ]);

        $templateBaru->update([
            'is_active' => true,
            'diaktifkan_pada' => now(),
        ]);

        $snapshotBaru = $service->buatSnapshotAktif();

        $logoLama = $service->bacaLogoSnapshot($snapshotLogo);

        pastikan(
            $snapshotBaru['template_id'] === $templateBaru->id
                && $snapshotBaru['logo_path'] === null
                && $snapshotLogo['template_id'] === $templateDenganLogo->id
                && $logoLama === $fileLogoAsli
                && $disk->exists($snapshotLogo['logo_path'])
        );
    });

    $uji('Pembacaan snapshot tidak mengubah metadata database', function () use (
        $templateDenganLogo,
        $service,
        $snapshotLogo
    ) {
        $sebelum = $templateDenganLogo->fresh()->getAttributes();

        $service->bacaLogoSnapshot($snapshotLogo);
        $service->logoDataUriSnapshot($snapshotLogo);

        $sesudah = $templateDenganLogo->fresh()->getAttributes();

        pastikan($sebelum === $sesudah);
    });

    $uji('Snapshot dapat diserialisasi menjadi JSON', function () use (
        $snapshotTanpaLogo,
        $snapshotLogo
    ) {
        $jsonTanpaLogo = json_encode(
            $snapshotTanpaLogo,
            JSON_THROW_ON_ERROR
        );

        $jsonLogo = json_encode(
            $snapshotLogo,
            JSON_THROW_ON_ERROR
        );

        pastikan(
            is_string($jsonTanpaLogo)
                && is_string($jsonLogo)
                && json_decode($jsonLogo, true, 512, JSON_THROW_ON_ERROR)['logo_metadata']['file_hash']
                === $snapshotLogo['logo_metadata']['file_hash']
        );
    });

    $uji('Transaksi database tetap bersih', function () {
        pastikan(DB::transactionLevel() === 0);
    });
} finally {
    $uji('Pembersihan data dan storage', function () use (
        $ids,
        $disk,
        $root
    ) {
        TemplateSuratCuti::whereIn('id', $ids)->delete();

        pastikan(
            TemplateSuratCuti::count() === 0
                && DB::transactionLevel() === 0
        );

        if ($disk->exists('cuti')) {
            pastikan($disk->deleteDirectory('cuti'));
        }

        if (is_dir($root)) {
            pastikan(rmdir($root));
        }

        pastikan(!is_dir($root));
    });

    config(['filesystems.disks.local.root' => $rootAwal]);
    Storage::forgetDisk('local');
}

echo "\nTotal pengujian : " . ($pass + $fail) . "\n";
echo "PASS            : $pass\n";
echo "FAIL            : $fail\n";

exit($fail === 0 ? 0 : 1);