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
$service = app(PengajuanCutiService::class);
$passed = 0;
$failed = 0;

function sama(mixed $expected, mixed $actual): void
{
    if ($expected !== $actual) {
        throw new RuntimeException('Expected: ' . var_export($expected, true) . ' Actual: ' . var_export($actual, true));
    }
}

function samaSaldo(array $expected, array $actual): void
{
    ksort($expected);
    ksort($actual);
    sama($expected, $actual);
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
    return array_map(fn($model) => $model::count(), [JenisCuti::class, PengajuanCuti::class, DetailPengajuanCuti::class, KuotaCuti::class, MutasiKuotaCuti::class, PersetujuanCuti::class, SuratPermohonanCuti::class, SkCuti::class]);
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
        'nama' => 'Cuti Pengujian Integrasi',
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
    return [$p->fresh()->getAttributes(), $p->details()->get()->toArray(), tahapan($p)->toArray(), kuota($p)->snapshotSaldo(), data()];
}

function buatUntuk(JenisCuti $jenis, string $mulai = '2026-11-30', string $selesai = '2026-12-06'): PengajuanCuti
{
    global $service;
    $pegawai = Pegawai::findOrFail(2);
    $pegawai->update(['tanggal_masuk' => '2020-01-01']);
    return $service->buatDraft($pegawai->user_id, $jenis->id, $mulai, $selesai, 'Integrasi cuti');
}

function saldo(PengajuanCuti $p, int $sisa, int $reservasi): void
{
    $q = kuota($p);
    sama($sisa, $q->sisa_kuota);
    sama($reservasi, $q->kuota_direservasi);
    sama(0, $q->kuota_terpakai);
}

if (!app()->environment('local', 'testing')) {
    fwrite(STDERR, "Pengujian hanya untuk lingkungan local atau testing.\n");
    exit(1);
}

