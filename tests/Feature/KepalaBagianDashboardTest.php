<?php

namespace Tests\Feature;

use App\Models\Absensi;
use App\Models\Divisi;
use App\Models\HasilPenilaian;
use App\Models\Jabatan;
use App\Models\LaporanHarian;
use App\Models\MasterKompetensi;
use App\Models\MasterKpi;
use App\Models\Pegawai;
use App\Models\PeriodePenilaian;
use App\Models\User;
use Database\Seeders\KepalaBagianSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class KepalaBagianDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_kepala_bagian_can_log_in_view_dashboard_and_assigned_atasan(): void
    {
        [$kepalaBagianUser, $atasan] = $this->createHierarchy();

        $this->post(route('kepala_bagian.login.store'), [
            'email' => $kepalaBagianUser->email,
            'password' => 'password',
        ])->assertRedirect(route('kepala_bagian.dashboard'));

        $this->get(route('kepala_bagian.dashboard'))
            ->assertOk()
            ->assertSee('Dashboard Kepala Bagian')
            ->assertSee('Atasan yang Dinilai')
            ->assertSee(route('kepala_bagian.absensi.index'), false)
            ->assertSee(route('kepala_bagian.laporan.index', ['status' => 'diajukan']), false)
            ->assertSee('Pegawai Atasan');

        $this->get(route('kepala_bagian.penilaian-atasan.index'))
            ->assertOk()
            ->assertSee($atasan->nama)
            ->assertSee('Daftar Atasan yang Dinilai');
    }

    public function test_kepala_bagian_cannot_assess_atasan_outside_its_reporting_line(): void
    {
        [$kepalaBagianUser] = $this->createHierarchy();
        $jabatan = Jabatan::firstOrFail();
        $otherAtasanUser = User::create([
            'name' => 'Atasan Lain',
            'email' => 'atasan.lain@example.test',
            'password' => bcrypt('password'),
            'role' => 'atasan',
        ]);
        $otherAtasan = Pegawai::create([
            'user_id' => $otherAtasanUser->id,
            'nama' => 'Atasan Lain',
            'jabatan_id' => $jabatan->id,
            'atasan_id' => null,
            'status' => 'aktif',
        ]);

        $this->actingAs($kepalaBagianUser)
            ->get(route('kepala_bagian.penilaian-atasan.show', $otherAtasan))
            ->assertForbidden();
    }

    public function test_kepala_bagian_kpi_assessment_only_lists_active_atasan_not_employee_subordinates(): void
    {
        [$kepalaBagianUser, $atasan] = $this->createHierarchy();
        $jabatan = Jabatan::firstOrFail();

        $pegawaiUser = User::create([
            'name' => 'Pegawai Bawahan Uji',
            'email' => 'pegawai.bawahan.uji@example.test',
            'password' => bcrypt('password'),
            'role' => 'pegawai',
        ]);
        Pegawai::create([
            'user_id' => $pegawaiUser->id,
            'nama' => 'Pegawai Bawahan Uji',
            'jabatan_id' => $jabatan->id,
            'atasan_id' => $atasan->id,
            'status' => 'aktif',
        ]);

        $inactiveAtasanUser = User::create([
            'name' => 'Atasan Nonaktif Uji',
            'email' => 'atasan.nonaktif.uji@example.test',
            'password' => bcrypt('password'),
            'role' => 'atasan',
        ]);
        $inactiveAtasan = Pegawai::create([
            'user_id' => $inactiveAtasanUser->id,
            'nama' => 'Atasan Nonaktif Uji',
            'jabatan_id' => $jabatan->id,
            'atasan_id' => $kepalaBagianUser->pegawai->id,
            'status' => 'nonaktif',
        ]);

        $response = $this->actingAs($kepalaBagianUser)
            ->get(route('kepala_bagian.penilaian-atasan.index'))
            ->assertOk()
            ->assertSee($atasan->nama)
            ->assertSee('Menampilkan 1 dari 1 pegawai')
            ->assertDontSee('Pegawai Bawahan Uji')
            ->assertDontSee('Atasan Nonaktif Uji');

        $this->assertSame(1, substr_count($response->getContent(), 'class="kepala-kpi-row"'));

        $this->get(route('kepala_bagian.penilaian-atasan.create', $inactiveAtasan))
            ->assertForbidden();
    }

    public function test_non_kepala_bagian_cannot_use_kepala_bagian_login(): void
    {
        $pegawaiUser = User::create([
            'name' => 'Pegawai',
            'email' => 'pegawai.login@example.test',
            'password' => bcrypt('password'),
            'role' => 'pegawai',
        ]);

        $this->from(route('kepala_bagian.login'))
            ->post(route('kepala_bagian.login.store'), [
                'email' => $pegawaiUser->email,
                'password' => 'password',
            ])
            ->assertRedirect(route('kepala_bagian.login'))
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_master_can_create_a_kepala_bagian_and_assign_an_atasan_to_them(): void
    {
        $divisi = Divisi::create(['nama_divisi' => 'Divisi Uji']);
        $jabatan = Jabatan::create([
            'divisi_id' => $divisi->id,
            'nama_jabatan' => 'Jabatan Uji',
            'grade' => 'Grade 1',
            'base_tukin' => 1000000,
        ]);
        $master = User::create([
            'name' => 'Master',
            'email' => 'master@example.test',
            'password' => bcrypt('password'),
            'role' => 'master',
        ]);

        $this->actingAs($master)
            ->post(route('master.pegawai.store'), [
                'nama' => 'Kepala Bagian Baru',
                'email' => 'kabag.baru@example.test',
                'password' => 'password123',
                'role' => 'kepala_bagian',
                'jabatan_id' => $jabatan->id,
                'atasan_id' => '',
                'status' => 'aktif',
            ])
            ->assertRedirect(route('master.pegawai.index'));

        $kepalaBagian = Pegawai::where('nama', 'Kepala Bagian Baru')->firstOrFail();

        $this->actingAs($master)
            ->post(route('master.pegawai.store'), [
                'nama' => 'Atasan Baru',
                'email' => 'atasan.baru@example.test',
                'password' => 'password123',
                'role' => 'atasan',
                'jabatan_id' => $jabatan->id,
                'atasan_id' => $kepalaBagian->id,
                'status' => 'aktif',
            ])
            ->assertRedirect(route('master.pegawai.index'));

        $this->assertDatabaseHas('pegawais', [
            'nama' => 'Atasan Baru',
            'atasan_id' => $kepalaBagian->id,
        ]);
    }

    public function test_kepala_bagian_can_rate_an_assigned_atasans_kpi_and_competencies(): void
    {
        [$kepalaBagianUser, $atasan] = $this->createHierarchy();
        $periode = PeriodePenilaian::create([
            'nama_periode' => 'Periode Uji',
            'tanggal_mulai' => '2026-01-01',
            'tanggal_selesai' => '2026-12-31',
            'status' => 'berjalan',
        ]);
        $masterKpi = MasterKpi::create([
            'jabatan_id' => $atasan->jabatan_id,
            'indikator' => 'Target Atasan',
            'target_default' => 100,
            'bobot' => 100,
            'arah_capaian' => 'maksimal',
            'urutan' => 1,
        ]);
        $masterKompetensi = MasterKompetensi::create([
            'jabatan_id' => null,
            'nama_kompetensi' => 'Kepemimpinan',
            'definisi_perilaku' => 'Mengarahkan tim',
            'urutan' => 1,
        ]);

        $this->actingAs($kepalaBagianUser)
            ->get(route('kepala_bagian.penilaian-atasan.index'))
            ->assertOk()
            ->assertSee('Daftar Atasan yang Dinilai')
            ->assertSee('Filter Unit')
            ->assertSee('Ekspor Rekap')
            ->assertSee('0 dari 1 butir indikator')
            ->assertSee(route('kepala_bagian.penilaian-atasan.create', $atasan), false);

        $this->actingAs($kepalaBagianUser)
            ->get(route('kepala_bagian.penilaian-atasan.create', [
                'pegawai' => $atasan,
                'periode_id' => $periode->id,
            ]))
            ->assertOk()
            ->assertSee('ATASAN YANG DINILAI')
            ->assertSee('Target Atasan');

        $this->post(route('kepala_bagian.penilaian-atasan.store', $atasan), [
            'periode_id' => $periode->id,
            'kpi' => [[
                'master_kpi_id' => $masterKpi->id,
                'target' => 100,
                'realisasi_final' => 90,
                'skor' => 4,
                'keterangan' => 'Target tercapai',
            ]],
        ])->assertRedirect(route('kepala_bagian.penilaian-atasan.show', $atasan));

        $this->assertDatabaseHas('penilaian_kpis', [
            'pegawai_id' => $atasan->id,
            'periode_id' => $periode->id,
            'master_kpi_id' => $masterKpi->id,
            'skor' => 4,
        ]);

        $hasilTunjangan = HasilPenilaian::where('pegawai_id', $atasan->id)
            ->where('periode_id', $periode->id)
            ->firstOrFail();

        $this->get(route('kepala_bagian.penilaian-atasan.show', [
            'pegawai' => $atasan,
            'periode_id' => $periode->id,
        ]))
            ->assertOk()
            ->assertSee('Hasil Penilaian KPI')
            ->assertSee('Capaian KPI tertimbang')
            ->assertSee('90,00%')
            ->assertSee('Rincian Tunjangan Kinerja')
            ->assertSee('Base Tukin Jabatan')
            ->assertSee('TUKIN NETO DITERIMA')
            ->assertSee('Rp ' . number_format((float) $hasilTunjangan->tukin_bruto, 0, ',', '.'))
            ->assertSee('Rp ' . number_format((float) $hasilTunjangan->tukin_neto, 0, ',', '.'))
            ->assertDontSee('Rincian Indikator Kinerja')
            ->assertDontSee('Target Atasan')
            ->assertDontSee('Edit')
            ->assertSee('Cetak PDF');

        $this->assertFalse(Route::has('kepala_bagian.penilaian-atasan.edit'));
        $this->assertFalse(Route::has('kepala_bagian.penilaian-atasan.update'));

        $this->get(route('kepala_bagian.penilaian-atasan.kompetensi.edit', [
            'pegawai' => $atasan,
            'periode_id' => $periode->id,
        ]))
            ->assertOk()
            ->assertSee('Kepemimpinan');

        $this->put(route('kepala_bagian.penilaian-atasan.kompetensi.update', $atasan), [
            'periode_id' => $periode->id,
            'kompetensi' => [[
                'master_kompetensi_id' => $masterKompetensi->id,
                'skor' => 5,
                'catatan' => 'Baik',
            ]],
        ])->assertRedirect(route('kepala_bagian.penilaian-atasan.show', [
            'pegawai' => $atasan,
            'periode_id' => $periode->id,
        ]));

        $this->assertDatabaseHas('penilaian_kompetensis', [
            'pegawai_id' => $atasan->id,
            'periode_id' => $periode->id,
            'master_kompetensi_id' => $masterKompetensi->id,
            'skor' => 5,
        ]);
    }

    public function test_kepala_bagian_seeder_creates_account_profile_and_reporting_line(): void
    {
        $divisi = Divisi::create(['nama_divisi' => 'Divisi Seeder']);
        $jabatan = Jabatan::create([
            'divisi_id' => $divisi->id,
            'nama_jabatan' => 'Kepala Seksi Seeder',
            'grade' => 'Grade 3',
            'base_tukin' => 2500000,
        ]);
        $atasanUser = User::create([
            'name' => 'Atasan Seeder',
            'email' => 'atasan@sikinerja.test',
            'password' => bcrypt('password'),
            'role' => 'atasan',
        ]);
        $atasan = Pegawai::create([
            'user_id' => $atasanUser->id,
            'nama' => 'Atasan Seeder',
            'jabatan_id' => $jabatan->id,
            'atasan_id' => null,
            'status' => 'aktif',
        ]);

        app(KepalaBagianSeeder::class)->run();

        $kepalaBagianUser = User::where('email', 'kabag@sikinerja.test')->firstOrFail();
        $kepalaBagian = $kepalaBagianUser->pegawai;

        $this->assertSame('kepala_bagian', $kepalaBagianUser->role);
        $this->assertNotNull($kepalaBagian);
        $this->assertSame($kepalaBagian->id, $atasan->fresh()->atasan_id);
        $this->assertSame($divisi->id, $kepalaBagian->jabatan->divisi_id);
    }

    public function test_login_pages_use_clear_names_for_each_role(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('Masuk Pegawai');

        $this->get(route('master.login'))
            ->assertOk()
            ->assertSee('Masuk Admin');

        $this->get(route('atasan.login'))
            ->assertOk()
            ->assertSee('Masuk Atasan');

        $this->get(route('kepala_bagian.login'))
            ->assertOk()
            ->assertSee('Masuk Kepala Bagian');
    }

    public function test_employee_management_displays_separate_role_counts_and_hierarchy(): void
    {
        [, $atasan] = $this->createHierarchy();
        $pegawaiUser = User::create([
            'name' => 'Pegawai Uji',
            'email' => 'pegawai.hirarki@example.test',
            'password' => bcrypt('password'),
            'role' => 'pegawai',
        ]);
        Pegawai::create([
            'user_id' => $pegawaiUser->id,
            'nama' => 'Pegawai Uji',
            'jabatan_id' => $atasan->jabatan_id,
            'atasan_id' => $atasan->id,
            'status' => 'aktif',
        ]);
        $adminUser = User::create([
            'name' => 'Admin Uji',
            'email' => 'admin.uji@example.test',
            'password' => bcrypt('password'),
            'role' => 'master',
        ]);

        $this->actingAs($adminUser)
            ->get(route('master.pegawai.index'))
            ->assertOk()
            ->assertSee('Atasan')
            ->assertSee('Kepala Bagian')
            ->assertSee('Pegawai')
            ->assertSee('Menilai Pegawai')
            ->assertSee('Menilai Atasan');
    }

    public function test_kepala_bagian_can_review_team_attendance_and_approve_reports(): void
    {
        [$kepalaBagianUser, $atasan] = $this->createHierarchy();
        $pegawaiUser = User::create([
            'name' => 'Pegawai Tim',
            'email' => 'pegawai.tim@example.test',
            'password' => bcrypt('password'),
            'role' => 'pegawai',
        ]);
        $pegawai = Pegawai::create([
            'user_id' => $pegawaiUser->id,
            'nama' => 'Pegawai Tim',
            'jabatan_id' => $atasan->jabatan_id,
            'atasan_id' => $atasan->id,
            'status' => 'aktif',
        ]);
        $absensi = Absensi::create([
            'pegawai_id' => $pegawai->id,
            'tanggal' => now()->toDateString(),
            'status' => 'hadir',
        ]);
        $absensiAtasan = Absensi::create([
            'pegawai_id' => $atasan->id,
            'tanggal' => now()->toDateString(),
            'status' => 'hadir',
        ]);
        $pegawaiTanpaAbsensiUser = User::create([
            'name' => 'Pegawai Belum Absen',
            'email' => 'pegawai.belum.absen@example.test',
            'password' => bcrypt('password'),
            'role' => 'pegawai',
        ]);
        Pegawai::create([
            'user_id' => $pegawaiTanpaAbsensiUser->id,
            'nama' => 'Pegawai Belum Absen',
            'jabatan_id' => $atasan->jabatan_id,
            'atasan_id' => $atasan->id,
            'status' => 'aktif',
        ]);
        $laporan = LaporanHarian::create([
            'pegawai_id' => $pegawai->id,
            'tanggal' => now()->toDateString(),
            'catatan' => 'Laporan pekerjaan hari ini',
            'status' => 'diajukan',
        ]);
        $periode = PeriodePenilaian::create([
            'nama_periode' => 'Periode Uji Tim',
            'tanggal_mulai' => now()->startOfMonth()->toDateString(),
            'tanggal_selesai' => now()->endOfMonth()->toDateString(),
            'status' => 'berjalan',
        ]);
        HasilPenilaian::create([
            'pegawai_id' => $pegawai->id,
            'periode_id' => $periode->id,
            'nilai_akhir' => 92,
            'predikat' => 'Sangat Baik',
            'status' => 'dikirim',
        ]);
        $atasan->user->update(['name' => 'Nama Akun Terdaftar']);

        $this->actingAs($kepalaBagianUser)
            ->get(route('kepala_bagian.pegawai.index'))
            ->assertOk()
            ->assertSee('Atasan & Pegawai dalam Tim')
            ->assertSee('3 Orang')
            ->assertSee('Pegawai Tim')
            ->assertSee('Atasan Uji')
            ->assertSee('Nama Akun Terdaftar')
            ->assertSee('KEPALA BAGIAN')
            ->assertSee('KEPALA SEKSI')
            ->assertSee($atasan->nama)
            ->assertSee('Kepala Seksi Uji')
            ->assertSee('Rata-rata Capaian SKP')
            ->assertSee('92.0%')
            ->assertSee('Perlu Review (1)')
            ->assertSee('1 laporan menunggu verifikasi')
            ->assertSee('Penutupan SKP Periode Uji Tim')
            ->assertSee(route('kepala_bagian.laporan.index'), false);

        $this->get(route('kepala_bagian.pegawai.index', ['sort' => 'nama_za']))
            ->assertOk()
            ->assertSeeInOrder([
                '<strong>Nama Akun Terdaftar</strong>',
                '<strong>Pegawai Tim</strong>',
                '<strong>Pegawai Belum Absen</strong>',
            ], false);

        $this->get(route('kepala_bagian.pegawai.index', ['search' => 'Pegawai Tim', 'status' => 'review']))
            ->assertOk()
            ->assertSee('Pegawai Tim')
            ->assertSee('Laporan perlu review');

        $this->actingAs($kepalaBagianUser)
            ->get(route('kepala_bagian.absensi.index'))
            ->assertOk()
            ->assertSee('Data Absensi Atasan')
            ->assertSee('1 Atasan Terdaftar')
            ->assertSee($atasan->nama)
            ->assertDontSee('Pegawai Tim')
            ->assertDontSee('Pegawai Belum Absen')
            ->assertSee('desktop-atasan-attendance', false)
            ->assertSee(route('kepala_bagian.absensi.index'), false);

        $this->get(route('kepala_bagian.laporan.index', ['status' => 'diajukan']))
            ->assertOk()
            ->assertSee('Pegawai Tim')
            ->assertSee('Approval Laporan');

        $this->post(route('kepala_bagian.laporan.approve', $laporan))
            ->assertRedirect(route('kepala_bagian.laporan.index'));

        $this->assertDatabaseHas('laporan_harians', [
            'id' => $laporan->id,
            'status' => 'disetujui',
            'disetujui_oleh' => $kepalaBagianUser->pegawai->id,
        ]);

        $this->get(route('kepala_bagian.absensi.show', $absensiAtasan))->assertOk();
        $this->get(route('kepala_bagian.absensi.show', $absensi))->assertNotFound();

        $otherAtasanUser = User::create([
            'name' => 'Atasan di Luar Tim',
            'email' => 'atasan.luar.tim@example.test',
            'password' => bcrypt('password'),
            'role' => 'atasan',
        ]);
        $otherAtasan = Pegawai::create([
            'user_id' => $otherAtasanUser->id,
            'nama' => 'Atasan di Luar Tim',
            'jabatan_id' => $atasan->jabatan_id,
            'atasan_id' => null,
            'status' => 'aktif',
        ]);
        $otherPegawaiUser = User::create([
            'name' => 'Pegawai di Luar Tim',
            'email' => 'pegawai.luar.tim@example.test',
            'password' => bcrypt('password'),
            'role' => 'pegawai',
        ]);
        $otherPegawai = Pegawai::create([
            'user_id' => $otherPegawaiUser->id,
            'nama' => 'Pegawai di Luar Tim',
            'jabatan_id' => $atasan->jabatan_id,
            'atasan_id' => $otherAtasan->id,
            'status' => 'aktif',
        ]);
        $otherAbsensi = Absensi::create([
            'pegawai_id' => $otherPegawai->id,
            'tanggal' => now()->toDateString(),
            'status' => 'hadir',
        ]);
        $otherLaporan = LaporanHarian::create([
            'pegawai_id' => $otherPegawai->id,
            'tanggal' => now()->toDateString(),
            'status' => 'diajukan',
        ]);

        $this->get(route('kepala_bagian.absensi.show', $otherAbsensi))->assertNotFound();
        $this->get(route('kepala_bagian.laporan.show', $otherLaporan))->assertForbidden();
    }

    private function createHierarchy(): array
    {
        $divisi = Divisi::create(['nama_divisi' => 'Divisi Uji']);
        $jabatan = Jabatan::create([
            'divisi_id' => $divisi->id,
            'nama_jabatan' => 'Kepala Seksi Uji',
            'grade' => 'Grade 1',
            'base_tukin' => 1000000,
        ]);
        $kepalaBagianUser = User::create([
            'name' => 'Kepala Bagian',
            'email' => 'kepala.bagian@example.test',
            'password' => bcrypt('password'),
            'role' => 'kepala_bagian',
        ]);
        Pegawai::create([
            'user_id' => $kepalaBagianUser->id,
            'nama' => 'Kepala Bagian',
            'jabatan_id' => $jabatan->id,
            'atasan_id' => null,
            'status' => 'aktif',
        ]);
        $atasanUser = User::create([
            'name' => 'Atasan Uji',
            'email' => 'atasan.uji@example.test',
            'password' => bcrypt('password'),
            'role' => 'atasan',
        ]);
        $atasan = Pegawai::create([
            'user_id' => $atasanUser->id,
            'nama' => 'Atasan Uji',
            'jabatan_id' => $jabatan->id,
            'atasan_id' => $kepalaBagianUser->pegawai->id,
            'status' => 'aktif',
        ]);

        return [$kepalaBagianUser, $atasan];
    }
}
