<?php

use App\Models\JenisCuti;
use App\Models\Pegawai;
use App\Models\PengajuanCuti;
use App\Models\PersetujuanCuti;
use App\Models\DetailPengajuanCuti;
use App\Models\KuotaCuti;
use App\Models\MutasiKuotaCuti;
use App\Models\SuratPermohonanCuti;
use App\Models\SkCuti;
use App\Models\TemplateSuratCuti;
use App\Models\TandaTanganPengguna;
use App\Models\User;
use App\Services\Cuti\PengajuanCutiService;
use App\Services\Cuti\BentrokCutiService;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

require __DIR__ . '/../../vendor/autoload.php';
$app = require __DIR__ . '/../../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();
if (!app()->environment('local', 'testing') || DB::transactionLevel() !== 0) {
    throw new RuntimeException('Gunakan database lokal/testing tanpa transaksi aktif.');
}
$service = app(PengajuanCutiService::class);
$passed = 0;
$failed = 0;

function sama(mixed $expected, mixed $actual): void
{
    if ($expected !== $actual) {
        throw new RuntimeException('Expected: ' . var_export($expected, true) . ' Actual: ' . var_export($actual, true));
    }
}

function validasi(callable $callback, string $field): void
{
    try {
        $callback();
    } catch (ValidationException $e) {
        sama(true, isset($e->errors()[$field]));
        return;
    }
    throw new RuntimeException('ValidationException tidak terjadi.');
}

function data(): array
{
    return array_map(fn($model) => $model::count(), [JenisCuti::class, PengajuanCuti::class, DetailPengajuanCuti::class, KuotaCuti::class, MutasiKuotaCuti::class, PersetujuanCuti::class, SuratPermohonanCuti::class, SkCuti::class, TemplateSuratCuti::class, TandaTanganPengguna::class]);
}

function uji(string $nama, callable $callback): void
{
    global $passed, $failed;
    $level = DB::transactionLevel();
    DB::beginTransaction();
    try {
        $callback();
        $passed++;
        echo "[PASS] $nama\n";
    } catch (Throwable $e) {
        $failed++;
        echo "[FAIL] $nama\n       " . $e->getMessage() . "\n";
    } finally {
        if (DB::transactionLevel() > $level) {
            DB::rollBack($level);
        }
    }
}

function jenis(array $attributes = []): JenisCuti
{
    return JenisCuti::create(array_merge([
        'kode' => 'T-' . Str::upper(Str::random(6)),
        'nama' => 'Cuti Pengujian Pembatalan',
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
    ], $attributes));
}

function draft(int $pegawaiId = 2, array $attributes = [], string $mulai = '2026-11-30', string $selesai = '2026-12-06'): PengajuanCuti
{
    global $service;
    $pegawai = Pegawai::findOrFail($pegawaiId);
    $pegawai->update(['tanggal_masuk' => '2020-01-01']);
    return $service->buatDraft($pegawai->user_id, jenis($attributes)->id, $mulai, $selesai, 'Pengujian pembatalan');
}

function submit(PengajuanCuti $draft): PengajuanCuti
{
    global $service;
    return $service->ajukanDraft(Pegawai::findOrFail($draft->pegawai_id)->user_id, $draft->id);
}

function tahapan(PengajuanCuti $p)
{
    return PersetujuanCuti::where('pengajuan_cuti_id', $p->id)->orderBy('urutan')->get();
}

function batalkan(PengajuanCuti $p, string $alasan = 'Perubahan rencana'): PengajuanCuti
{
    global $service;
    return $service->batalkanPengajuan(Pegawai::findOrFail($p->pegawai_id)->user_id, $p->id, $alasan);
}

function kuota(PengajuanCuti $p): KuotaCuti
{
    return KuotaCuti::findOrFail($p->details()->whereNotNull('kuota_cuti_id')->firstOrFail()->kuota_cuti_id);
}

