<?php

namespace Tests\Feature;

use App\Models\Divisi;
use App\Models\Absensi;
use App\Models\Jabatan;
use App\Models\LaporanHarian;
use App\Models\MasterTemplateLaporan;
use App\Models\MasterKompetensi;
use App\Models\MasterKpi;
use App\Models\Pegawai;
use App\Models\PenilaianKpi;
use App\Models\PeriodePenilaian;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DirekturAssessmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_seeder_creates_director_and_assigns_kepala_bagian(): void
    {
        $this->seed(DatabaseSeeder::class);

        $direktur = User::where('email', 'direktur@sikinerja.test')->firstOrFail();
        $kepalaBagian = User::where('email', 'kabag@sikinerja.test')->firstOrFail();

        $this->assertSame('direktur', $direktur->role);
        $this->assertNotNull($direktur->pegawai);
        $this->assertSame($direktur->pegawai->id, $kepalaBagian->pegawai->atasan_id);
    }

    public function test_master_can_register_director_and_assign_kepala_bagian(): void
    {
        $divisi = Divisi::create(['nama_divisi' => 'Divisi Utama']);
        $jabatan = Jabatan::create([
            'divisi_id' => $divisi->id,
            'nama_jabatan' => 'Jabatan Struktural',
            'grade' => 'Grade 4',
            'base_tukin' => 5000000,
        ]);
        $master = User::create([
            'name' => 'Admin Master',
            'email' => 'admin.master.director@example.test',
            'password' => bcrypt('password'),
            'role' => 'master',
        ]);

        $this->actingAs($master)
            ->post(route('master.pegawai.store'), [
                'nama' => 'Direktur Operasional',
                'email' => 'direktur.operasional@example.test',
                'password' => 'password123',
                'role' => 'direktur',
                'jabatan_id' => $jabatan->id,
                'atasan_id' => '',
                'status' => 'aktif',
            ])
            ->assertRedirect(route('master.pegawai.index'));

        $direktur = Pegawai::where('nama', 'Direktur Operasional')->firstOrFail();

        $this->post(route('master.pegawai.store'), [
            'nama' => 'Kepala Bagian Operasional',
            'email' => 'kabag.operasional.director@example.test',
            'password' => 'password123',
            'role' => 'kepala_bagian',
            'jabatan_id' => $jabatan->id,
            'atasan_id' => $direktur->id,
            'status' => 'aktif',
        ])->assertRedirect(route('master.pegawai.index'));

        $this->assertDatabaseHas('pegawais', [
            'nama' => 'Kepala Bagian Operasional',
            'atasan_id' => $direktur->id,
        ]);
        $this->assertDatabaseHas('users', [
            'email' => 'direktur.operasional@example.test',
            'role' => 'direktur',
        ]);

        $this->get(route('master.pegawai.index'))
            ->assertOk()
            ->assertSee('Menilai Kepala Bagian')
            ->assertSee('Direktur Operasional')
            ->assertSee('Kepala Bagian Operasional');
    }

    public function test_director_can_open_rekap_data_from_dashboard_and_sidebar(): void
    {
        [$direkturUser] = $this->createHierarchy();

        $this->actingAs($direkturUser)
            ->get(route('direktur.dashboard'))
            ->assertOk()
            ->assertSee('title="Rekap Data"', false)
            ->assertDontSee('Rekap Data Kehadiran dan Tunjangan')
            ->assertSee(route('direktur.rekap-data.index'), false);

        $this->get(route('direktur.rekap-data.index'))
            ->assertOk()
            ->assertSee('Rekap Data Kehadiran')
            ->assertSee(route('direktur.rekap-data.index'), false)
            ->assertSee(route('direktur.rekap-data.pdf'), false);
    }

    public function test_director_dashboard_links_to_attendance_and_can_approve_only_direct_reports(): void
    {
        [$direkturUser, $kepalaBagian] = $this->createHierarchy();
        $otherDirekturUser = $this->createUserAndPegawai(
            'Direktur Lain',
            'direktur.laporan.lain@example.test',
            'direktur',
            $kepalaBagian->jabatan_id
        );
        $kepalaBagianLain = $this->createUserAndPegawai(
            'Kepala Bagian Lain',
            'kabag.laporan.lain@example.test',
            'kepala_bagian',
            $kepalaBagian->jabatan_id,
            $otherDirekturUser->pegawai->id
        );
        $laporanKepalaBagian = LaporanHarian::create([
            'pegawai_id' => $kepalaBagian->id,
            'tanggal' => now()->toDateString(),
            'catatan' => 'Laporan untuk Direktur',
            'status' => 'diajukan',
        ]);
        $laporanLain = LaporanHarian::create([
            'pegawai_id' => $kepalaBagianLain->pegawai->id,
            'tanggal' => now()->toDateString(),
            'catatan' => 'Laporan Direktur lain',
            'status' => 'diajukan',
        ]);

        $dashboard = $this->actingAs($direkturUser)
            ->get(route('direktur.dashboard'))
            ->assertOk()
            ->assertSee(route('direktur.rekap-data.index'), false)
            ->assertSee(route('direktur.laporan.index', ['status' => 'diajukan']), false)
            ->assertSee('Verifikasi Absensi Harian')
            ->assertSee('Approval Laporan');

        preg_match(
            '/<aside class="sidebar" id="sidebar">(.*?)<\/aside>/s',
            $dashboard->getContent(),
            $sidebar
        );
        $this->assertNotEmpty($sidebar);
        $this->assertStringNotContainsString(route('direktur.laporan.index'), $sidebar[1]);
        $this->assertStringNotContainsString(route('direktur.absensi.index'), $sidebar[1]);

        $this->get(route('direktur.laporan.index'))
            ->assertOk()
            ->assertSee('Laporan untuk Direktur')
            ->assertDontSee('Laporan Direktur lain');

        $this->get(route('direktur.laporan.show', $laporanLain))
            ->assertForbidden();

        $this->post(route('direktur.laporan.approve', $laporanKepalaBagian))
            ->assertRedirect(route('direktur.laporan.index'));

        $this->assertDatabaseHas('laporan_harians', [
            'id' => $laporanKepalaBagian->id,
            'status' => 'disetujui',
            'disetujui_oleh' => $direkturUser->pegawai->id,
        ]);
    }

    public function test_kepala_bagian_can_submit_daily_data_for_director_verification(): void
    {
        [$direkturUser, $kepalaBagian] = $this->createHierarchy();
        $template = MasterTemplateLaporan::create([
            'divisi_id' => $kepalaBagian->jabatan->divisi_id,
            'nama_field' => 'Jumlah pekerjaan selesai',
            'tipe_field' => 'angka',
            'satuan' => 'pekerjaan',
        ]);
        $kepalaBagianUser = $kepalaBagian->user;

        $this->actingAs($kepalaBagianUser)
            ->get(route('kepala_bagian.laporan-harian.create'))
            ->assertOk()
            ->assertSee('Buat Laporan Harian')
            ->assertSee('Jumlah pekerjaan selesai')
            ->assertSee(route('kepala_bagian.laporan-harian.store'), false);

        $this->get(route('kepala_bagian.absensi-saya.index'))
            ->assertOk()
            ->assertSee('Presensi Kepala Bagian')
            ->assertSee(route('kepala_bagian.absensi-saya.store'), false);

        $this->post(route('kepala_bagian.laporan-harian.store'), [
            'tanggal' => now()->toDateString(),
            'catatan' => 'Laporan kegiatan Kepala Bagian.',
            'detail' => [[
                'master_template_laporan_id' => $template->id,
                'nilai' => '5',
            ]],
        ])
            ->assertRedirect(route('kepala_bagian.laporan-harian.create'))
            ->assertSessionHas(
                'success',
                'Laporan harian berhasil dikirim kepada Direktur untuk diverifikasi.'
            );

        $laporan = LaporanHarian::where('pegawai_id', $kepalaBagian->id)->firstOrFail();
        $this->assertSame('diajukan', $laporan->status);

        $this->actingAs($direkturUser)
            ->get(route('direktur.laporan.index'))
            ->assertOk()
            ->assertSee('Laporan kegiatan Kepala Bagian.');

        $this->post(route('direktur.laporan.approve', $laporan))
            ->assertRedirect(route('direktur.laporan.index'));

        $absensi = Absensi::create([
            'pegawai_id' => $kepalaBagian->id,
            'tanggal' => now()->toDateString(),
            'jam_masuk' => '08:00:00',
            'jam_pulang' => '16:00:00',
            'status' => 'hadir',
        ]);

        $this->get(route('direktur.absensi.index'))
            ->assertOk()
            ->assertSee('Data Absensi Kepala Bagian')
            ->assertSee($kepalaBagian->nama);

        $this->post(route('direktur.absensi.verifikasi', $absensi), [
            'keputusan' => 'terverifikasi',
        ])
            ->assertRedirect(route('direktur.absensi.show', $absensi));

        $this->assertDatabaseHas('absensis', [
            'id' => $absensi->id,
            'status_verifikasi' => 'terverifikasi',
            'diverifikasi_oleh' => $direkturUser->pegawai->id,
        ]);
    }

    public function test_director_can_assess_assigned_head_and_is_limited_to_reporting_line(): void
    {
        [$direkturUser, $kepalaBagian] = $this->createHierarchy();
        $otherDirekturUser = $this->createUserAndPegawai(
            'Direktur Lain',
            'direktur.lain@example.test',
            'direktur',
            $kepalaBagian->jabatan_id
        );
        $kepalaBagianLain = $this->createUserAndPegawai(
            'Kepala Bagian Lain',
            'kabag.lain@example.test',
            'kepala_bagian',
            $kepalaBagian->jabatan_id,
            $otherDirekturUser->pegawai->id
        );
        $periode = PeriodePenilaian::create([
            'nama_periode' => 'Periode Direktur',
            'tanggal_mulai' => '2026-01-01',
            'tanggal_selesai' => '2026-12-31',
            'status' => 'berjalan',
        ]);
        $masterKpi = MasterKpi::create([
            'jabatan_id' => $kepalaBagian->jabatan_id,
            'indikator' => 'Capaian Kepala Bagian',
            'target_default' => 100,
            'bobot' => 100,
            'arah_capaian' => 'maksimal',
            'urutan' => 1,
        ]);
        $masterKompetensi = MasterKompetensi::create([
            'jabatan_id' => null,
            'nama_kompetensi' => 'Kepemimpinan Strategis',
            'definisi_perilaku' => 'Mengarahkan unit kerja',
            'urutan' => 1,
        ]);

        $this->post(route('login.store'), [
                'email' => $direkturUser->email,
                'password' => 'password',
            ])
            ->assertRedirect(route('direktur.dashboard'));

        $this->get(route('direktur.dashboard'))
            ->assertOk()
            ->assertSee('Dashboard Direktur')
            ->assertSee('id="sidebarToggleDesktop"', false)
            ->assertSee('id="sidebarToggleMobile"', false)
            ->assertSee('title="Penilaian Kepala Bagian"', false)
            ->assertDontSee('direktur-dashboard-person', false)
            ->assertDontSee('Kepala Bagian Operasional')
            ->assertDontSee('Kepala Bagian Lain');

        $this->get(route('direktur.penilaian-kepala-bagian.index'))
            ->assertOk()
            ->assertSee('DIREKTUR')
            ->assertSee('Daftar Kepala Bagian yang Dinilai')
            ->assertSee('PROGRES KPI')
            ->assertSee('0 dari 1 butir indikator')
            ->assertSee('Hasil Penilaian KPI')
            ->assertDontSee('Nilai KPI')
            ->assertSee(route('direktur.penilaian-kepala-bagian.show', [
                'pegawai' => $kepalaBagian,
                'periode_id' => $periode->id,
            ]), false)
            ->assertDontSee('Kepala Bagian Lain');

        $this->get(route('direktur.penilaian-kepala-bagian.show', [
            'pegawai' => $kepalaBagian,
            'periode_id' => $periode->id,
        ]))
            ->assertOk()
            ->assertSee('Hasil Penilaian KPI')
            ->assertSee('Belum ada indikator KPI yang dinilai')
            ->assertDontSee('Form Penilaian');

        $this->get(route('direktur.penilaian-kepala-bagian.create', [
            'pegawai' => $kepalaBagian,
            'periode_id' => $periode->id,
        ]))
            ->assertOk()
            ->assertSee('KEPALA BAGIAN YANG DINILAI')
            ->assertSee('Capaian Kepala Bagian');

        $this->post(route('direktur.penilaian-kepala-bagian.store', $kepalaBagian), [
            'periode_id' => $periode->id,
            'kpi' => [[
                'master_kpi_id' => $masterKpi->id,
                'target' => 100,
                'realisasi_final' => 90,
                'skor' => 4,
                'keterangan' => 'Target hampir tercapai',
            ]],
        ])->assertRedirect(route('direktur.penilaian-kepala-bagian.show', [
            'pegawai' => $kepalaBagian,
            'periode_id' => $periode->id,
        ]));

        $this->assertDatabaseHas('penilaian_kpis', [
            'pegawai_id' => $kepalaBagian->id,
            'periode_id' => $periode->id,
            'master_kpi_id' => $masterKpi->id,
            'skor' => 4,
        ]);
        $this->assertDatabaseHas('hasil_penilaians', [
            'pegawai_id' => $kepalaBagian->id,
            'periode_id' => $periode->id,
        ]);

        $this->get(route('direktur.penilaian-kepala-bagian.index'))
            ->assertOk()
            ->assertSee('100%')
            ->assertSee('1 dari 1 butir indikator')
            ->assertSee(route('direktur.penilaian-kepala-bagian.show', [
                'pegawai' => $kepalaBagian,
                'periode_id' => $periode->id,
            ]), false);

        $this->get(route('direktur.penilaian-kepala-bagian.kompetensi.edit', [
            'pegawai' => $kepalaBagian,
            'periode_id' => $periode->id,
        ]))
            ->assertOk()
            ->assertSee('Kepemimpinan Strategis');

        $this->put(route('direktur.penilaian-kepala-bagian.kompetensi.update', $kepalaBagian), [
            'periode_id' => $periode->id,
            'kompetensi' => [[
                'master_kompetensi_id' => $masterKompetensi->id,
                'skor' => 5,
                'catatan' => 'Mampu memimpin tim.',
            ]],
        ])->assertRedirect(route('direktur.penilaian-kepala-bagian.show', [
            'pegawai' => $kepalaBagian,
            'periode_id' => $periode->id,
        ]));

        $this->assertDatabaseHas('penilaian_kompetensis', [
            'pegawai_id' => $kepalaBagian->id,
            'periode_id' => $periode->id,
            'master_kompetensi_id' => $masterKompetensi->id,
            'skor' => 5,
        ]);
        $this->assertDatabaseHas('hasil_penilaians', [
            'pegawai_id' => $kepalaBagian->id,
            'periode_id' => $periode->id,
        ]);

        $this->get(route('direktur.penilaian-kepala-bagian.show', [
            'pegawai' => $kepalaBagian,
            'periode_id' => $periode->id,
        ]))
            ->assertOk()
            ->assertSee('Hasil Penilaian KPI')
            ->assertSee('Kepala Bagian')
            ->assertSee('Rincian Hasil KPI')
            ->assertSee('Rincian Tunjangan Kinerja')
            ->assertSee('Cetak PDF')
            ->assertSee(route('direktur.penilaian-kepala-bagian.pdf', [
                'pegawai' => $kepalaBagian,
                'periode_id' => $periode->id,
            ]), false)
            ->assertSee('TUKIN NETO DITERIMA');

        $pdfResponse = $this->get(route('direktur.penilaian-kepala-bagian.pdf', [
            'pegawai' => $kepalaBagian,
            'periode_id' => $periode->id,
        ]));
        $pdfResponse->assertOk();
        $this->assertSame('application/pdf', $pdfResponse->headers->get('content-type'));
        $this->assertStringContainsString('.pdf', $pdfResponse->headers->get('content-disposition'));

        $this->get(route('direktur.penilaian-kepala-bagian.show', $kepalaBagianLain))
            ->assertForbidden();
    }

    private function createHierarchy(): array
    {
        $divisi = Divisi::create(['nama_divisi' => 'Divisi Direktur']);
        $jabatan = Jabatan::create([
            'divisi_id' => $divisi->id,
            'nama_jabatan' => 'Kepala Bagian Utama',
            'grade' => 'Grade 3',
            'base_tukin' => 4000000,
        ]);
        $direkturUser = User::create([
            'name' => 'Direktur Uji',
            'email' => 'direktur.uji@example.test',
            'password' => bcrypt('password'),
            'role' => 'direktur',
        ]);
        $direktur = Pegawai::create([
            'user_id' => $direkturUser->id,
            'nama' => 'Direktur Uji',
            'jabatan_id' => $jabatan->id,
            'status' => 'aktif',
        ]);
        $kepalaBagianUser = $this->createUserAndPegawai(
            'Kepala Bagian Operasional',
            'kabag.operasional@example.test',
            'kepala_bagian',
            $jabatan->id,
            $direktur->id
        );

        return [$direkturUser, $kepalaBagianUser->pegawai];
    }

    private function createUserAndPegawai(
        string $name,
        string $email,
        string $role,
        int $jabatanId,
        ?int $atasanId = null
    ): User {
        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => bcrypt('password'),
            'role' => $role,
        ]);
        Pegawai::create([
            'user_id' => $user->id,
            'nama' => $name,
            'jabatan_id' => $jabatanId,
            'atasan_id' => $atasanId,
            'status' => 'aktif',
        ]);

        return $user->load('pegawai');
    }
}
