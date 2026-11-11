<?php

namespace Tests\Feature;

use App\Models\Divisi;
use App\Models\HasilPenilaian;
use App\Models\Jabatan;
use App\Models\Pegawai;
use App\Models\PeriodePenilaian;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\JenisPotonganSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HasilPenilaianDashboardTrendTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_results_dashboard_shows_period_trend_and_working_search_filters(): void
    {
        Carbon::setTestNow('2026-09-16 12:00:00');
        (new JenisPotonganSeeder)->run();

        $divisi = Divisi::create(['nama_divisi' => 'Operasional']);
        $jabatan = Jabatan::create([
            'divisi_id' => $divisi->id,
            'nama_jabatan' => 'Staf Operasional',
            'grade' => 'Grade 1',
            'base_tukin' => 1500000,
        ]);

        $atasanUser = User::create([
            'name' => 'Atasan Tren',
            'email' => 'atasan.tren@contoh.test',
            'password' => bcrypt('password'),
            'role' => 'atasan',
        ]);
        $atasan = Pegawai::create([
            'user_id' => $atasanUser->id,
            'nama' => 'Atasan Tren',
            'jabatan_id' => $jabatan->id,
            'tanggal_masuk' => '2020-01-01',
            'status' => 'aktif',
        ]);

        $pegawaiUser = User::create([
            'name' => 'Pegawai Tren',
            'email' => 'pegawai.tren@contoh.test',
            'password' => bcrypt('password'),
            'role' => 'pegawai',
        ]);
        $pegawai = Pegawai::create([
            'user_id' => $pegawaiUser->id,
            'nama' => 'Pegawai Tren',
            'jabatan_id' => $jabatan->id,
            'atasan_id' => $atasan->id,
            'tanggal_masuk' => '2021-01-01',
            'status' => 'aktif',
        ]);

        $periodeLalu = PeriodePenilaian::create([
            'nama_periode' => 'Agustus 2026',
            'tanggal_mulai' => '2026-08-01',
            'tanggal_selesai' => '2026-08-31',
            'status' => 'final',
        ]);
        $periodeAktif = PeriodePenilaian::create([
            'nama_periode' => 'September 2026',
            'tanggal_mulai' => '2026-09-01',
            'tanggal_selesai' => '2026-09-30',
            'status' => 'berjalan',
        ]);
        HasilPenilaian::create([
            'pegawai_id' => $pegawai->id,
            'periode_id' => $periodeLalu->id,
            'rata_skor_kpi' => 80,
            'nilai_akhir' => 80,
            'predikat' => 'Baik',
            'status' => 'dikirim',
        ]);

        $this->actingAs($atasanUser)
            ->get(route('atasan.hasil-penilaian.index', ['periode_id' => $periodeAktif->id]))
            ->assertOk()
            ->assertSee('Tren Skor Kinerja')
            ->assertSee('5 bulan')
            ->assertSee('data-result-filter="sangat baik"', false)
            ->assertSee('id="hasilSearch"', false)
            ->assertSee('Pegawai Tren');
    }
}