$awal = data();
if (array_sum($awal) !== 0) {
    fwrite(STDERR, "Gunakan database pengujian dengan tabel cuti kosong.\n");
    exit(1);
}
$waktu = CarbonImmutable::parse('2026-11-23 09:00:00', 'Asia/Jakarta');
Carbon::setTestNow($waktu);
CarbonImmutable::setTestNow($waktu);
echo "\nPENGUJIAN INTEGRASI PENGAJUAN CUTI\n\n";
try {
    uji('Siklus buat ubah submit dan batalkan', function () use ($service) {
        $j = jenis();
        $p = buatUntuk($j, '2026-11-30', '2026-12-02');
        sama(3, $p->jumlah_hari_diajukan);
        $id = $p->id;
        $p = $service->ubahDraft(Pegawai::findOrFail(2)->user_id, $id, $j->id, '2026-11-30', '2026-12-06', 'Perubahan');
        sama($id, $p->id);
        sama(5, $p->jumlah_hari_diajukan);
        sama(0, KuotaCuti::count());
        $p = submit($p);
        saldo($p, 7, 5);
        $p = batalkan($p);
        sama('cancelled', $p->status);
        saldo($p, 12, 0);
    });
    uji('Hapus draft tidak mengubah kuota aktif', function () use ($service) {
        $j = jenis();
        $p = submit(buatUntuk($j));
        $d = buatUntuk($j);
        $sebelum = snapshot($p);
        $hasil = $service->hapusDraft(Pegawai::findOrFail(2)->user_id, $d->id);
        sama(true, $hasil['deleted']);
        sama(false, PengajuanCuti::whereKey($d->id)->exists());
        sama(0, DetailPengajuanCuti::where('pengajuan_cuti_id', $d->id)->count());
        sama(array_slice($sebelum, 0, 4), array_slice(snapshot($p), 0, 4));
        saldo($p, 7, 5);
    });
    uji('Pengajuan kembali menggunakan kuota tahunan yang sama', function () {
        $j = jenis();
        $p = submit(buatUntuk($j));
        batalkan($p);
        $q = submit(buatUntuk($j, '2026-11-30', '2026-12-02'));
        sama(kuota($p)->id, kuota($q)->id);
        sama(true, $p->nomor_pengajuan !== $q->nomor_pengajuan);
        saldo($q, 9, 3);
        batalkan($q);
        saldo($q, 12, 0);
    });
    uji('Dua pengajuan aktif menggunakan satu kuota', function () {
        $j = jenis();
        $p = submit(buatUntuk($j));
        $q = submit(buatUntuk($j, '2026-12-07', '2026-12-09'));
        sama(kuota($p)->id, kuota($q)->id);
        saldo($q, 4, 8);
    });
    uji('Pembatalan tidak mengganggu reservasi pengajuan lain', function () {
        $j = jenis();
        $p = submit(buatUntuk($j));
        $q = submit(buatUntuk($j, '2026-12-07', '2026-12-09'));
        $sebelum = array_slice(snapshot($q), 0, 3);
        batalkan($p);
        saldo($q, 9, 3);
        sama($sebelum, array_slice(snapshot($q), 0, 3));
        $ks = app(\App\Services\Cuti\KuotaCutiService::class);
        sama(0, $ks->jumlahReservasiPengajuan(kuota($p)->id, $p->id));
        sama(3, $ks->jumlahReservasiPengajuan(kuota($q)->id, $q->id));
        batalkan($q);
        saldo($q, 12, 0);
    });
    uji('Saldo konsisten dengan rantai mutasi', function () {
        $j = jenis();
        $p = submit(buatUntuk($j));
        $q = submit(buatUntuk($j, '2026-12-07', '2026-12-09'));
        batalkan($p);
        batalkan($q);
        $mutasi = MutasiKuotaCuti::where('kuota_cuti_id', kuota($p)->id)->orderBy('id')->get();
        sama(['pemberian_kuota', 'reservasi', 'reservasi', 'pelepasan_reservasi', 'pelepasan_reservasi'], $mutasi->pluck('jenis_mutasi')->all());
        for ($i = 1; $i < $mutasi->count(); $i++) {
            samaSaldo($mutasi[$i - 1]->saldo_sesudah, $mutasi[$i]->saldo_sebelum);
        }
        samaSaldo(kuota($p)->snapshotSaldo(), $mutasi->last()->saldo_sesudah);
    });
    uji('Status detail dan persetujuan konsisten', function () {
        $p = submit(buatUntuk(jenis()));
        sama(5, $p->details->where('status', 'pending')->count());
        sama(['pending', 'waiting', 'waiting'], tahapan($p)->pluck('status')->all());
        $p = batalkan($p);
        sama(5, $p->details->where('status', 'cancelled')->count());
        sama(2, $p->details->where('status', 'excluded')->count());
        sama(['cancelled', 'cancelled', 'cancelled'], tahapan($p)->pluck('status')->all());
        sama(null, $p->current_approver_id);
    });
    uji('Riwayat pengajuan dibatalkan tetap tersimpan', function () {
        $p = submit(buatUntuk(jenis()));
        $sebelum = $p->getAttributes();
        $ids = $p->details->pluck('id')->all();
        $p = batalkan($p);
        foreach (['nomor_pengajuan', 'tanggal_pengajuan', 'diajukan_pada', 'kebijakan_snapshot', 'pemohon_snapshot', 'alasan', 'lampiran'] as $field) {
            sama($sebelum[$field], $p->getAttributes()[$field]);
        }
        sama($ids, $p->details->pluck('id')->all());
        sama(0, SkCuti::where('pengajuan_cuti_id', $p->id)->count());
    });
    uji('Kebijakan terbaru berlaku pada draft tanpa mengubah pengajuan aktif', function () {
        $j = jenis();
        $p = submit(buatUntuk($j));
        $d = buatUntuk($j, '2026-12-07', '2026-12-09');
        $sebelum = snapshot($p);
        $j->update(['wajib_lampiran' => true]);
        validasi(fn() => submit($d), 'lampiran');
        sama('draft', $d->fresh()->status);
        sama($sebelum, snapshot($p));
        batalkan($p);
        saldo($p, 12, 0);
    });
    uji('Rollback transaksi luar mengembalikan seluruh siklus', function () {
        $sebelum = data();
        $pegawai = Pegawai::findOrFail(2)->getAttributes();
        $level = DB::transactionLevel();
        DB::beginTransaction();
        try {
            $p = submit(buatUntuk(jenis()));
            batalkan($p);
            saldo($p, 12, 0);
        } finally {
            DB::rollBack($level);
        }
        sama($sebelum, data());
        sama($pegawai, Pegawai::findOrFail(2)->getAttributes());
    });
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