function snapshot(PengajuanCuti $p): array
{
    return [$p->fresh()->getAttributes(), $p->details()->get()->toArray(), tahapan($p)->toArray(), kuota($p)->snapshotSaldo(), MutasiKuotaCuti::where('pengajuan_cuti_id', $p->id)->orderBy('id')->get()->map(fn($m) => $m->getAttributes())->all(), SuratPermohonanCuti::where('pengajuan_cuti_id', $p->id)->orderBy('id')->get()->map(fn($m) => $m->getAttributes())->all(), data()];
}


function surat(PengajuanCuti $p, string $status = 'final', bool $latest = true, int $versi = 1): SuratPermohonanCuti
{
    $userId = Pegawai::findOrFail($p->pegawai_id)->user_id;
    $master = User::where('role', 'master')->firstOrFail();
    $template = TemplateSuratCuti::create([
        'kode_template' => 'TEST-' . Str::random(12),
        'nama_template' => 'Template pengujian',
        'versi' => 1,
        'nama_perusahaan' => 'PDAM Pengujian',
        'format_nomor_surat' => 'TEST',
        'isi_pembuka' => 'Pembuka',
        'isi_penutup' => 'Penutup',
        'dibuat_oleh' => $master->id,
        'is_active' => false,
    ]);
    $ttd = TandaTanganPengguna::create([
        'user_id' => $userId,
        'versi' => (int) TandaTanganPengguna::where('user_id', $userId)->max('versi') + 1,
        'metode' => 'upload',
        'file_path' => 'testing/ttd.png',
        'file_hash' => hash('sha256', 'ttd'),
        'mime_type' => 'image/png',
        'ukuran_file' => 100,
        'lebar' => 100,
        'tinggi' => 50,
        'is_active' => false,
    ]);
    return SuratPermohonanCuti::create([
        'pengajuan_cuti_id' => $p->id,
        'template_surat_cuti_id' => $template->id,
        'tanda_tangan_pemohon_id' => $ttd->id,
        'nomor_surat' => 'TEST-' . $p->id,
        'versi' => $versi,
        'status_dokumen' => $status,
        'status_pengajuan_snapshot' => $p->status,
        'template_snapshot' => $template->snapshotTemplate(),
        'pemohon_snapshot' => $p->pemohon_snapshot,
        'pengajuan_snapshot' => ['id' => $p->id],
        'detail_snapshot' => $p->details()->get()->toArray(),
        'persetujuan_snapshot' => tahapan($p)->toArray(),
        'tanda_tangan_snapshot' => ['id' => $ttd->id],
        'file_path' => 'testing/surat-' . $versi . '.pdf',
        'file_hash' => hash('sha256', 'surat-' . $versi),
        'ukuran_file' => 100,
        'token_verifikasi' => Str::random(64),
        'is_latest' => $latest,
        'dibuat_oleh' => $userId,
        'diterbitkan_pada' => $status === 'preview' ? null : now(),
    ]);
}

function sk(PengajuanCuti $p, bool $terbit): SkCuti
{
    $s = surat($p);
    $direktur = tahapan($p)->last();
    return SkCuti::create([
        'pengajuan_cuti_id' => $p->id,
        'persetujuan_cuti_id' => $direktur->id,
        'surat_permohonan_cuti_id' => $s->id,
        'nomor_sk' => 'TEST-SK-' . $p->id,
        'tanggal_sk' => today(),
        'tanggal_mulai_cuti' => $p->tanggal_mulai,
        'tanggal_selesai_cuti' => $p->tanggal_selesai,
        'jumlah_hari_disetujui' => 5,
        'tanggal_disetujui_snapshot' => $p->details()->where('dihitung_kuota', true)->get()->map(fn($d) => $d->tanggal->toDateString())->all(),
        'pemohon_snapshot' => $p->pemohon_snapshot,
        'direktur_snapshot' => $direktur->approver_snapshot,
        'nama_file_asli' => 'sk.pdf',
        'file_path' => 'testing/sk.pdf',
        'file_hash' => hash('sha256', 'sk'),
        'mime_type' => 'application/pdf',
        'ukuran_file' => 100,
        'diunggah_oleh' => Pegawai::findOrFail($direktur->approver_id)->user_id,
        'diunggah_pada' => now(),
        'diterbitkan_pada' => $terbit ? now() : null,
    ]);
}

