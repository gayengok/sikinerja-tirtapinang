<?php

use App\Models\TandaTanganPengguna;
use App\Services\Cuti\TandaTanganPenggunaService;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

require __DIR__ . '/../../vendor/autoload.php';
$app = require __DIR__ . '/../../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

if (!$app->environment(['local', 'testing']) || DB::transactionLevel() !== 0 || TandaTanganPengguna::count() !== 0) {
    exit("Pengujian hanya untuk database lokal/testing dengan tabel tanda tangan kosong.\n");
}

function pastikan(bool $kondisi, string $pesan = 'Kondisi tidak terpenuhi.'): void
{
    if (!$kondisi) {
        throw new RuntimeException($pesan);
    }
}

function ditolak(callable $aksi, string $kelas, string $pesan): void
{
    try {
        $aksi();
    } catch (Throwable $e) {
        pastikan($e instanceof $kelas && str_contains($e->getMessage(), $pesan), get_class($e) . ': ' . $e->getMessage());
        return;
    }
    throw new RuntimeException('Exception yang diharapkan tidak terjadi.');
}

function buatPng(): string
{
    $gambar = imagecreatetruecolor(600, 250);
    imagealphablending($gambar, false);
    imagesavealpha($gambar, true);
    imagefill($gambar, 0, 0, imagecolorallocatealpha($gambar, 255, 255, 255, 127));
    $hitam = imagecolorallocate($gambar, 0, 0, 0);
    imagesetthickness($gambar, 5);
    imageline($gambar, 50, 180, 150, 50, $hitam);
    imageline($gambar, 150, 50, 250, 180, $hitam);
    imageline($gambar, 100, 200, 500, 200, $hitam);
    ob_start();
    try {
        pastikan(imagepng($gambar));
        return ob_get_contents();
    } finally {
        ob_end_clean();
        imagedestroy($gambar);
    }
}

