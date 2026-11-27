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

$v1 = $v2 = $v3 = $lain = null;
echo "PENGUJIAN AKTIVASI TANDA TANGAN\n\n";
try {
    $v1 = simpanId($service->simpanDariCanvas(6, $url), $ids);
    $v2 = simpanId($service->gantiDariCanvas(6, urlPng(20)), $ids);
    $v3 = simpanId($service->gantiDariCanvas(6, urlPng(40)), $ids);
    $lain = simpanId($service->simpanDariCanvas(7, $url), $ids);
    $awal = keadaan($disk);
    $uji('Aktivasi ulang versi historis', function () use ($service, $v1) {
        pastikan($service->aktifkan(6, $v1->id)->is_active);
    });
    $uji('Penonaktifan versi sebelumnya otomatis', function () use ($v1, $v3) {
        pastikan(!$v3->refresh()->is_active && $v3->dinonaktifkan_pada !== null);
        pastikan(TandaTanganPengguna::pengguna(6)->aktif()->sole()->id === $v1->id);
    });
    $uji('Aktivasi ketika tidak ada versi aktif', function () use ($service, $v1, $v2) {
        $service->nonaktifkan(6, $v1->id);
        pastikan(TandaTanganPengguna::pengguna(6)->aktif()->count() === 0);
        pastikan($service->aktifkan(6, $v2->id)->is_active);
    });
    $uji('Aktivasi berulang idempotent', function () use ($service, $v2, $disk) {
        $a = keadaan($disk); $service->aktifkan(6, $v2->id); pastikan(keadaan($disk) === $a);
    });
    $uji('Penonaktifan versi aktif', function () use ($service, $v2) {
        pastikan(!$service->nonaktifkan(6, $v2->id)->is_active && TandaTanganPengguna::pengguna(6)->aktif()->count() === 0);
    });
    $uji('Penonaktifan berulang idempotent', function () use ($service, $v2, $disk) {
        $a = keadaan($disk); $service->nonaktifkan(6, $v2->id); pastikan(keadaan($disk) === $a);
    });
    $uji('Timestamp aktivasi', function () use ($service, $v1) {
        $t = $service->aktifkan(6, $v1->id); pastikan($t->diaktifkan_pada !== null && $t->dinonaktifkan_pada === null && abs($t->diaktifkan_pada->timestamp - now()->timestamp) <= 5);
    });
    $uji('Timestamp penonaktifan', function () use ($service, $v1) {
        $a = $v1->refresh()->diaktifkan_pada->timestamp; $t = $service->nonaktifkan(6, $v1->id); pastikan($t->diaktifkan_pada->timestamp === $a && abs($t->dinonaktifkan_pada->timestamp - now()->timestamp) <= 5);
    });
    $uji('Jumlah versi dan file tidak berubah', function () use ($disk, $awal) {
        $a = keadaan($disk); pastikan(count($a[0]) === count($awal[0]) && $a[1] === $awal[1]);
    });
    $uji('Integritas metadata historis', function () use ($disk, $awal) {
        $a = keadaan($disk); $b = $awal;
        foreach ([$a[0], $b[0]] as $rows) pastikan(count($rows) === 4);
        foreach (['is_active','active_user_id','diaktifkan_pada','dinonaktifkan_pada','updated_at'] as $k) {
            foreach ($a[0] as &$r) unset($r[$k]); unset($r);
            foreach ($b[0] as &$r) unset($r[$k]); unset($r);
        }
        pastikan($a === $b);
    });
    $uji('Aktivasi pengguna lain ditolak', fn () => tanpaPerubahan($disk, fn () => $service->aktifkan(6, $lain->id), ValidationException::class, 'Tanda tangan tidak ditemukan'));
    $uji('Penonaktifan pengguna lain ditolak', fn () => tanpaPerubahan($disk, fn () => $service->nonaktifkan(6, $lain->id), ValidationException::class, 'Tanda tangan tidak ditemukan'));
    $uji('Record tidak tersedia ditolak', function () use ($disk, $service) {
        tanpaPerubahan($disk, fn () => $service->aktifkan(6, 0), ValidationException::class, 'Tanda tangan tidak ditemukan');
        tanpaPerubahan($disk, fn () => $service->nonaktifkan(6, 0), ValidationException::class, 'Tanda tangan tidak ditemukan');
    });
    $uji('Master ditolak', fn () => tanpaPerubahan($disk, fn () => $service->aktifkan(1, $v1->id), ValidationException::class, 'Pengguna tidak berhak'));
    $uji('Pegawai nonaktif ditolak', function () use ($disk, $service, $v1) {
        DB::beginTransaction();
        try {
            pastikan(\App\Models\Pegawai::where('user_id', 6)->update(['status' => 'nonaktif']) === 1);
            tanpaPerubahan($disk, fn () => $service->aktifkan(6, $v1->id), ValidationException::class, 'Pengguna tidak berhak');
        } finally { DB::rollBack(); }
    });
    $service->aktifkan(6, $v3->id);
    $uji('Aktivasi file hilang ditolak', function () use ($disk, $service, $v1) {
        $isi = $disk->get($v1->file_path); pastikan($disk->delete($v1->file_path));
        try { tanpaPerubahan($disk, fn () => $service->aktifkan(6, $v1->id), ValidationException::class, 'File tanda tangan hilang'); }
        finally { pastikan($disk->put($v1->file_path, $isi)); }
    });
    $uji('Aktivasi file rusak ditolak', function () use ($disk, $service, $v1) {
        $isi = $disk->get($v1->file_path); pastikan($disk->put($v1->file_path, str_repeat('x', strlen($isi))));
        try { tanpaPerubahan($disk, fn () => $service->aktifkan(6, $v1->id), ValidationException::class, 'Integritas file'); }
        finally { pastikan($disk->put($v1->file_path, $isi)); }
    });
    $uji('Penonaktifan file rusak diperbolehkan', function () use ($disk, $service, $v3) {
        $isi = $disk->get($v3->file_path); pastikan($disk->put($v3->file_path, 'rusak'));
        try { pastikan(!$service->nonaktifkan(6, $v3->id)->is_active); }
        finally { pastikan($disk->put($v3->file_path, $isi)); }
    });
    $service->aktifkan(6, $v3->id);
    foreach ([[$v3, 'Rollback kegagalan penonaktifan sebelumnya', 'Gagal menonaktifkan tanda tangan sebelumnya'], [$v1, 'Rollback kegagalan aktivasi tujuan', 'Gagal mengubah status tanda tangan']] as [$target, $nama, $pesan]) {
        $uji($nama, function () use ($disk, $service, $v1, $target, $pesan) {
            $dispatcher = TandaTanganPengguna::getEventDispatcher(); TandaTanganPengguna::setEventDispatcher(clone $dispatcher);
            try {
                TandaTanganPengguna::updating(fn ($t) => $t->id === $target->id ? false : null);
                tanpaPerubahan($disk, fn () => $service->aktifkan(6, $v1->id), RuntimeException::class, $pesan);
            } finally { TandaTanganPengguna::setEventDispatcher($dispatcher); }
        });
    }
    $uji('Versioning setelah aktivasi historis', function () use ($service, $v1, $url, &$ids) {
        $service->aktifkan(6, $v1->id); $t = simpanId($service->gantiDariCanvas(6, $url), $ids);
        pastikan($t->versi === 4 && $t->is_active && !$v1->refresh()->is_active);
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
