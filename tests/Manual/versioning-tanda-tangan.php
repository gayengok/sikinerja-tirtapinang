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
    fwrite(STDERR, "Pengujian hanya untuk database lokal/testing dengan tabel tanda tangan kosong.\n");
    exit(1);
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

function buatPng(int $geser = 0): string
{
    $gambar = imagecreatetruecolor(600, 250);
    imagealphablending($gambar, false);
    imagesavealpha($gambar, true);
    imagefill($gambar, 0, 0, imagecolorallocatealpha($gambar, 255, 255, 255, 127));
    $hitam = imagecolorallocate($gambar, 0, 0, 0);
    imagesetthickness($gambar, 5);
    imageline($gambar, 50, 180, 150, 50 + $geser, $hitam);
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

function keadaan($disk): array
{
    $files = [];
    foreach ($disk->allFiles() as $path) {
        $files[$path] = hash('sha256', $disk->get($path));
    }
    ksort($files);
    return [TandaTanganPengguna::orderBy('id')->get()->map(fn ($t) => $t->getAttributes())->all(), $files, DB::transactionLevel()];
}

function tanpaPerubahan($disk, callable $aksi, string $kelas, string $pesan): void
{
    $awal = keadaan($disk);
    ditolak($aksi, $kelas, $pesan);
    pastikan(keadaan($disk) === $awal, 'Database atau file berubah.');
}

function urlPng(int $geser): string
{
    return 'data:image/png;base64,' . base64_encode(buatPng($geser));
}

function simpanId($ttd, array &$ids)
{
    $ids[] = $ttd->id;
    return $ttd;
}

$v1 = $v2 = $v3 = $v4 = $lain = null;
$awalV1 = null;
echo "PENGUJIAN VERSIONING TANDA TANGAN\n\n";
try {
    $uji('Penggantian melalui canvas', function () use ($service, $url, &$v1, &$v2, &$ids, &$awalV1) {
        $v1 = simpanId($service->simpanDariCanvas(6, $url), $ids);
        $awalV1 = $v1->getAttributes();
        $v2 = simpanId($service->gantiDariCanvas(6, urlPng(20)), $ids);
        pastikan($v2->exists && $v2->metode === 'canvas' && $v2->id !== $v1->id);
    });
    $uji('Penggantian melalui upload', function () use ($service, &$v3, &$ids) {
        $tmp = tempnam(sys_get_temp_dir(), 'ttd_');
        pastikan($tmp !== false);
        try {
            $png = buatPng(40);
            pastikan(file_put_contents($tmp, $png) === strlen($png));
            $v3 = simpanId($service->gantiDariUpload(6, new UploadedFile($tmp, 'uji.png', 'image/png', UPLOAD_ERR_OK, true)), $ids);
            pastikan($v3->exists && $v3->metode === 'upload');
        } finally { unlink($tmp); }
    });
    $uji('Versi berurutan', function () {
        pastikan(TandaTanganPengguna::pengguna(6)->orderBy('versi')->pluck('versi')->map(fn ($v) => (int) $v)->all() === [1, 2, 3]);
    });
    $uji('Versi sebelumnya nonaktif', function () use (&$v1, &$v2, &$v3) {
        foreach ([$v1, $v2] as $t) {
            pastikan($t && !$t->refresh()->is_active && $t->dinonaktifkan_pada !== null && $t->active_user_id === null);
        }
        pastikan($v3 && $v3->refresh()->is_active && $v3->dinonaktifkan_pada === null);
    });
    $uji('Hanya satu versi aktif', function () use (&$v3) {
        pastikan($v3 && TandaTanganPengguna::pengguna(6)->aktif()->sole()->id === $v3->id && (int) $v3->active_user_id === 6);
    });
    $uji('File historis tetap tersedia', function () use ($disk) {
        $rows = TandaTanganPengguna::pengguna(6)->get();
        pastikan($rows->count() === 3 && $rows->pluck('file_path')->unique()->count() === 3);
        foreach ($rows as $t) pastikan($disk->exists($t->file_path));
    });
    $uji('Hash seluruh versi valid', function () use ($disk, &$v1, &$awalV1) {
        pastikan($v1 && $awalV1 && $v1->refresh()->file_hash === $awalV1['file_hash']);
        foreach (TandaTanganPengguna::pengguna(6)->get() as $t) {
            $isi = $disk->get($t->file_path);
            pastikan(is_string($isi) && hash('sha256', $isi) === $t->file_hash && strlen($isi) === $t->ukuran_file);
        }
    });
    $uji('Metadata historis dipertahankan', function () use (&$v1, &$awalV1) {
        pastikan($v1 && $awalV1);
        $baru = $v1->refresh()->getAttributes();
        foreach (['is_active', 'active_user_id', 'dinonaktifkan_pada', 'updated_at'] as $k) { unset($baru[$k], $awalV1[$k]); }
        pastikan($baru === $awalV1);
    });
    $uji('Penggantian tanpa riwayat ditolak', fn () => tanpaPerubahan($disk, fn () => $service->gantiDariCanvas(8, $url), ValidationException::class, 'belum memiliki riwayat'));
    $uji('Penggantian ketika seluruh versi nonaktif', function () use ($service, $url, &$v3, &$v4, &$ids) {
        pastikan($v3 && $v3->refresh()->is_active);
        pastikan($v3->update(['is_active' => false, 'dinonaktifkan_pada' => now()]));
        pastikan(TandaTanganPengguna::pengguna(6)->aktif()->count() === 0);
        $v4 = simpanId($service->gantiDariCanvas(6, $url), $ids);
        pastikan($v4->versi === 4 && $v4->is_active && TandaTanganPengguna::pengguna(6)->aktif()->count() === 1);
    });
    $uji('Isolasi antar pengguna', function () use ($service, $url, $disk, &$lain, &$ids) {
        $awal = TandaTanganPengguna::pengguna(6)->orderBy('id')->get()->map(fn ($t) => $t->getAttributes())->all();
        $lain = simpanId($service->simpanDariCanvas(7, $url), $ids);
        $lain = simpanId($service->gantiDariCanvas(7, $url), $ids);
        pastikan($lain->versi === 2 && $lain->is_active && TandaTanganPengguna::pengguna(7)->aktif()->count() === 1);
        pastikan($awal === TandaTanganPengguna::pengguna(6)->orderBy('id')->get()->map(fn ($t) => $t->getAttributes())->all());
    });
    $uji('Master ditolak', fn () => tanpaPerubahan($disk, fn () => $service->gantiDariCanvas(1, $url), ValidationException::class, 'Pengguna tidak berhak'));
    $uji('Gambar tidak valid ditolak', fn () => tanpaPerubahan($disk, fn () => $service->gantiDariCanvas(6, 'data:image/png;base64,@@@@'), ValidationException::class, 'Encoding Base64 tidak valid'));
    $uji('Kegagalan filesystem mempertahankan versi lama', function () use ($service, $url, $disk, $root) {
        $dir = $root . '/cuti/tanda-tangan/6';
        $cadangan = $root . '/cadangan-6';
        $awal = keadaan($disk);
        pastikan(is_dir($dir) && !file_exists($cadangan) && rename($dir, $cadangan));
        try {
            pastikan(file_put_contents($dir, 'blokir direktori') !== false);
            $gagal = false;
            try { $service->gantiDariCanvas(6, $url); }
            catch (\League\Flysystem\UnableToCreateDirectory | \League\Flysystem\UnableToWriteFile $e) { $gagal = true; }
            catch (RuntimeException $e) { pastikan($e->getMessage() === 'Gagal menyimpan file tanda tangan.'); $gagal = true; }
            pastikan($gagal && file_get_contents($dir) === 'blokir direktori');
        } finally {
            if (is_file($dir)) unlink($dir);
            pastikan(rename($cadangan, $dir));
        }
        pastikan(keadaan($disk) === $awal);
    });
    foreach (['updating' => ['Kegagalan penonaktifan versi lama', 'Gagal menonaktifkan tanda tangan sebelumnya'], 'creating' => ['Kegagalan metadata versi baru', 'Gagal menyimpan metadata tanda tangan']] as $event => [$nama, $pesan]) {
        $uji($nama, function () use ($service, $url, $disk, $event, $pesan) {
            $dispatcher = TandaTanganPengguna::getEventDispatcher();
            TandaTanganPengguna::setEventDispatcher(clone $dispatcher);
            try {
                TandaTanganPengguna::$event(fn () => false);
                tanpaPerubahan($disk, fn () => $service->gantiDariCanvas(6, $url), RuntimeException::class, $pesan);
            } finally { TandaTanganPengguna::setEventDispatcher($dispatcher); }
        });
    }
    $uji('Transaksi luar ditolak', function () use ($service, $url, $disk) {
        DB::beginTransaction();
        try { tanpaPerubahan($disk, fn () => $service->gantiDariCanvas(6, $url), LogicException::class, 'wajib memiliki transaksi sendiri'); }
        finally { DB::rollBack(); }
    });
} finally {
    $uji('Pembersihan seluruh data pengujian', function () use (&$ids, $disk, $root) {
        TandaTanganPengguna::whereIn('id', $ids)->delete();
        pastikan(TandaTanganPengguna::count() === 0, 'Ada record tersisa; periksa manual.');
        $disk->deleteDirectory('cuti');
        pastikan($disk->allFiles() === []);
        if (is_dir($root)) pastikan(rmdir($root));
        pastikan(DB::transactionLevel() === 0);
    });
    config(['filesystems.disks.local' => $asli]);
    Storage::forgetDisk('local');
}
echo "\nTotal pengujian : " . ($pass + $fail) . "\nPASS            : $pass\nFAIL            : $fail\n";
exit($fail === 0 ? 0 : 1);
