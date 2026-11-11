<?php

namespace Tests\Feature;

use App\Models\Absensi;
use App\Models\Divisi;
use App\Models\Jabatan;
use App\Models\MasterJenisPotongan;
use App\Models\MasterJamKerja;
use App\Models\Pegawai;
use App\Models\PeriodePenilaian;
use App\Models\PresensiPotongan;
use App\Models\User;
use App\Services\PresensiPotonganService;
use Carbon\Carbon;
use Database\Seeders\JenisPotonganSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PresensiPotonganServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_calculates_late_early_exit_and_unexcused_absence_rules_idempotently(): void
    {
        Carbon::setTestNow('2026-09-30 12:00:00');

        try {
            [$pegawai, $periode] = $this->createPayrollData();
            $this->createSchedule($pegawai);
            Absensi::create([
                'pegawai_id' => $pegawai->id,
                'tanggal' => '2026-09-28',
                'jam_masuk' => '08:10:00',
                'jam_pulang' => '15:30:00',
                'status' => 'hadir',
            ]);
            $service = app(PresensiPotonganService::class);
            $service->syncForPegawai($pegawai, $periode);
            $service->syncForPegawai($pegawai, $periode);

            $potongans = PresensiPotongan::with('jenisPotongan')
                ->where('pegawai_id', $pegawai->id)
                ->where('periode_id', $periode->id)
                ->get()
                ->keyBy(fn (PresensiPotongan $potongan) => $potongan->jenisPotongan->nama_potongan);

            $this->assertCount(3, $potongans);
            $this->assertSame(1, $potongans['Terlambat Ringan']->jumlah_kejadian);
            $this->assertEquals(1, (float) $potongans['Terlambat Ringan']->persen_potongan);
            $this->assertSame(1, $potongans['Pulang Sebelum Waktu (Ringan)']->jumlah_kejadian);
            $this->assertEquals(1, (float) $potongans['Pulang Sebelum Waktu (Ringan)']->persen_potongan);
            $this->assertSame(1, $potongans['Tanpa Keterangan']->jumlah_kejadian);
            $this->assertEquals(5, (float) $potongans['Tanpa Keterangan']->persen_potongan);
            $this->assertEquals(7, (float) $potongans->sum('persen_potongan'));
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_each_missing_clock_event_is_counted_separately(): void
    {
        Carbon::setTestNow('2026-09-29 17:00:00');

        try {
            [$pegawai, $periode] = $this->createPayrollData('2026-09-28', '2026-09-28');
            $this->createSchedule($pegawai);
            Absensi::create([
                'pegawai_id' => $pegawai->id,
                'tanggal' => '2026-09-28',
                'status' => 'hadir',
            ]);

            app(PresensiPotonganService::class)->syncForPegawai($pegawai, $periode);

            $potongan = PresensiPotongan::with('jenisPotongan')
                ->where('pegawai_id', $pegawai->id)
                ->where('periode_id', $periode->id)
                ->firstOrFail();

            $this->assertSame('Tidak Absen', $potongan->jenisPotongan->nama_potongan);
            $this->assertSame(2, $potongan->jumlah_kejadian);
            $this->assertEquals(2, (float) $potongan->persen_potongan);
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_it_accepts_legacy_alpa_rule_when_tanpa_keterangan_is_not_configured_yet(): void
    {
        Carbon::setTestNow('2026-09-29 17:00:00');

        try {
            [$pegawai, $periode] = $this->createPayrollData('2026-09-28', '2026-09-28');
            $this->createSchedule($pegawai);
            MasterJenisPotongan::where('nama_potongan', 'Tanpa Keterangan')
                ->update(['nama_potongan' => 'Alpa']);

            app(PresensiPotonganService::class)->syncForPegawai($pegawai, $periode);

            $potongan = PresensiPotongan::with('jenisPotongan')
                ->where('pegawai_id', $pegawai->id)
                ->where('periode_id', $periode->id)
                ->firstOrFail();

            $this->assertSame('Alpa', $potongan->jenisPotongan->nama_potongan);
            $this->assertSame(1, $potongan->jumlah_kejadian);
            $this->assertEquals(5, (float) $potongan->persen_potongan);
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_it_applies_medium_and_heavy_late_rules_and_exempts_authorized_leave(): void
    {
        Carbon::setTestNow('2026-10-01 18:00:00');

        try {
            [$pegawai, $periode] = $this->createPayrollData('2026-09-28', '2026-09-30');
            $this->createSchedule($pegawai);
            Absensi::create([
                'pegawai_id' => $pegawai->id,
                'tanggal' => '2026-09-28',
                'jam_masuk' => '08:11:00',
                'jam_pulang' => '15:29:00',
                'status' => 'hadir',
            ]);
            Absensi::create([
                'pegawai_id' => $pegawai->id,
                'tanggal' => '2026-09-29',
                'jam_masuk' => '08:21:00',
                'jam_pulang' => '16:00:00',
                'status' => 'hadir',
            ]);
            Absensi::create([
                'pegawai_id' => $pegawai->id,
                'tanggal' => '2026-09-30',
                'status' => 'izin',
            ]);

            app(PresensiPotonganService::class)->syncForPegawai($pegawai, $periode);

            $potongans = PresensiPotongan::with('jenisPotongan')
                ->where('pegawai_id', $pegawai->id)
                ->where('periode_id', $periode->id)
                ->get()
                ->keyBy(fn (PresensiPotongan $potongan) => $potongan->jenisPotongan->nama_potongan);

            $this->assertCount(3, $potongans);
            $this->assertEquals(2, (float) $potongans['Terlambat Sedang']->persen_potongan);
            $this->assertEquals(5, (float) $potongans['Terlambat Berat']->persen_potongan);
            $this->assertEquals(5, (float) $potongans['Pulang Sebelum Waktu (Berat)']->persen_potongan);
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_totals_match_the_attendance_table_example_after_rounding_each_category(): void
    {
        Carbon::setTestNow('2026-10-01 18:00:00');

        try {
            [$pegawai, $periode] = $this->createPayrollData('2026-09-28', '2026-09-30');
            $this->createSchedule($pegawai);
            Absensi::create([
                'pegawai_id' => $pegawai->id,
                'tanggal' => '2026-09-28',
                'jam_masuk' => '08:10:00',
                'status' => 'hadir',
            ]);
            Absensi::create([
                'pegawai_id' => $pegawai->id,
                'tanggal' => '2026-09-29',
                'status' => 'hadir',
            ]);

            app(PresensiPotonganService::class)->syncForPegawai($pegawai, $periode);

            $potongans = PresensiPotongan::with('jenisPotongan')
                ->where('pegawai_id', $pegawai->id)
                ->where('periode_id', $periode->id)
                ->get()
                ->keyBy(fn (PresensiPotongan $potongan) => $potongan->jenisPotongan->nama_potongan);

            $this->assertSame(3, $potongans['Tidak Absen']->jumlah_kejadian);
            $this->assertEquals(3, (float) $potongans['Tidak Absen']->persen_potongan);
            $this->assertEquals(1, (float) $potongans['Terlambat Ringan']->persen_potongan);
            $this->assertEquals(5, (float) $potongans['Tanpa Keterangan']->persen_potongan);
            $this->assertEquals(9, (float) $potongans->sum('persen_potongan'));
        } finally {
            Carbon::setTestNow();
        }
    }

    private function createPayrollData(string $mulai = '2026-09-28', string $selesai = '2026-09-29'): array
    {
        (new JenisPotonganSeeder)->run();

        $divisi = Divisi::create(['nama_divisi' => 'Divisi Uji']);
        $jabatan = Jabatan::create([
            'divisi_id' => $divisi->id,
            'nama_jabatan' => 'Pegawai Uji',
            'grade' => 'Grade 1',
            'base_tukin' => 1500000,
        ]);
        $user = User::create([
            'name' => 'Pegawai Uji',
            'email' => 'presensi-potongan@contoh.test',
            'password' => bcrypt('password'),
            'role' => 'pegawai',
        ]);
        $pegawai = Pegawai::create([
            'user_id' => $user->id,
            'nama' => 'Pegawai Uji',
            'jabatan_id' => $jabatan->id,
            'tanggal_masuk' => '2021-01-01',
            'status' => 'aktif',
        ]);
        $periode = PeriodePenilaian::create([
            'nama_periode' => 'Periode Uji',
            'tanggal_mulai' => $mulai,
            'tanggal_selesai' => $selesai,
            'status' => 'berjalan',
        ]);

        return [$pegawai, $periode];
    }

    private function createSchedule(Pegawai $pegawai): void
    {
        foreach (['Senin', 'Selasa', 'Rabu'] as $hari) {
            MasterJamKerja::create([
                'jabatan_id' => $pegawai->jabatan_id,
                'hari' => $hari,
                'jam_masuk' => '08:00:00',
                'jam_pulang' => '16:00:00',
                'toleransi_menit' => 0,
            ]);
        }
    }
}
