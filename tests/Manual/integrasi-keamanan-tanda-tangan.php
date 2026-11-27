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

echo "PENGUJIAN INTEGRASI DAN KEAMANAN TANDA TANGAN\n\n";
try {
    $v1 = simpanId($service->simpanDariCanvas(6, $url), $ids);
    $snap1 = $service->buatSnapshot(6, $v1->id);
    $v2 = simpanId($service->gantiDariCanvas(6, urlPng(20)), $ids);
    $uji('Siklus simpan snapshot dan ganti', function () use ($service, $v1, $v2, $snap1) {
        pastikan($v2->versi === 2 && !$v1->fresh()->is_active && $service->wajibAktif(6)->id === $v2->id);
        pastikan(hash('sha256', $service->bacaFile(6, $v1->id)) === $snap1['file_hash']);
    });
    $uji('Aktivasi historis dan snapshot baru', function () use ($service, $v1, $v2, $snap1) {
        $service->aktifkan(6, $v1->id);
        $snap = $service->buatSnapshot(6, $v1->id);
        pastikan(!$v2->fresh()->is_active && $snap['file_hash'] === $snap1['file_hash'] && $snap['versi'] === 1);
    });
    $uji('Rollback transaksi luar perubahan status', function () use ($service, $disk, $v1, $v2) {
        $awal = keadaan($disk);
        DB::beginTransaction();
        try {
            $service->aktifkan(6, $v2->id);
            pastikan($service->buatSnapshot(6, $v2->id)['versi'] === 2);
        } finally { DB::rollBack(); }
        pastikan(keadaan($disk) === $awal);
    });
    $uji('Penonaktifan menghentikan snapshot baru', function () use ($service, $disk, $v1) {
        $service->nonaktifkan(6, $v1->id);
        pastikan($service->cariAktif(6) === null);
        tanpaPerubahan($disk, fn () => $service->buatSnapshot(6, $v1->id), ValidationException::class, 'Tanda tangan aktif tidak ditemukan.');
    });
    $v3 = simpanId($service->gantiDariCanvas(6, urlPng(30)), $ids);
    $uji('Versi tetap meningkat setelah penonaktifan', fn () => pastikan($v3->versi === 3 && $service->wajibAktif(6)->id === $v3->id));
    foreach ([
        'Prefix canvas salah' => ['data:image/jpeg;base64,' . base64_encode($png), 'Data canvas tidak valid'],
        'Base64 tidak kanonik' => [$url . "\n", 'Encoding Base64 tidak valid'],
        'Canvas kosong' => ['data:image/png;base64,', 'Ukuran gambar tidak valid'],
        'Canvas melebihi batas' => ['data:image/png;base64,' . str_repeat('A', 2796208), 'Data canvas tidak valid'],
        'Konten bukan gambar' => ['data:image/png;base64,' . base64_encode('not an image'), 'Format gambar tidak diizinkan'],
    ] as $nama => [$input, $pesan]) {
        $uji($nama, fn () => tanpaPerubahan($disk, fn () => $service->gantiDariCanvas(6, $input), ValidationException::class, $pesan));
    }
    $uji('Upload JPEG dinormalisasi menjadi PNG', function () use ($service, &$ids) {
        $gambar = imagecreatetruecolor(600, 250);
        imagefill($gambar, 0, 0, imagecolorallocate($gambar, 255, 255, 255));
        imagesetthickness($gambar, 5);
        imageline($gambar, 20, 200, 500, 40, imagecolorallocate($gambar, 0, 0, 0));
        $tmp = tempnam(sys_get_temp_dir(), 'ttd');
        try {
            pastikan(imagejpeg($gambar, $tmp));
            $t = simpanId($service->simpanDariUpload(7, new UploadedFile($tmp, 'ttd.jpg', 'image/jpeg', null, true)), $ids);
            pastikan($t->mime_type === 'image/png' && str_starts_with($service->bacaFile(7, $t->id), "\x89PNG\r\n\x1a\n"));
        } finally { imagedestroy($gambar); if (is_file($tmp)) unlink($tmp); }
    });
    $uji('Upload ekstensi palsu ditolak', function () use ($service, $disk) {
        $tmp = tempnam(sys_get_temp_dir(), 'ttd');
        try {
            file_put_contents($tmp, 'not an image');
            tanpaPerubahan($disk, fn () => $service->gantiDariUpload(6, new UploadedFile($tmp, 'ttd.png', 'image/png', null, true)), ValidationException::class, 'Format gambar tidak diizinkan');
        } finally { if (is_file($tmp)) unlink($tmp); }
    });
    $uji('Metadata privat tidak muncul dalam serialisasi model', function () use ($v3) {
        $data = $v3->fresh()->toArray();
        foreach (['file_path', 'file_hash', 'active_user_id'] as $key) pastikan(!array_key_exists($key, $data));
    });
    $uji('Lock pengguna tanpa transaksi ditolak', fn () => tanpaPerubahan($disk, fn () => $service->validasiPengguna(6, true), LogicException::class, 'Lock pengguna wajib berada dalam transaksi database.'));
    $uji('Pengguna tidak tersedia ditolak', fn () => tanpaPerubahan($disk, fn () => $service->riwayat(0), ValidationException::class, 'Pengguna tidak berhak'));
    $uji('Seluruh file historis tetap valid', function () use ($service, $disk) {
        $awal = keadaan($disk);
        foreach ([6, 7] as $id) foreach ($service->riwayat($id) as $t) pastikan($service->verifikasiIntegritas($id, $t->id));
        pastikan(keadaan($disk) === $awal);
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