$service = app(TandaTanganPenggunaService::class);
foreach ([6, 7, 8] as $id) {
    $service->validasiPengguna($id);
}
$asli = config('filesystems.disks.local');
$root = storage_path('framework/testing/ttd-' . Str::uuid());
config(['filesystems.disks.local' => ['driver' => 'local', 'root' => $root, 'throw' => false]]);
Storage::forgetDisk('local');
$disk = Storage::disk('local');
$png = buatPng();
$url = 'data:image/png;base64,' . base64_encode($png);
$pass = 0;
$fail = 0;
$canvas = null;
$upload = null;
$ids = [];
$uji = function (string $nama, callable $aksi) use (&$pass, &$fail): void {
    try {
        $aksi();
        $pass++;
        echo "[PASS] $nama\n";
    } catch (Throwable $e) {
        $fail++;
        echo "[FAIL] $nama: {$e->getMessage()}\n";
    }
};
echo "PENGUJIAN PENYIMPANAN TANDA TANGAN\n\n";
try {
    $uji('Penyimpanan canvas', function () use ($service, $url, &$canvas, &$ids) {
        $canvas = $service->simpanDariCanvas(6, $url);
        $ids[] = $canvas->id;
        pastikan($canvas->exists && $canvas->metode === 'canvas' && (int) $canvas->user_id === 6);
    });
    $uji('Penyimpanan upload PNG', function () use ($service, $png, &$upload, &$ids) {
        $tmp = tempnam(sys_get_temp_dir(), 'ttd_');
        try {
            pastikan(file_put_contents($tmp, $png) === strlen($png));
            $upload = $service->simpanDariUpload(7, new UploadedFile($tmp, 'uji.png', 'image/png', UPLOAD_ERR_OK, true));
            $ids[] = $upload->id;
            pastikan($upload->exists && $upload->metode === 'upload' && (int) $upload->user_id === 7);
        } finally {
            unlink($tmp);
        }
    });
    $uji('Versi pertama', function () use (&$canvas, &$upload) {
        pastikan($canvas && $upload && $canvas->versi === 1 && $upload->versi === 1);
    });
    $uji('Aktivasi pertama', function () use (&$canvas, &$upload) {
        foreach ([$canvas, $upload] as $ttd) {
            pastikan($ttd && $ttd->isAktif() && (int) $ttd->active_user_id === (int) $ttd->user_id && $ttd->diaktifkan_pada !== null && $ttd->dinonaktifkan_pada === null);
        }
    });
    $uji('File berada pada storage pengujian privat', function () use ($disk, &$canvas, &$upload) {
        foreach ([$canvas, $upload] as $ttd) {
            pastikan($ttd && str_starts_with($ttd->file_path, 'cuti/tanda-tangan/' . $ttd->user_id . '/v1-') && $disk->exists($ttd->file_path));
        }
    });
    $uji('Hash file sesuai database', function () use ($disk, &$canvas, &$upload) {
        foreach ([$canvas, $upload] as $ttd) {
            pastikan($ttd && hash('sha256', $disk->get($ttd->file_path)) === $ttd->file_hash);
        }
    });
    $uji('Ukuran dimensi dan MIME sesuai', function () use ($disk, &$canvas, &$upload) {
        foreach ([$canvas, $upload] as $ttd) {
            pastikan($ttd !== null);
            $isi = $disk->get($ttd->file_path);
            $info = getimagesizefromstring($isi);
            pastikan(strlen($isi) === $ttd->ukuran_file && $info[0] === $ttd->lebar && $info[1] === $ttd->tinggi && $info['mime'] === $ttd->mime_type && $ttd->lebar === 600 && $ttd->tinggi === 250);
        }
    });
    $uji('Master ditolak tanpa efek samping', function () use ($service, $url, $disk) {
        $files = $disk->allFiles();
        $count = TandaTanganPengguna::count();
        ditolak(fn() => $service->simpanDariCanvas(1, $url), ValidationException::class, 'Pengguna tidak berhak');
        pastikan($files === $disk->allFiles() && $count === TandaTanganPengguna::count());
    });
    $uji('Penyimpanan pertama berulang ditolak', function () use ($service, $url, $disk) {
        $files = $disk->allFiles();
        ditolak(fn() => $service->simpanDariCanvas(6, $url), ValidationException::class, 'sudah memiliki riwayat');
        pastikan($files === $disk->allFiles() && TandaTanganPengguna::pengguna(6)->count() === 1);
    });
    $uji('Kegagalan filesystem tidak membuat record', function () use ($service, $url, $disk, $root) {
        $blok = $root . '/cuti/tanda-tangan/8';
        pastikan(!file_exists($blok));
        pastikan(file_put_contents($blok, 'blokir direktori') !== false);
        try {
            $files = $disk->allFiles();
            $count = TandaTanganPengguna::count();
            $gagal = false;
            try {
                $service->simpanDariCanvas(8, $url);
            } catch (\League\Flysystem\UnableToCreateDirectory | \League\Flysystem\UnableToWriteFile $e) {
                $gagal = true;
            } catch (RuntimeException $e) {
                pastikan($e->getMessage() === 'Gagal menyimpan file tanda tangan.', get_class($e) . ': ' . $e->getMessage());
                $gagal = true;
            }
            pastikan($gagal, 'Penyimpanan seharusnya gagal.');
            pastikan(TandaTanganPengguna::pengguna(8)->count() === 0 && TandaTanganPengguna::count() === $count && DB::transactionLevel() === 0);
            pastikan($files === $disk->allFiles(), 'Daftar file berubah setelah kegagalan filesystem.');
            pastikan(file_get_contents($blok) === 'blokir direktori', 'File pemblokir berubah.');
        } finally {
            unlink($blok);
        }
    });
    $uji('Rollback database membersihkan file baru', function () use ($service, $url, $disk) {
        $dispatcher = TandaTanganPengguna::getEventDispatcher();
        $files = $disk->allFiles();
        TandaTanganPengguna::setEventDispatcher(clone $dispatcher);
        try {
            TandaTanganPengguna::creating(fn() => false);
            ditolak(fn() => $service->simpanDariCanvas(8, $url), RuntimeException::class, 'Gagal menyimpan metadata');
            pastikan(TandaTanganPengguna::pengguna(8)->count() === 0 && $files === $disk->allFiles() && DB::transactionLevel() === 0);
        } finally {
            TandaTanganPengguna::setEventDispatcher($dispatcher);
        }
    });
    $uji('Transaksi luar ditolak', function () use ($service, $url, $disk) {
        $files = $disk->allFiles();
        DB::beginTransaction();
        try {
            ditolak(fn() => $service->simpanDariCanvas(8, $url), LogicException::class, 'wajib memiliki transaksi sendiri');
            pastikan(DB::transactionLevel() === 1 && $files === $disk->allFiles());
        } finally {
            DB::rollBack();
        }
    });
} finally {
    $uji('Pembersihan record dan storage pengujian', function () use (&$ids, $disk, $root) {
        TandaTanganPengguna::whereIn('id', $ids)->delete();
        pastikan(TandaTanganPengguna::count() === 0, 'Ada record tersisa; periksa manual.');
        $disk->deleteDirectory('cuti');
        pastikan($disk->allFiles() === []);
        if (is_dir($root)) {
            pastikan(rmdir($root));
        }
        pastikan(DB::transactionLevel() === 0);
    });
    config(['filesystems.disks.local' => $asli]);
    Storage::forgetDisk('local');
}
echo "\nTotal pengujian : " . ($pass + $fail) . "\nPASS            : $pass\nFAIL            : $fail\n";
exit($fail === 0 ? 0 : 1);