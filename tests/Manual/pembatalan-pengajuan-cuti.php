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
    return [$p->fresh()->getAttributes(), $p->details()->get()->toArray(), tahapan($p)->toArray(), kuota($p)->snapshotSaldo(), data()];
}

$awal = data();
$waktu = CarbonImmutable::parse('2026-11-23 09:00:00', 'Asia/Jakarta');
Carbon::setTestNow($waktu);
CarbonImmutable::setTestNow($waktu);
echo "\nPENGUJIAN PEMBATALAN PENGAJUAN CUTI\n\n";
try {
    foreach ([2, 1, 10] as $id) {
        uji("Pembatalan pemohon ID $id", function () use ($id, $waktu) {
            $p = submit(draft($id));
            $nomor = $p->nomor_pengajuan;
            $hasil = batalkan($p, '  Perubahan rencana  ');
            sama('cancelled', $hasil->status);
            sama(null, $hasil->current_approver_id);
            sama(0, $hasil->jumlah_hari_disetujui);
            sama($nomor, $hasil->nomor_pengajuan);
            sama('Perubahan rencana', $hasil->alasan_pembatalan);
            sama($waktu->toDateTimeString(), $hasil->dibatalkan_pada->toDateTimeString());
            sama($hasil->dibatalkan_pada->toDateTimeString(), $hasil->diselesaikan_pada->toDateTimeString());
            sama(5, $hasil->details->where('status', 'cancelled')->count());
            sama(2, $hasil->details->where('status', 'excluded')->count());
            sama(tahapan($p)->count(), tahapan($p)->where('status', 'cancelled')->count());
            sama(12, kuota($p)->sisa_kuota);
            sama(0, kuota($p)->kuota_direservasi);
            sama(0, kuota($p)->kuota_terpakai);
            $m = MutasiKuotaCuti::where('pengajuan_cuti_id', $p->id)->where('jenis_mutasi', 'pelepasan_reservasi')->sole();
            sama(5, $m->jumlah_hari);
            sama(5, $m->perubahan_sisa);
            sama(null, $m->persetujuan_cuti_id);
            sama((int) Pegawai::findOrFail($id)->user_id, (int) $m->user_id);
            sama('pembatalan_pengajuan', $m->metadata['sumber']);
        });
    }
    foreach (['per_kejadian', 'seumur_kerja', 'tanpa_batas'] as $periode) {
        uji("Pembatalan kuota $periode", function () use ($periode) {
            $p = submit(draft(2, ['periode_kuota' => $periode, 'kuota_hari' => $periode === 'tanpa_batas' ? null : 12]));
            batalkan($p);
            sama(0, kuota($p)->kuota_direservasi);
            sama($periode === 'tanpa_batas' ? null : 12, kuota($p)->sisa_kuota);
            $m = MutasiKuotaCuti::where('pengajuan_cuti_id', $p->id)->where('jenis_mutasi', 'pelepasan_reservasi')->sole();
            sama($periode === 'tanpa_batas' ? null : 5, $m->perubahan_sisa);
        });
    }
    uji('Pembatalan ganda ditolak', function () {
        $p = submit(draft());
        batalkan($p);
        $awal = snapshot($p);
        validasi(fn() => batalkan($p), 'status');
        sama($awal, snapshot($p));
    });
    uji('Pemohon lain ditolak', function () use ($service) {
        $p = submit(draft());
        $awal = snapshot($p);
        validasi(fn() => $service->batalkanPengajuan(Pegawai::findOrFail(3)->user_id, $p->id, 'Uji'), 'pengajuan_cuti_id');
        sama($awal, snapshot($p));
    });
    uji('Alasan kosong dan terlalu panjang ditolak', function () {
        $p = submit(draft());
        $awal = snapshot($p);
        foreach (['   ', str_repeat('a', 65536)] as $alasan) {
            validasi(fn() => batalkan($p, $alasan), 'alasan_pembatalan');
        }
        sama($awal, snapshot($p));
    });
    uji('Draft tidak dapat dibatalkan', function () {
        $p = draft();
        validasi(fn() => batalkan($p), 'status');
        sama('draft', $p->fresh()->status);
    });
    foreach (['approved', 'partially_approved', 'rejected'] as $status) {
        uji("Status $status ditolak", function () use ($status) {
            $p = submit(draft());
            $p->update(['status' => $status]);
            $awal = snapshot($p);
            validasi(fn() => batalkan($p), 'status');
            sama($awal, snapshot($p));
        });
    }
    uji('Current approver tidak konsisten ditolak', function () {
        $p = submit(draft());
        $p->update(['current_approver_id' => 10]);
        $awal = snapshot($p);
        validasi(fn() => batalkan($p), 'status');
        sama($awal, snapshot($p));
    });
    uji('Penolakan sebagian tidak dikembalikan dua kali', function () use ($waktu) {
        $p = submit(draft());
        [$a, $b] = tahapan($p)->all();
        $ditolak = $p->details()->where('status', 'pending')->orderBy('tanggal')->limit(2)->get();
        app(\App\Services\Cuti\KuotaCutiService::class)->pelepasanReservasiPengajuan($p->id, $ditolak->pluck('id')->map(fn($id) => (int) $id)->all(), $a->id, Pegawai::findOrFail($a->approver_id)->user_id);
        $sisa = $p->details()->where('status', 'pending')->orderBy('tanggal')->get()->map(fn($d) => $d->tanggal->toDateString())->all();
        $a->update(['status' => 'partially_approved', 'jumlah_hari_disetujui' => 3, 'jumlah_hari_ditolak' => 2, 'tanggal_disetujui' => $sisa, 'tanggal_ditolak' => $ditolak->map(fn($d) => $d->tanggal->toDateString())->all(), 'diproses_pada' => $waktu]);
        $b->update(['status' => 'pending', 'jumlah_hari_diterima' => 3, 'tanggal_diterima' => $sisa, 'diterima_pada' => $waktu]);
        $p->update(['status' => 'pending_kepala_bagian', 'current_approver_id' => $b->approver_id]);
        $riwayat = $a->fresh()->getAttributes();
        sama(3, kuota($p)->kuota_direservasi);
        batalkan($p);
        sama($riwayat, $a->fresh()->getAttributes());
        sama(2, $p->details()->where('status', 'rejected')->count());
        sama(3, $p->details()->where('status', 'cancelled')->count());
        sama(12, kuota($p)->sisa_kuota);
        sama(0, kuota($p)->kuota_direservasi);
        sama(3, MutasiKuotaCuti::where('pengajuan_cuti_id', $p->id)->where('jenis_mutasi', 'pelepasan_reservasi')->whereNull('persetujuan_cuti_id')->sole()->jumlah_hari);
    });
    uji('Pembatalan membebaskan tanggal untuk pengajuan baru', function () {
        $p = submit(draft());
        batalkan($p);
        sama('pending_atasan', submit(draft())->status);
    });
    uji('Kegagalan status akhir mengembalikan seluruh perubahan', function () {
        $p = submit(draft());
        $awal = snapshot($p);
        $dispatcher = PengajuanCuti::getEventDispatcher();
        $salinan = clone $dispatcher;
        $salinan->listen('eloquent.updating: ' . PengajuanCuti::class, function ($model) {
            if ($model->status === 'cancelled') {
                throw new RuntimeException('Simulasi kegagalan pembatalan');
            }
        });
        PengajuanCuti::setEventDispatcher($salinan);
        try {
            try {
                batalkan($p);
                throw new RuntimeException('Kegagalan tidak terjadi');
            } catch (RuntimeException $e) {
                sama('Simulasi kegagalan pembatalan', $e->getMessage());
            }
        } finally {
            PengajuanCuti::setEventDispatcher($dispatcher);
        }
        sama($awal, snapshot($p));
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