$awal = data();
$waktu = CarbonImmutable::parse('2026-11-23 09:00:00', 'Asia/Jakarta');
Carbon::setTestNow($waktu);
CarbonImmutable::setTestNow($waktu);
echo "\nPENGUJIAN PEMBATALAN DOKUMEN CUTI\n\n";
try {
    foreach (['preview', 'final'] as $status) {
        uji("Surat $status aktif menjadi superseded", function () use ($status) {
            $p = submit(draft());
            $s = surat($p, $status);
            $sebelum = $s->fresh()->getAttributes();
            batalkan($p);
            $s->refresh();
            sama('superseded', $s->status_dokumen);
            sama(false, $s->is_latest);
            sama(null, $s->getAttribute('latest_pengajuan_id'));
            foreach (['status_dokumen', 'is_latest', 'latest_pengajuan_id', 'updated_at'] as $key) {
                unset($sebelum[$key]);
            }
            $sesudah = $s->getAttributes();
            foreach (['status_dokumen', 'is_latest', 'latest_pengajuan_id', 'updated_at'] as $key) {
                unset($sesudah[$key]);
            }
            sama($sebelum, $sesudah);
            sama(0, kuota($p)->kuota_direservasi);
        });
    }
    uji('Surat versi lama tidak berubah', function () {
        $p = submit(draft());
        $lama = surat($p, 'superseded', false);
        $aktif = surat($p, 'final', true, 2);
        $awal = $lama->fresh()->getAttributes();
        batalkan($p);
        sama($awal, $lama->fresh()->getAttributes());
        sama('superseded', $aktif->fresh()->status_dokumen);
        sama(2, SuratPermohonanCuti::where('pengajuan_cuti_id', $p->id)->count());
    });
    foreach ([false, true] as $terbit) {
        uji('SK ' . ($terbit ? 'sudah' : 'belum') . ' terbit memblokir pembatalan', function () use ($terbit) {
            $p = submit(draft(10));
            $sk = sk($p, $terbit);
            $awal = snapshot($p);
            $awalSk = $sk->fresh()->getAttributes();
            try {
                batalkan($p);
                throw new RuntimeException('Pembatalan tidak ditolak.');
            } catch (LogicException $e) {
                sama('Pengajuan yang telah memiliki data SK cuti tidak dapat dibatalkan.', $e->getMessage());
            }
            sama($awal, snapshot($p));
            sama($awalSk, $sk->fresh()->getAttributes());
        });
    }
    foreach ([SuratPermohonanCuti::class => 'status_dokumen', PersetujuanCuti::class => 'status'] as $model => $field) {
        uji('Rollback kegagalan pembaruan ' . $model, function () use ($model, $field) {
            $p = submit(draft());
            surat($p);
            $awal = snapshot($p);
            $dispatcher = $model::getEventDispatcher();
            $salinan = clone $dispatcher;
            $salinan->listen('eloquent.updating: ' . $model, function ($record) use ($field) {
                if (in_array($record->{$field}, ['cancelled', 'superseded'], true)) {
                    throw new RuntimeException('Simulasi kegagalan dokumen');
                }
            });
            $model::setEventDispatcher($salinan);
            try {
                try {
                    batalkan($p);
                    throw new RuntimeException('Kegagalan tidak terjadi');
                } catch (RuntimeException $e) {
                    sama('Simulasi kegagalan dokumen', $e->getMessage());
                }
            } finally {
                $model::setEventDispatcher($dispatcher);
            }
            sama($awal, snapshot($p));
        });
    }
} finally {
    Carbon::setTestNow();
    CarbonImmutable::setTestNow();
}

try {
    sama($awal, data());
    $passed++;
    echo "[PASS] Seluruh data pengujian berhasil di-rollback\n";
} catch (Throwable $e) {
    $failed++;
    echo "[FAIL] Rollback: " . $e->getMessage() . "\n";
}
echo "\n========================================\n";
echo 'Total pengujian : ' . ($passed + $failed) . "\nPASS            : $passed\nFAIL            : $failed\n========================================\n";
exit($failed > 0 ? 1 : 0);