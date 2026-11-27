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
    return array_map(fn ($model) => $model::count(), [JenisCuti::class, PengajuanCuti::class, DetailPengajuanCuti::class, KuotaCuti::class, MutasiKuotaCuti::class, PersetujuanCuti::class, SuratPermohonanCuti::class, SkCuti::class]);
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
        'nama' => 'Cuti Pengujian Submit',
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
    return $service->buatDraft($pegawai->user_id, jenis($attributes)->id, $mulai, $selesai, 'Pengujian submit');
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

function tetapDraft(PengajuanCuti $p, array $awal, array $detailAwal): void
{
    sama($awal, data());
    sama(PengajuanCuti::STATUS_DRAFT, $p->fresh()->status);
    sama(null, $p->fresh()->nomor_pengajuan);
    sama(null, $p->fresh()->current_approver_id);
    sama($detailAwal, $p->details()->orderBy('id')->get()->toArray());
}

$awal = data();
$waktu = CarbonImmutable::parse('2026-11-23 09:00:00', 'Asia/Jakarta');
Carbon::setTestNow($waktu);
CarbonImmutable::setTestNow($waktu);
echo "\n========================================\n PENGUJIAN SUBMIT PENGAJUAN CUTI\n========================================\n\n";

try {
    foreach ([2 => ['pending_atasan', 1, 3], 1 => ['pending_kepala_bagian', 10, 2], 10 => ['pending_direktur', 11, 1]] as $id => $expected) {
        uji("Submit pemohon pegawai ID $id", function () use ($id, $expected) {
            $p = submit(draft($id));
            sama($expected[0], $p->status);
            sama($expected[1], (int) $p->current_approver_id);
            sama($expected[2], tahapan($p)->count());
            sama('pending', tahapan($p)->first()->status);
            sama(1, tahapan($p)->where('status', 'pending')->count());
        });
    }
    uji('Nomor tanggal dan waktu submit sesuai', function () use ($waktu) {
        $p = submit(draft());
        sama(sprintf('CUTI-20261123-%06d', $p->id), $p->nomor_pengajuan);
        sama('2026-11-23', $p->tanggal_pengajuan->toDateString());
        sama($waktu->timestamp, $p->diajukan_pada->timestamp);
    });
    uji('Reservasi lima hari dan saldo tujuh hari', function () {
        $p = submit(draft());
        $q = KuotaCuti::findOrFail($p->details()->whereNotNull('kuota_cuti_id')->firstOrFail()->kuota_cuti_id);
        sama(5, $q->kuota_direservasi);
        sama(7, $q->sisa_kuota);
        sama(0, $q->kuota_terpakai);
        sama(5, $p->details()->whereNotNull('kuota_cuti_id')->count());
        sama(2, $p->details()->whereNull('kuota_cuti_id')->count());
        sama(5, tahapan($p)->first()->jumlah_hari_diterima);
        sama(0, $p->jumlah_hari_disetujui);
    });
    uji('Kalender dan snapshot kebijakan diperbarui', function () {
        $p = draft();
        JenisCuti::findOrFail($p->jenis_cuti_id)->update(['metode_hitung_hari' => 'hari_kalender']);
        $p = submit($p);
        sama(7, $p->jumlah_hari_diajukan);
        sama('hari_kalender', $p->kebijakan_snapshot['metode_hitung_hari']);
        sama(7, tahapan($p)->first()->jumlah_hari_diterima);
    });
    uji('Submit ganda ditolak tanpa perubahan data', function () {
        $p = submit(draft());
        $before = data();
        validasi(fn () => submit($p), 'status');
        sama($before, data());
    });
    uji('Bentrok lintas jenis cuti ditolak', function () {
        submit(draft());
        $p = draft();
        $before = data();
        $details = $p->details()->orderBy('id')->get()->toArray();
        validasi(fn () => submit($p), 'tanggal_mulai');
        tetapDraft($p, $before, $details);
    });
    uji('Pegawai berbeda dapat submit tanggal sama', function () {
        $a = submit(draft());
        $b = submit(draft(3));
        sama(false, $a->nomor_pengajuan === $b->nomor_pengajuan);
    });
    uji('Pemohon lain tidak dapat submit', function () use ($service) {
        $p = draft();
        validasi(fn () => $service->ajukanDraft(Pegawai::findOrFail(3)->user_id, $p->id), 'pengajuan_cuti_id');
    });
    foreach ([['is_active' => false], ['wajib_lampiran' => true], ['minimal_hari_pengajuan' => 8], ['maksimal_hari_pengajuan' => 2], ['minimal_masa_kerja_bulan' => 120], ['kuota_hari' => 2]] as $i => $attributes) {
        $fields = ['jenis_cuti_id', 'lampiran', 'tanggal_mulai', 'tanggal_selesai', 'jenis_cuti_id', 'kuota_cuti'];
        uji('Validasi ulang ' . array_key_first($attributes), function () use ($attributes, $fields, $i) {
            $p = draft();
            JenisCuti::findOrFail($p->jenis_cuti_id)->update($attributes);
            $before = data();
            $details = $p->details()->orderBy('id')->get()->toArray();
            validasi(fn () => submit($p), $fields[$i]);
            tetapDraft($p, $before, $details);
        });
    }
    uji('Hierarki tidak lengkap ditolak', function () {
        $p = draft();
        Pegawai::findOrFail(1)->update(['atasan_id' => null]);
        validasi(fn () => submit($p), 'hierarki');
    });
    uji('Kuota tahun mendatang ditolak secara atomik', function () {
        $p = draft(2, [], '2026-12-30', '2027-01-05');
        $before = data();
        $details = $p->details()->orderBy('id')->get()->toArray();
        validasi(fn () => submit($p), 'kuota_cuti');
        tetapDraft($p, $before, $details);
    });
    foreach (['per_kejadian', 'seumur_kerja', 'tanpa_batas'] as $periode) {
        uji("Submit kuota $periode", function () use ($periode) {
            $p = submit(draft(2, ['periode_kuota' => $periode, 'kuota_hari' => $periode === 'tanpa_batas' ? null : 12]));
            sama('pending_atasan', $p->status);
            sama(5, KuotaCuti::findOrFail($p->details()->whereNotNull('kuota_cuti_id')->firstOrFail()->kuota_cuti_id)->kuota_direservasi);
        });
    }
    uji('Kegagalan persetujuan mengembalikan seluruh submit', function () {
        $p = draft();
        $before = data();
        $details = $p->details()->orderBy('id')->get()->toArray();
        $dispatcher = PersetujuanCuti::getEventDispatcher();
        $salinan = clone $dispatcher;
        $salinan->listen('eloquent.creating: ' . PersetujuanCuti::class, function ($record) {
            if ((int) $record->urutan === 2) {
                throw new RuntimeException('Simulasi gagal persetujuan');
            }
        });
        PersetujuanCuti::setEventDispatcher($salinan);
        try {
            submit($p);
            throw new RuntimeException('Submit seharusnya gagal');
        } catch (RuntimeException $e) {
            sama('Simulasi gagal persetujuan', $e->getMessage());
        } finally {
            PersetujuanCuti::setEventDispatcher($dispatcher);
        }
        tetapDraft($p, $before, $details);
    });
    uji('Kegagalan penyimpanan status akhir mengembalikan seluruh submit', function () {
        $p = draft();
        $before = data();
        $details = $p->details()->orderBy('id')->get()->toArray();
        $dispatcher = PengajuanCuti::getEventDispatcher();
        $salinan = clone $dispatcher;
        $salinan->listen('eloquent.updating: ' . PengajuanCuti::class, function ($record) {
            if ($record->status !== 'draft') {
                throw new RuntimeException('Simulasi gagal status akhir');
            }
        });
        PengajuanCuti::setEventDispatcher($salinan);
        try {
            submit($p);
            throw new RuntimeException('Submit seharusnya gagal');
        } catch (RuntimeException $e) {
            sama('Simulasi gagal status akhir', $e->getMessage());
        } finally {
            PengajuanCuti::setEventDispatcher($dispatcher);
        }
        tetapDraft($p, $before, $details);
    });
    uji('Submit tidak membuat surat atau SK', function () {
        $surat = SuratPermohonanCuti::count();
        $sk = SkCuti::count();
        submit(draft());
        sama($surat, SuratPermohonanCuti::count());
        sama($sk, SkCuti::count());
    });
    uji('Lock bentrok membaca pengajuan aktif', function () {
        $p = submit(draft());
        sama(1, count(app(BentrokCutiService::class)->cariBentrok($p->pegawai_id, ['2026-11-30'], null, true)));
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
