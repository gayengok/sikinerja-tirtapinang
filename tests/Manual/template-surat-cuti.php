<?php

use App\Models\TemplateSuratCuti;
use App\Models\User;
use App\Services\Cuti\TemplateSuratCutiService;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

require __DIR__ . '/../../vendor/autoload.php';
$app = require __DIR__ . '/../../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

if (!app()->environment(['local', 'testing']) || DB::transactionLevel() !== 0 || TemplateSuratCuti::count() !== 0 || DB::table('surat_permohonan_cuti')->exists()) {
    fwrite(STDERR, "Lingkungan pengujian tidak aman.\n");
    exit(1);
}

$master = User::where('role', 'master')->firstOrFail();
$nonmaster = User::where('role', '!=', 'master')->firstOrFail();
$service = app(TemplateSuratCutiService::class);
$rootAwal = config('filesystems.disks.local.root');
$root = storage_path('framework/testing/template-' . Str::uuid());
config(['filesystems.disks.local.root' => $root]);
Storage::forgetDisk('local');
$disk = Storage::disk('local');
$data = [
    'nama_template' => 'Template Uji',
    'nama_perusahaan' => 'PDAM Uji',
    'format_nomor_surat' => '{id_pengajuan}/CUTI/{tahun}',
    'isi_pembuka' => 'Dengan hormat',
    'isi_penutup' => 'Terima kasih',
];
$ids = [];
$pass = 0;
$fail = 0;
function pastikan(bool $nilai): void
{
    if (!$nilai) {
        throw new RuntimeException('Hasil tidak sesuai.');
    }
}
function ditolak(callable $aksi, string $kelas = ValidationException::class): void
{
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
$uji = function (string $nama, callable $aksi) use (&$pass, &$fail): void {
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
    $uji('Master membuat template tanpa logo', function () use ($service, $master, $data, &$ids, &$a) {
        $a = $service->buatTemplate($master->id, $data);
        $ids[] = $a->id;
        pastikan($a->versi === 1 && !$a->is_active && $a->logo_path === null && (int)$a->dibuat_oleh === $master->id);
    });
    $uji('Nonmaster ditolak', fn() => ditolak(fn() => $service->buatTemplate($nonmaster->id, $data, 'bawaan')));
    $uji('Field sistem ditolak', fn() => ditolak(fn() => $service->buatTemplate($master->id, $data + ['versi' => 99])));
    $uji('Template kedua dengan logo bawaan', function () use ($service, $master, $data, $disk, &$ids, &$b) {
        $b = $service->buatTemplate($master->id, $data, 'bawaan');
        $ids[] = $b->id;
        pastikan($b->versi === 2 && !$b->is_active && $disk->exists($b->logo_path));
    });
    $uji('Pembaruan mempertahankan logo dan versi', function () use ($service, $master, $data, &$b) {
        $lama = $b->logo_path;
        $b = $service->ubahTemplate($master->id, $b->id, array_replace($data, ['nama_template' => 'Diubah']));
        pastikan($b->nama_template === 'Diubah' && $b->versi === 2 && $b->logo_path === $lama);
    });
    $uji('Upload mengganti logo tanpa menghapus lama', function () use ($service, $master, $data, $disk, &$b) {
        $lama = $b->logo_path;
        $file = new UploadedFile(public_path('img/logo-pdf.jpg'), 'logo.jpg', 'image/jpeg', null, true);
        $b = $service->ubahTemplate($master->id, $b->id, $data, 'upload', $file);
        pastikan($b->logo_path !== $lama && $disk->exists($lama) && $disk->exists($b->logo_path));
    });
    $uji('Menghapus referensi logo tanpa menghapus file', function () use ($service, $master, $data, $disk, &$b) {
        $lama = $b->logo_path;
        $b = $service->ubahTemplate($master->id, $b->id, $data, 'tanpa_logo');
        pastikan($b->logo_path === null && $disk->exists($lama));
    });
    $uji('Template aktif dan pernah aktif dilindungi', function () use ($service, $master, $data, &$a) {
        $a->update(['is_active' => true, 'diaktifkan_pada' => now()]);
        try {
            ditolak(fn() => $service->ubahTemplate($master->id, $a->id, $data));
        } finally {
            $a->update(['is_active' => false]);
        }
        ditolak(fn() => $service->ubahTemplate($master->id, $a->id, $data));
    });
    $uji('Tindakan logo tidak valid ditolak', function () use ($service, $master, $data) {
        foreach (['tetap', 'upload', 'lain'] as $aksi) {
            ditolak(fn() => $service->buatTemplate($master->id, $data, $aksi));
        }
    });
    $uji('Transaksi luar ditolak', function () use ($service, $master, $data) {
        DB::beginTransaction();
        try {
            ditolak(fn() => $service->buatTemplate($master->id, $data), LogicException::class);
        } finally {
            DB::rollBack();
        }
    });
    $uji('Gagal create mengembalikan database dan file', function () use ($service, $master, $data, $disk) {
        $awal = $disk->allFiles();
        $jumlah = TemplateSuratCuti::count();
        $dispatcher = TemplateSuratCuti::getEventDispatcher();
        TemplateSuratCuti::setEventDispatcher(clone $dispatcher);
        TemplateSuratCuti::creating(fn() => false);
        try {
            ditolak(fn() => $service->buatTemplate($master->id, $data, 'bawaan'), RuntimeException::class);
        } finally {
            TemplateSuratCuti::setEventDispatcher($dispatcher);
        }
        pastikan($awal === $disk->allFiles() && $jumlah === TemplateSuratCuti::count() && DB::transactionLevel() === 0);
    });
    $uji('Gagal update mempertahankan metadata dan file', function () use ($service, $master, $data, $disk, &$b) {
        $awal = $disk->allFiles();
        $metadata = $b->fresh()->getAttributes();
        $dispatcher = TemplateSuratCuti::getEventDispatcher();
        TemplateSuratCuti::setEventDispatcher(clone $dispatcher);
        TemplateSuratCuti::updating(fn() => false);
        try {
            ditolak(fn() => $service->ubahTemplate($master->id, $b->id, $data, 'bawaan'), RuntimeException::class);
        } finally {
            TemplateSuratCuti::setEventDispatcher($dispatcher);
        }
        pastikan($awal === $disk->allFiles() && $metadata === $b->fresh()->getAttributes() && DB::transactionLevel() === 0);
    });
} finally {
    $uji('Pembersihan data dan storage', function () use ($ids, $disk, $root) {
        TemplateSuratCuti::whereIn('id', $ids)->delete();
        pastikan(TemplateSuratCuti::count() === 0 && DB::transactionLevel() === 0);
        pastikan($disk->deleteDirectory('cuti'));
        if (is_dir($root)) {
            pastikan(rmdir($root));
        }
    });
    config(['filesystems.disks.local.root' => $rootAwal]);
    Storage::forgetDisk('local');
}
echo "PASS : $pass\nFAIL : $fail\n";
exit($fail === 0 ? 0 : 1);