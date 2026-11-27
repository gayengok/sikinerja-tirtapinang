<?php

use App\Models\{User, Pegawai, JenisCuti, KuotaCuti, MutasiKuotaCuti, PengajuanCuti, DetailPengajuanCuti};
use App\Services\Cuti\{KuotaCutiService, MutasiKuotaCutiService};
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

require __DIR__ . '/../../vendor/autoload.php';
$app = require __DIR__ . '/../../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$hasil = (function () {
    if (!app()->environment('local', 'testing') || DB::transactionLevel() !== 0) {
        throw new RuntimeException('Gunakan database lokal/testing tanpa transaksi aktif.');
    }
    $tables = ['jenis_cuti', 'kuota_cuti', 'pengajuan_cuti', 'detail_pengajuan_cuti', 'persetujuan_cuti', 'mutasi_kuota_cuti', 'tanda_tangan_pengguna', 'template_surat_cuti', 'surat_permohonan_cuti', 'sk_cuti'];
    $counts = fn() => array_map(fn($t) => DB::table($t)->count(), $tables);
    $before = $counts();
    if (array_sum($before) !== 0) throw new RuntimeException('Tabel cuti harus kosong untuk script ini.');
    $pass = 0;
    $fail = 0;
    $check = function ($name, $ok) use (&$pass, &$fail) {
        if ($ok) {
            $pass++;
            echo "PASS | $name\n";
        } else {
            $fail++;
            echo "FAIL | $name\n";
        }
    };
    $service = app(KuotaCutiService::class);
    $master = User::where('role', 'master')->firstOrFail();
    $pegawai = Pegawai::findOrFail(2);
    $year = (int) today()->year;
    $uuid = fn() => (string) Str::uuid();
    $make = function ($code, $days = 12, $period = 'tahunan', $y = null) use ($service, $pegawai, $year) {
        $jenis = JenisCuti::create(['kode' => 'TEST-KURANG-' . $code, 'nama' => 'Cuti Pengujian ' . $code, 'kuota_hari' => $days, 'periode_kuota' => $period, 'minimal_hari_pengajuan' => 0, 'boleh_hari_h' => true, 'metode_hitung_hari' => 'hari_kalender', 'minimal_masa_kerja_bulan' => 0, 'bisa_akumulasi' => false, 'tambahan_akumulasi_hari' => 0, 'maksimal_tahun_akumulasi' => 0, 'wajib_lampiran' => false, 'is_active' => true]);
        return $period === 'tahunan' ? $service->berikanKuotaTahunan($pegawai->id, $jenis->id, $y ?? $year) : $service->berikanKuotaNonTahunan($pegawai->id, $jenis->id);
    };
    $reduce = fn($q, $n, $reason = 'Pengujian', $ref = null, $actor = null) => $service->penguranganKuota($q->id, $n, $reason, $ref ?? $uuid(), $actor ?? $master->id);
    $reject = function ($name, $q, $call, $class, $message = '') use ($check) {
        $saldo = $q->refresh()->snapshotSaldo();
        $count = MutasiKuotaCuti::count();
        $caught = null;
        DB::beginTransaction();
        try {
            $call();
        } catch (Throwable $e) {
            $caught = $e;
        } finally {
            DB::rollBack();
        }
        $check($name, $caught instanceof $class && str_contains($caught->getMessage(), $message) && $q->refresh()->snapshotSaldo() === $saldo && MutasiKuotaCuti::count() === $count);
        if ($caught) echo '       ' . get_class($caught) . ': ' . $caught->getMessage() . "\n";
    };
    DB::beginTransaction();
    try {
        $q = $make('NORMAL');
        $ref = $uuid();
        $r = $reduce($q, 4, 'Pengujian', $ref);
        $check('Pengurangan normal', !$r['sudah_tercatat'] && $q->refresh()->kuota_penyesuaian === -4 && $q->sisa_kuota === 8 && $q->kuota_dasar === 12);
        $m = MutasiKuotaCuti::findOrFail($r['mutasi_id']);
        $check('Audit', $m->jenis_mutasi === 'penyesuaian_kurang' && $m->jumlah_hari === 4 && $m->perubahan_sisa === -4 && (int)$m->user_id === (int)$master->id && $m->saldo_sebelum['sisa_kuota'] === 12 && $m->saldo_sesudah['sisa_kuota'] === 8 && $m->metadata['referensi'] === $ref);
        $retry = $reduce($q, 4, 'Pengujian', $ref);
        $check('UUID sama', $retry['sudah_tercatat'] && $retry['mutasi_id'] === $r['mutasi_id'] && $q->refresh()->sisa_kuota === 8 && MutasiKuotaCuti::count() === 2);
        $reject('UUID sama jumlah berbeda', $q, fn() => $reduce($q, 5, 'Pengujian', $ref), LogicException::class, 'data berbeda');
        $reject('UUID sama alasan berbeda', $q, fn() => $reduce($q, 4, 'Berbeda', $ref), LogicException::class, 'data berbeda');
        $reduce($q, 2);
        $retry = $reduce($q, 4, 'Pengujian', $ref);
        $check('UUID baru dan retry lama', $retry['sudah_tercatat'] && $retry['mutasi_id'] === $r['mutasi_id'] && $q->refresh()->sisa_kuota === 6 && MutasiKuotaCuti::count() === 3);
        $start = today();
        $end = $start->copy()->addDays(1);
        if ($end->year !== $year) {
            $start = today()->subDay();
            $end = today();
        }
        $p = PengajuanCuti::create(['nomor_pengajuan' => 'TEST-KURANG-001', 'pegawai_id' => $pegawai->id, 'jenis_cuti_id' => $q->jenis_cuti_id, 'role_pemohon' => 'pegawai', 'tanggal_mulai' => $start, 'tanggal_selesai' => $end, 'jumlah_hari_diajukan' => 2, 'alasan' => 'Simulasi', 'status' => 'draft', 'kebijakan_snapshot' => $q->kebijakan_snapshot, 'pemohon_snapshot' => ['pegawai_id' => $pegawai->id]]);
        foreach ([$start, $end] as $date) DetailPengajuanCuti::create(['pengajuan_cuti_id' => $p->id, 'kuota_cuti_id' => $q->id, 'tanggal' => $date, 'is_hari_kerja' => true, 'dihitung_kuota' => true, 'status' => 'pending']);
        $service->reservasiPengajuan($p->id, $pegawai->user_id);
        $reduce($q, 1);
        $check('Pengurangan dengan reservasi', $q->refresh()->kuota_direservasi === 2 && $q->sisa_kuota === 3 && $p->details()->where('status', 'pending')->count() === 2);
        $reject('Melebihi sisa', $q, fn() => $reduce($q, 4), LogicException::class, 'melebihi sisa');
        $reduce($q, 3);
        $check('Pengurangan tepat sisa', $q->refresh()->sisa_kuota === 0 && $q->total_kuota === 2 && $q->kuota_direservasi === 2);
        $reject('Saldo nol', $q, fn() => $reduce($q, 1), LogicException::class, 'melebihi sisa');
        $u = $make('UNLIMITED', null, 'tanpa_batas');
        $reject('Tanpa batas', $u, fn() => $reduce($u, 1), LogicException::class, 'tanpa batas');
        $inactive = $make('NONAKTIF');
        $inactive->update(['is_active' => false]);
        $reject('Nonaktif', $inactive, fn() => $reduce($inactive, 1), LogicException::class, 'Periode kuota');
        $expired = $make('EXPIRED', 12, 'tahunan', $year - 1);
        $expired->update(['is_active' => true]);
        $reject('Kedaluwarsa', $expired, fn() => $reduce($expired, 1), LogicException::class, 'Periode kuota');
        $v = $make('VALIDASI');
        foreach ([0, -1, 2147483648] as $n) $reject('Jumlah invalid ' . $n, $v, fn() => $reduce($v, $n), InvalidArgumentException::class, 'Jumlah pengurangan');
        foreach (['', '   ', str_repeat('a', 65536)] as $i => $reason) $reject('Alasan invalid ' . ($i + 1), $v, fn() => $reduce($v, 1, $reason), InvalidArgumentException::class, 'Alasan pengurangan');
        foreach (['', 'INVALID'] as $i => $bad) $reject('UUID invalid ' . ($i + 1), $v, fn() => $reduce($v, 1, 'Pengujian', $bad), InvalidArgumentException::class, 'UUID');
        foreach (['pegawai', 'atasan', 'kepala_bagian', 'direktur'] as $role) {
            $actor = User::where('role', $role)->firstOrFail();
            $reject('Akses ' . $role, $v, fn() => $reduce($v, 1, 'Pengujian', null, $actor->id), LogicException::class, 'hanya dapat dilakukan oleh Master');
        }
        $missing = (int) User::max('id') + 1;
        $reject('Aktor tidak ditemukan', $v, fn() => $reduce($v, 1, 'Pengujian', null, $missing), Illuminate\Database\Eloquent\ModelNotFoundException::class);
        $check('Transaksi utama aktif', DB::transactionLevel() === 1);
    } catch (Throwable $e) {
        $check('Eksekusi suite: ' . get_class($e) . ': ' . $e->getMessage(), false);
    } finally {
        while (DB::transactionLevel() > 0) DB::rollBack();
    }
    $check('Rollback dan jumlah tabel kembali semula', DB::transactionLevel() === 0 && $counts() === $before);
    echo "\nTOTAL: " . ($pass + $fail) . " | PASS: $pass | FAIL: $fail\n";
    return ['pass' => $pass, 'fail' => $fail, 'transaction_level' => DB::transactionLevel(), 'counts' => array_combine($tables, $counts())];
})();

exit($hasil['fail'] > 0 ? 1 : 0);