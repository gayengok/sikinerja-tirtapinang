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
        $jenis = JenisCuti::create(['kode' => 'TEST-TUTUP-' . $code, 'nama' => 'Cuti Pengujian ' . $code, 'kuota_hari' => $days, 'periode_kuota' => $period, 'minimal_hari_pengajuan' => 0, 'boleh_hari_h' => true, 'metode_hitung_hari' => 'hari_kalender', 'minimal_masa_kerja_bulan' => 0, 'bisa_akumulasi' => false, 'tambahan_akumulasi_hari' => 0, 'maksimal_tahun_akumulasi' => 0, 'wajib_lampiran' => false, 'is_active' => true]);
        return $period === 'tahunan' ? $service->berikanKuotaTahunan($pegawai->id, $jenis->id, $y ?? $year) : $service->berikanKuotaNonTahunan($pegawai->id, $jenis->id);
    };
    DB::beginTransaction();
    try {
        $q = $make('NORMAL', 12, 'tahunan', $year - 1);
        $r = $service->tutupPeriodeKuota($q->id);
        $check('Penutupan historis nonaktif', !$r['sudah_tercatat'] && $q->refresh()->kuota_hangus === 12 && $q->sisa_kuota === 0 && !$q->is_active);
        $m = MutasiKuotaCuti::findOrFail($r['mutasi_id']);
        $check('Audit kedaluwarsa', $m->jenis_mutasi === 'kedaluwarsa' && $m->jumlah_hari === 12 && $m->perubahan_sisa === -12 && $m->user_id === null && $m->saldo_sebelum['sisa_kuota'] === 12 && $m->saldo_sesudah['sisa_kuota'] === 0);
        $retry = $service->tutupPeriodeKuota($q->id);
        $check('Idempotency', $retry['sudah_tercatat'] && $retry['mutasi_id'] === $r['mutasi_id'] && MutasiKuotaCuti::where('kuota_cuti_id', $q->id)->count() === 2);
        $active = $make('ACTIVE', 12, 'tahunan', $year - 1);
        $active->update(['is_active' => true]);
        $zero = $make('ZERO', 0, 'tahunan', $year - 1);
        $zero->update(['is_active' => true]);
        $current = $make('CURRENT');
        $unlimited = $make('UNLIMITED', null, 'tanpa_batas');
        $lifetime = $make('LIFETIME', 15, 'seumur_kerja');
        foreach ([$current, $unlimited, $lifetime] as $item) {
            $caught = false;
            try {
                $service->tutupPeriodeKuota($item->id);
            } catch (LogicException $e) {
                $caught = str_contains($e->getMessage(), 'belum dapat ditutup');
            }
            $check('Penolakan ' . $item->kode_periode, $caught && $item->refresh()->is_active);
        }
        $pending = $make('PENDING', 12, 'tahunan', $year - 1);
        $pending->update(['is_active' => true]);
        $date = $pending->tanggal_akhir_periode->toDateString();
        $p = PengajuanCuti::create(['nomor_pengajuan' => 'TEST-TUTUP-001', 'pegawai_id' => $pegawai->id, 'jenis_cuti_id' => $pending->jenis_cuti_id, 'role_pemohon' => 'pegawai', 'tanggal_mulai' => $date, 'tanggal_selesai' => $date, 'jumlah_hari_diajukan' => 1, 'alasan' => 'Simulasi', 'status' => 'draft', 'kebijakan_snapshot' => $pending->kebijakan_snapshot, 'pemohon_snapshot' => ['pegawai_id' => $pegawai->id]]);
        DetailPengajuanCuti::create(['pengajuan_cuti_id' => $p->id, 'kuota_cuti_id' => $pending->id, 'tanggal' => $date, 'is_hari_kerja' => true, 'dihitung_kuota' => true, 'status' => 'pending']);
        $service->reservasiPengajuan($p->id, $pegawai->user_id);
        $batch = $service->tutupKuotaKedaluwarsa();
        $check('Batch menutup kuota aktif dan nol', count($batch['ditutup']) === 2 && !$active->refresh()->is_active && $active->kuota_hangus === 12 && !$zero->refresh()->is_active && MutasiKuotaCuti::where('kuota_cuti_id', $zero->id)->count() === 1);
        $check('Batch menunda reservasi', count($batch['ditunda']) === 1 && $batch['ditunda'][0]['kuota_id'] === $pending->id && $pending->refresh()->kuota_direservasi === 1 && $pending->sisa_kuota === 11 && $pending->is_active);
        $check('Batch tanpa kegagalan', count($batch['gagal']) === 0);
        $batch = $service->tutupKuotaKedaluwarsa();
        $check('Batch ulang', count($batch['ditutup']) === 0 && count($batch['ditunda']) === 1 && count($batch['gagal']) === 0);
        $jenis = JenisCuti::create(['kode' => 'TEST-TUTUP-AKUM', 'nama' => 'Cuti Akumulasi', 'kuota_hari' => 12, 'periode_kuota' => 'tahunan', 'minimal_masa_kerja_bulan' => 0, 'bisa_akumulasi' => true, 'tambahan_akumulasi_hari' => 5, 'maksimal_tahun_akumulasi' => 2, 'tahun_awal_siklus' => $year - 2, 'is_active' => true]);
        $old = $service->berikanKuotaTahunan($pegawai->id, $jenis->id, $year - 2);
        $service->tutupPeriodeKuota($old->id);
        $mid = $service->berikanKuotaTahunan($pegawai->id, $jenis->id, $year - 1);
        $service->tutupPeriodeKuota($mid->id);
        $new = $service->berikanKuotaTahunan($pegawai->id, $jenis->id, $year);
        $check('Integrasi akumulasi 12-17-22', $old->refresh()->kuota_hangus === 12 && $mid->refresh()->kuota_hangus === 17 && $mid->kuota_akumulasi === 5 && $new->total_kuota === 22 && $new->kuota_akumulasi === 10);
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