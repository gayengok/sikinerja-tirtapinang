<?php

namespace Tests\Feature;

use App\Models\Divisi;
use App\Models\Jabatan;
use App\Models\LaporanHarian;
use App\Models\Pegawai;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApprovalLaporanBulananAtasanTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_approval_list_defaults_to_current_month_and_can_show_an_older_month(): void
    {
        Carbon::setTestNow('2026-11-03 12:00:00');

        $divisi = Divisi::create(['nama_divisi' => 'Operasional']);
        $jabatan = Jabatan::create([
            'divisi_id' => $divisi->id,
            'nama_jabatan' => 'Staf Operasional',
            'grade' => 'Grade 1',
            'base_tukin' => 1500000,
        ]);

        $atasanUser = User::create([
            'name' => 'Atasan Bulanan',
            'email' => 'atasan.bulanan@contoh.test',
            'password' => bcrypt('password'),
            'role' => 'atasan',
        ]);
        $atasan = Pegawai::create([
            'user_id' => $atasanUser->id,
            'nama' => 'Atasan Bulanan',
            'jabatan_id' => $jabatan->id,
            'tanggal_masuk' => '2020-01-01',
            'status' => 'aktif',
        ]);

        $pegawaiUser = User::create([
            'name' => 'Pegawai Bulanan',
            'email' => 'pegawai.bulanan@contoh.test',
            'password' => bcrypt('password'),
            'role' => 'pegawai',
        ]);
        $pegawai = Pegawai::create([
            'user_id' => $pegawaiUser->id,
            'nama' => 'Pegawai Bulanan',
            'jabatan_id' => $jabatan->id,
            'atasan_id' => $atasan->id,
            'tanggal_masuk' => '2021-01-01',
            'status' => 'aktif',
        ]);

        LaporanHarian::create([
            'pegawai_id' => $pegawai->id,
            'tanggal' => '2026-11-03',
            'catatan' => 'Kegiatan Bulan November',
            'status' => 'diajukan',
        ]);
        LaporanHarian::create([
            'pegawai_id' => $pegawai->id,
            'tanggal' => '2026-09-30',
            'catatan' => 'Kegiatan Bulan September',
            'status' => 'diajukan',
        ]);

        $this->actingAs($atasanUser)
            ->get(route('atasan.laporan.index'))
            ->assertOk()
            ->assertSee('Laporan November 2026')
            ->assertSee('Kegiatan Bulan November')
            ->assertDontSee('Kegiatan Bulan September');

        $this->actingAs($atasanUser)
            ->get(route('atasan.laporan.index', ['bulan' => '2026-09']))
            ->assertOk()
            ->assertSee('Laporan September 2026')
            ->assertSee('Kegiatan Bulan September')
            ->assertDontSee('Kegiatan Bulan November');
    }
}
