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
function pastikan(bool $nilai): void { if (!$nilai) { throw new RuntimeException('Hasil tidak sesuai.'); } }
function ditolak(callable $aksi, string $kelas = ValidationException::class): void {
    try { $aksi(); } catch (Throwable $e) { if ($e instanceof $kelas) { return; } throw $e; }
    throw new RuntimeException('Operasi seharusnya ditolak.');
}
$uji = function (string $nama, callable $aksi) use (&$pass, &$fail): void {
    try { $aksi(); $pass++; echo "PASS - $nama\n"; } catch (Throwable $e) { $fail++; echo "FAIL - $nama: " . $e->getMessage() . "\n"; }
};
try {
    $id = $master->id;
    $a = $service->buatTemplate($id, $data, 'bawaan'); $ids[] = $a->id;
    $awal = $a->getAttributes();
    $b = $service->buatVersiBaru($id, $a->id, array_replace($data, ['nama_template' => 'Versi 2'])); $ids[] = $b->id;
    $c = $service->buatVersiBaru($id, $b->id, $data, 'bawaan'); $ids[] = $c->id;
    $d = $service->buatVersiBaru($id, $c->id, $data, 'tanpa_logo'); $ids[] = $d->id;
    $uji('Versi berurutan', fn() => pastikan([$a->versi,$b->versi,$c->versi,$d->versi] === [1,2,3,4]));
    $uji('Metadata sumber tetap', fn() => pastikan($a->fresh()->getAttributes() === $awal));
    $uji('Versi baru nonaktif', fn() => pastikan(TemplateSuratCuti::aktif()->count() === 0));
    $uji('Logo sumber digunakan kembali', fn() => pastikan($a->logo_path === $b->logo_path));
    $uji('Logo baru berbeda', fn() => pastikan($c->logo_path !== $b->logo_path && $disk->exists($c->logo_path)));
    $uji('Versi tanpa logo', fn() => pastikan($d->logo_path === null));
    $uji('Logo historis tersedia', fn() => pastikan($disk->exists($a->logo_path)));
    foreach (['buatVersiBaru','aktifkan','nonaktifkan','hapusTemplate'] as $method) {
        $uji('Nonmaster ditolak: '.$method, fn() => ditolak(fn() => $method === 'buatVersiBaru' ? $service->$method($nonmaster->id,$a->id,$data) : $service->$method($nonmaster->id,$a->id)));
    }
    $uji('Aktivasi pertama', function () use ($service,$id,$a) { $service->aktifkan($id,$a->id); pastikan($a->fresh()->isAktif()); });
    $uji('Pergantian aktif', function () use ($service,$id,$a,$b) { $service->aktifkan($id,$b->id); pastikan($a->fresh()->isNonaktif() && $b->fresh()->isAktif() && TemplateSuratCuti::aktif()->count() === 1); });
    $uji('Aktivasi idempotent', function () use ($service,$id,$b) { $awal = $b->fresh()->getAttributes(); $service->aktifkan($id,$b->id); pastikan($awal === $b->fresh()->getAttributes()); });
    $uji('Reaktivasi historis', function () use ($service,$id,$a,$b) { $service->aktifkan($id,$a->id); pastikan($a->fresh()->isAktif() && $b->fresh()->isNonaktif()); });
    foreach (['hilang','rusak'] as $jenis) {
        $uji('Logo '.$jenis.' ditolak', function () use ($service,$id,$a,$c,$disk,$jenis) { $isi = $disk->get($c->logo_path); try { pastikan($jenis === 'hilang' ? $disk->delete($c->logo_path) : $disk->put($c->logo_path,'bukan gambar')); ditolak(fn() => $service->aktifkan($id,$c->id)); pastikan($a->fresh()->isAktif() && $c->fresh()->isNonaktif()); } finally { pastikan($disk->put($c->logo_path,$isi)); } });
    }
    $uji('Template aktif dilindungi', fn() => ditolak(fn() => $service->hapusTemplate($id,$a->id)));
    $uji('Nonaktivasi', function () use ($service,$id,$a) { $service->nonaktifkan($id,$a->id); pastikan(TemplateSuratCuti::aktif()->count() === 0); });
    $uji('Nonaktivasi idempotent', function () use ($service,$id,$a) { $awal = $a->fresh()->getAttributes(); $service->nonaktifkan($id,$a->id); pastikan($awal === $a->fresh()->getAttributes()); });
    $uji('Template historis dilindungi', fn() => ditolak(fn() => $service->hapusTemplate($id,$a->id)));
    $uji('Rollback aktivasi gagal', function () use ($service,$id,$a,$b) { $service->aktifkan($id,$a->id); $awal = $a->fresh()->getAttributes(); $dispatcher = TemplateSuratCuti::getEventDispatcher(); TemplateSuratCuti::setEventDispatcher(clone $dispatcher); TemplateSuratCuti::updating(fn($model) => $model->id === $b->id ? false : null); try { ditolak(fn() => $service->aktifkan($id,$b->id), RuntimeException::class); } finally { TemplateSuratCuti::setEventDispatcher($dispatcher); } pastikan($a->fresh()->getAttributes() === $awal && $b->fresh()->isNonaktif() && DB::transactionLevel() === 0); });
    $uji('Rollback versi gagal', function () use ($service,$id,$a,$data,$disk) { $awal = $disk->allFiles(); $jumlah = TemplateSuratCuti::count(); $dispatcher = TemplateSuratCuti::getEventDispatcher(); TemplateSuratCuti::setEventDispatcher(clone $dispatcher); TemplateSuratCuti::creating(fn() => false); try { ditolak(fn() => $service->buatVersiBaru($id,$a->id,$data,'bawaan'),RuntimeException::class); } finally { TemplateSuratCuti::setEventDispatcher($dispatcher); } pastikan($awal === $disk->allFiles() && $jumlah === TemplateSuratCuti::count() && DB::transactionLevel() === 0); });
    $uji('Hapus template baru', function () use ($service,$id,$c) { $service->hapusTemplate($id,$c->id); pastikan($c->fresh() === null); });
    $uji('Logo template terhapus tetap ada', fn() => pastikan($disk->exists($c->logo_path)));
} finally {
    $uji('Pembersihan data dan storage', function () use ($ids, $disk, $root) {
        TemplateSuratCuti::whereIn('id', $ids)->delete();
        pastikan(TemplateSuratCuti::count() === 0 && DB::transactionLevel() === 0);
        pastikan($disk->deleteDirectory('cuti'));
        if (is_dir($root)) { pastikan(rmdir($root)); }
    });
    config(['filesystems.disks.local.root' => $rootAwal]);
    Storage::forgetDisk('local');
}
echo "TOTAL: " . ($pass + $fail) . "\n";
echo "PASS : $pass\nFAIL : $fail\n";
exit($fail === 0 ? 0 : 1);
