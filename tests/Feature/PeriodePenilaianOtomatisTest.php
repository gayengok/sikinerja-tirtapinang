<?php

namespace Tests\Feature;

use App\Models\Divisi;
use App\Models\Jabatan;
use App\Models\Pegawai;
use App\Models\PeriodePenilaian;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PeriodePenilaianOtomatisTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_remains_available_when_master_has_not_created_any_schedule(): void
    {
        Carbon::setTestNow('2026-10-01 08:00:00');

        try {
            [$user] = $this->createPegawai();

            $this->actingAs($user)
                ->get(route('pegawai.dashboard'))
                ->assertOk()
                ->assertSee('Selamat Datang, Pegawai Uji')
                ->assertSee('Belum tersedia')
                ->assertDontSee('Dashboard terkunci')
                ->assertDontSee('inert aria-hidden="true"', false);
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_expired_running_period_is_finalized_and_dashboard_remains_available(): void
    {
        Carbon::setTestNow('2026-10-01 08:00:00');

        try {
            [$user] = $this->createPegawai();
            $periode = PeriodePenilaian::create([
                'nama_periode' => 'Penilaian September 2026',
                'tanggal_mulai' => '2026-09-01',
                'tanggal_selesai' => '2026-09-30',
                'status' => 'berjalan',
            ]);

            $response = $this->actingAs($user)->get(route('pegawai.dashboard'));

            $response->assertOk()
                ->assertSee('Selamat Datang, Pegawai Uji')
                ->assertSee('Selesai')
                ->assertDontSee('Dashboard terkunci')
                ->assertDontSee('inert aria-hidden="true"', false);
            $this->assertDatabaseHas('periode_penilaians', [
                'id' => $periode->id,
                'status' => 'final',
            ]);
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_dashboard_remains_available_when_next_period_has_been_scheduled(): void
    {
        Carbon::setTestNow('2026-10-01 08:00:00');

        try {
            [$user] = $this->createPegawai();
            PeriodePenilaian::create([
                'nama_periode' => 'Penilaian September 2026',
                'tanggal_mulai' => '2026-09-01',
                'tanggal_selesai' => '2026-09-30',
                'status' => 'berjalan',
            ]);
            PeriodePenilaian::create([
                'nama_periode' => 'Penilaian Oktober 2026',
                'tanggal_mulai' => '2026-10-01',
                'tanggal_selesai' => '2026-10-31',
                'status' => 'draft',
            ]);

            $this->actingAs($user)
                ->get(route('pegawai.dashboard'))
                ->assertOk()
                ->assertSee('Selamat Datang, Pegawai Uji')
                ->assertSee('Tersedia')
                ->assertDontSee('Dashboard terkunci');
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_period_remains_running_until_the_end_date_has_passed(): void
    {
        Carbon::setTestNow('2026-09-30 23:59:59');

        try {
            $periode = PeriodePenilaian::create([
                'nama_periode' => 'Penilaian September 2026',
                'tanggal_mulai' => '2026-09-01',
                'tanggal_selesai' => '2026-09-30',
                'status' => 'berjalan',
            ]);

            PeriodePenilaian::finalizeExpired();

            $this->assertDatabaseHas('periode_penilaians', [
                'id' => $periode->id,
                'status' => 'berjalan',
            ]);
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_master_period_index_finalizes_expired_periods_before_rendering_status_counts(): void
    {
        Carbon::setTestNow('2026-10-01 08:00:00');

        try {
            $master = User::create([
                'name' => 'Master Uji',
                'email' => 'master.periode@contoh.test',
                'password' => bcrypt('password'),
                'role' => 'master',
            ]);
            $periode = PeriodePenilaian::create([
                'nama_periode' => 'Penilaian September 2026',
                'tanggal_mulai' => '2026-09-01',
                'tanggal_selesai' => '2026-09-30',
                'status' => 'berjalan',
            ]);

            $this->actingAs($master)
                ->get(route('master.periode-penilaian.index'))
                ->assertOk()
                ->assertSee('Selesai (1)');

            $this->assertDatabaseHas('periode_penilaians', [
                'id' => $periode->id,
                'status' => 'final',
            ]);
        } finally {
            Carbon::setTestNow();
        }
    }

    private function createPegawai(): array
    {
        $divisi = Divisi::create(['nama_divisi' => 'Divisi Uji']);
        $jabatan = Jabatan::create([
            'divisi_id' => $divisi->id,
            'nama_jabatan' => 'Pegawai Uji',
            'grade' => 'Grade 1',
        ]);
        $user = User::create([
            'name' => 'Pegawai Uji',
            'email' => 'pegawai.periode@contoh.test',
            'password' => bcrypt('password'),
            'role' => 'pegawai',
        ]);
        Pegawai::create([
            'user_id' => $user->id,
            'nama' => 'Pegawai Uji',
            'jabatan_id' => $jabatan->id,
            'status' => 'aktif',
        ]);

        return [$user];
    }
}
