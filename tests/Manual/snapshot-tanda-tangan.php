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

echo "PENGUJIAN SNAPSHOT TANDA TANGAN\n\n";
try {
    $v1 = simpanId($service->simpanDariCanvas(6, $url), $ids);
    $v2 = simpanId($service->gantiDariCanvas(6, urlPng(20)), $ids);
    $lain = simpanId($service->simpanDariCanvas(7, $url), $ids);
    $snapshot = $service->buatSnapshot(6, $v2->id);
    $awal = keadaan($disk);
    $uji('Pencarian tanda tangan aktif', fn () => pastikan($service->cariAktif(6)->id === $v2->id));
    $uji('Pencarian ketika tidak ada tanda tangan aktif', fn () => pastikan($service->cariAktif(8) === null));
    $uji('Validasi tanda tangan aktif wajib', fn () => pastikan($service->wajibAktif(6)->id === $v2->id));
    $uji('Riwayat diurutkan dari versi terbaru', fn () => pastikan($service->riwayat(6)->pluck("id")->all() === [$v2->id, $v1->id]));
    $uji('Isolasi riwayat antar pengguna', fn () => pastikan($service->riwayat(7)->pluck("id")->all() === [$lain->id]));
    $uji('Verifikasi integritas versi aktif', fn () => pastikan($service->verifikasiIntegritas(6, $v2->id)));
    $uji('Verifikasi integritas versi nonaktif', fn () => pastikan($service->verifikasiIntegritas(6, $v1->id)));
    $uji('Pembacaan binary versi aktif', fn () => pastikan($service->bacaFile(6, $v2->id) === $disk->get($v2->file_path)));
    $uji('Pembacaan binary versi historis', fn () => pastikan($service->bacaFile(6, $v1->id) === $disk->get($v1->file_path)));
    $uji('Kelengkapan struktur snapshot', fn () => pastikan(array_keys($snapshot) === ["tanda_tangan_id", "user_id", "versi", "metode", "file_path", "file_hash", "mime_type", "ukuran_file", "lebar", "tinggi", "snapshot_pada"]));
    $uji('Kesesuaian metadata snapshot', fn () => pastikan(array_diff_key($snapshot, ["snapshot_pada" => true, "tanda_tangan_id" => true]) === array_intersect_key($v2->only(["user_id", "versi", "metode", "file_path", "file_hash", "mime_type", "ukuran_file", "lebar", "tinggi"]), $snapshot) && $snapshot["tanda_tangan_id"] === $v2->id));
    $uji('Kesesuaian timestamp snapshot', fn () => pastikan(abs(\Carbon\Carbon::parse($snapshot["snapshot_pada"])->timestamp - now()->timestamp) <= 10));
    $uji('Snapshot dapat diserialisasi menjadi JSON', fn () => pastikan(json_decode(json_encode($snapshot, JSON_THROW_ON_ERROR), true, 512, JSON_THROW_ON_ERROR) === $snapshot));
    pastikan(keadaan($disk) === $awal);
    $json = json_encode($snapshot, JSON_THROW_ON_ERROR);
    $v3 = simpanId($service->gantiDariCanvas(6, urlPng(40)), $ids);
    $uji('Snapshot lama tetap sama setelah penggantian', fn () => pastikan(json_encode($snapshot, JSON_THROW_ON_ERROR) === $json && $snapshot['file_hash'] === hash('sha256', $service->bacaFile(6, $v2->id)) && !$v2->refresh()->is_active));
    $uji('Snapshot baru menggunakan versi terbaru', fn () => pastikan($service->buatSnapshot(6, $v3->id)['versi'] === 3));
    $uji('Snapshot versi nonaktif ditolak', fn () => tanpaPerubahan($disk, fn () => $service->buatSnapshot(6, $v2->id), ValidationException::class, 'Tanda tangan aktif tidak ditemukan.'));
    $uji('Snapshot tanpa record ditolak', fn () => tanpaPerubahan($disk, fn () => $service->buatSnapshot(6, 999999), ValidationException::class, 'Tanda tangan aktif tidak ditemukan.'));
    $uji('Akses record pengguna lain ditolak', function () use ($disk, $service, $lain) {
        foreach (['bacaFile', 'verifikasiIntegritas', 'buatSnapshot'] as $method) {
            tanpaPerubahan($disk, fn () => $service->$method(6, $lain->id), ValidationException::class, $method === 'buatSnapshot' ? 'Tanda tangan aktif tidak ditemukan.' : 'Tanda tangan tidak ditemukan.');
        }
    });
    $uji('Akses master ditolak', fn () => tanpaPerubahan($disk, fn () => $service->riwayat(1), ValidationException::class, 'Pengguna tidak berhak'));
    $uji('Akses pegawai nonaktif ditolak', function () use ($disk, $service, $v3) {
        DB::beginTransaction();
        try {
            pastikan(\App\Models\Pegawai::where('user_id', 6)->update(['status' => 'nonaktif']) === 1);
            tanpaPerubahan($disk, fn () => $service->buatSnapshot(6, $v3->id), ValidationException::class, 'Pengguna tidak berhak');
        } finally { DB::rollBack(); }
    });
    $isi = $disk->get($v3->file_path);
    $uji('Verifikasi file hilang', function () use ($disk, $service, $v3, $isi) {
        pastikan($disk->delete($v3->file_path));
        try { pastikan(!$service->verifikasiIntegritas(6, $v3->id)); }
        finally { pastikan($disk->put($v3->file_path, $isi)); }
    });
    $uji('Verifikasi file rusak dengan ukuran sama', function () use ($disk, $service, $v3, $isi) {
        pastikan($disk->put($v3->file_path, str_repeat('x', strlen($isi))));
        try { pastikan(!$service->verifikasiIntegritas(6, $v3->id)); }
        finally { pastikan($disk->put($v3->file_path, $isi)); }
    });
    $uji('Verifikasi ukuran file tidak sesuai', function () use ($disk, $service, $v3, $isi) {
        pastikan($disk->put($v3->file_path, $isi . 'x'));
        try { pastikan(!$service->verifikasiIntegritas(6, $v3->id)); }
        finally { pastikan($disk->put($v3->file_path, $isi)); }
    });
    $uji('Penolakan path tidak valid', function () use ($disk, $service, $v3, $lain) {
        DB::beginTransaction();
        try {
            DB::table('tanda_tangan_pengguna')->where('id', $v3->id)->update(['file_path' => $lain->file_path]);
            pastikan(!$service->verifikasiIntegritas(6, $v3->id));
            tanpaPerubahan($disk, fn () => $service->bacaFile(6, $v3->id), ValidationException::class, 'Path tanda tangan tidak valid.');
        } finally { DB::rollBack(); }
    });
    foreach (['bacaFile' => 'Pembacaan file rusak ditolak', 'buatSnapshot' => 'Snapshot file rusak ditolak'] as $method => $nama) {
        $uji($nama, function () use ($disk, $service, $v3, $isi, $method) {
            pastikan($disk->put($v3->file_path, str_repeat('x', strlen($isi))));
            try { tanpaPerubahan($disk, fn () => $service->$method(6, $v3->id), ValidationException::class, 'Integritas file tanda tangan tidak valid.'); }
            finally { pastikan($disk->put($v3->file_path, $isi)); }
        });
    }
    $uji('Operasi pembacaan tidak mengubah metadata', function () use ($disk, $service, $v3) {
        $a = keadaan($disk);
        $service->cariAktif(6); $service->wajibAktif(6); $service->riwayat(6); $service->bacaFile(6, $v3->id); $service->verifikasiIntegritas(6, $v3->id); $service->buatSnapshot(6, $v3->id);
        pastikan(keadaan($disk) === $a);
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
