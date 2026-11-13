<?php

namespace Tests\Feature;

use App\Models\Divisi;
use App\Models\Jabatan;
use App\Models\LaporanHarian;
use App\Models\MasterTemplateLaporan;
use App\Models\Pegawai;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AtasanLaporanHarianTest extends TestCase
{
    use RefreshDatabase;

    public function test_atasan_can_create_submit_and_review_own_daily_report_using_division_template(): void
    {
        [$atasanUser, $jabatan] = $this->createAtasan();
        $template = MasterTemplateLaporan::create([
            'divisi_id' => $jabatan->divisi_id,
            'nama_field' => 'Jumlah pekerjaan selesai',
            'tipe_field' => 'angka',
            'satuan' => 'pekerjaan',
        ]);

        $this->actingAs($atasanUser)
            ->get(route('atasan.dashboard'))
            ->assertOk()
            ->assertSee(route('atasan.laporan-harian.create'), false)
            ->assertSee('Laporan Harian ke Kepala Bagian')
            ->assertSee('Buat Laporan')
            ->assertDontSee('Input Laporan Saya')
            ->assertDontSee('Belum ada laporan pribadi');

        $this->get(route('atasan.laporan-harian.create'))
            ->assertOk()
            ->assertSee('Buat Laporan Harian')
            ->assertSee('Tahapan pengisian laporan')
            ->assertSee('id="laporan-informasi"', false)
            ->assertSee('id="laporan-realisasi"', false)
            ->assertSee('Realisasi aktivitas')
            ->assertSee('Jumlah pekerjaan selesai')
            ->assertSee(route('atasan.laporan-harian.store'), false)
            ->assertSee('Kirim Laporan Harian')
            ->assertDontSee('Simpan')
            ->assertDontSee('Batal');

        $this->post(route('atasan.laporan-harian.store'), [
            'tanggal' => now()->toDateString(),
            'catatan' => 'Menyelesaikan rekap pekerjaan tim.',
            'detail' => [[
                'master_template_laporan_id' => $template->id,
                'nilai' => '8',
            ]],
        ])->assertRedirect(route('atasan.laporan-harian.create'));

        $laporan = LaporanHarian::where('pegawai_id', $atasanUser->pegawai->id)->firstOrFail();
        $this->assertSame('diajukan', $laporan->status);
        $this->assertDatabaseHas('laporan_harian_details', [
            'laporan_harian_id' => $laporan->id,
            'master_template_laporan_id' => $template->id,
            'nilai' => '8',
        ]);

        $this->get(route('atasan.laporan-harian.create'))
            ->assertOk()
            ->assertSee('Menunggu verifikasi Kepala Bagian')
            ->assertSee('Laporan sudah dikirim dan sedang menunggu verifikasi Kepala Bagian.')
            ->assertSee('Menyelesaikan rekap pekerjaan tim.')
            ->assertSee('value="8"', false)
            ->assertSee('readonly', false)
            ->assertDontSee('Kirim Laporan Harian');

        $this->get(route('atasan.dashboard'))
            ->assertOk()
            ->assertSee(route('atasan.laporan-harian.create'), false)
            ->assertDontSee(route('atasan.laporan-harian.show', $laporan), false)
            ->assertSee('is-completed', false)
            ->assertSee('Sudah Isi Laporan')
            ->assertDontSee('Buat Laporan');

        $this->get(route('atasan.laporan-harian.show', $laporan))
            ->assertRedirect(route('atasan.dashboard'));
    }

    public function test_atasan_report_menu_opens_the_form_instead_of_report_history(): void
    {
        [$atasanUser] = $this->createAtasan();
        LaporanHarian::create([
            'pegawai_id' => $atasanUser->pegawai->id,
            'tanggal' => now()->subDay()->toDateString(),
            'catatan' => 'Rekap hasil pekerjaan harian.',
            'status' => 'ditolak',
        ]);

        $this->actingAs($atasanUser)
            ->get(route('atasan.dashboard'))
            ->assertOk()
            ->assertSee(route('atasan.laporan-harian.create'), false)
            ->assertDontSee('Rekap hasil pekerjaan harian.')
            ->assertDontSee('Perlu revisi');

        $this->get(route('atasan.laporan-harian.index'))
            ->assertRedirect(route('atasan.laporan-harian.create'));
    }

    public function test_atasan_dashboard_shows_count_of_unconfirmed_subordinate_reports(): void
    {
        [$atasanUser, $jabatan] = $this->createAtasan();

        $this->actingAs($atasanUser)
            ->get(route('atasan.dashboard'))
            ->assertOk()
            ->assertDontSee('Menunggu Konfirmasi Laporan');

        for ($index = 1; $index <= 3; $index++) {
            $pegawaiUser = User::create([
                'name' => "Pegawai {$index}",
                'email' => "pegawai{$index}.pending@example.test",
                'password' => bcrypt('password'),
                'role' => 'pegawai',
            ]);
            $pegawai = Pegawai::create([
                'user_id' => $pegawaiUser->id,
                'nama' => "Pegawai {$index}",
                'jabatan_id' => $jabatan->id,
                'atasan_id' => $atasanUser->pegawai->id,
                'status' => 'aktif',
            ]);
            LaporanHarian::create([
                'pegawai_id' => $pegawai->id,
                'tanggal' => now()->subDays($index)->toDateString(),
                'catatan' => "Laporan {$index}",
                'status' => 'diajukan',
            ]);
        }

        $this->get(route('atasan.dashboard'))
            ->assertOk()
            ->assertSee('Menunggu Konfirmasi Laporan')
            ->assertSee('3 laporan harian bawahan telah dikirim dan belum dikonfirmasi.')
            ->assertSee(route('atasan.laporan.index', ['status' => 'diajukan']), false);
    }

    public function test_atasan_report_form_only_shows_templates_for_their_job(): void
    {
        [$atasanUser, $jabatan] = $this->createAtasan();
        $otherJabatan = Jabatan::create([
            'divisi_id' => $jabatan->divisi_id,
            'nama_jabatan' => 'Pengawas Operasional',
            'grade' => 'Grade 3',
            'base_tukin' => 2500000,
        ]);
        MasterTemplateLaporan::create([
            'divisi_id' => $jabatan->divisi_id,
            'jabatan_id' => $jabatan->id,
            'nama_field' => 'Jumlah pekerjaan atasan',
            'tipe_field' => 'angka',
        ]);
        MasterTemplateLaporan::create([
            'divisi_id' => $jabatan->divisi_id,
            'jabatan_id' => $otherJabatan->id,
            'nama_field' => 'Jumlah pengawasan',
            'tipe_field' => 'angka',
        ]);

        $this->actingAs($atasanUser)
            ->get(route('atasan.laporan-harian.create'))
            ->assertOk()
            ->assertSee('Jumlah pekerjaan atasan')
            ->assertDontSee('Jumlah pengawasan');
    }

    public function test_kepala_bagian_can_approve_report_submitted_by_assigned_atasan(): void
    {
        $divisi = Divisi::create(['nama_divisi' => 'Divisi Atasan Uji']);
        $jabatan = Jabatan::create([
            'divisi_id' => $divisi->id,
            'nama_jabatan' => 'Atasan Unit',
            'grade' => 'Grade 2',
            'base_tukin' => 2000000,
        ]);
        $kepalaBagianUser = User::create([
            'name' => 'Kepala Bagian Uji',
            'email' => 'kepala.bagian.laporan@example.test',
            'password' => bcrypt('password'),
            'role' => 'kepala_bagian',
        ]);
        $kepalaBagian = Pegawai::create([
            'user_id' => $kepalaBagianUser->id,
            'nama' => 'Kepala Bagian Uji',
            'jabatan_id' => $jabatan->id,
            'status' => 'aktif',
        ]);
        $atasanUser = User::create([
            'name' => 'Atasan Uji',
            'email' => 'atasan.report@example.test',
            'password' => bcrypt('password'),
            'role' => 'atasan',
        ]);
        $atasan = Pegawai::create([
            'user_id' => $atasanUser->id,
            'nama' => 'Atasan Uji',
            'jabatan_id' => $jabatan->id,
            'atasan_id' => $kepalaBagian->id,
            'status' => 'aktif',
        ]);
        $laporan = LaporanHarian::create([
            'pegawai_id' => $atasan->id,
            'tanggal' => now()->toDateString(),
            'catatan' => 'Laporan kerja atasan.',
            'status' => 'diajukan',
        ]);

        $this->actingAs($kepalaBagianUser)
            ->get(route('kepala_bagian.laporan.index', ['status' => 'diajukan']))
            ->assertOk()
            ->assertSee('Laporan kerja atasan.')
            ->assertSee('Atasan Uji');

        $this->post(route('kepala_bagian.laporan.approve', $laporan))
            ->assertRedirect(route('kepala_bagian.laporan.index'));

        $this->assertDatabaseHas('laporan_harians', [
            'id' => $laporan->id,
            'status' => 'disetujui',
            'disetujui_oleh' => $kepalaBagian->id,
        ]);

        $this->actingAs($atasanUser)
            ->get(route('atasan.laporan-harian.create'))
            ->assertOk()
            ->assertSee('Laporan sudah diverifikasi oleh Kepala Bagian.')
            ->assertDontSee('Laporan sudah diverifikasi</button>', false);
    }

    public function test_existing_pegawai_report_form_keeps_its_original_routes(): void
    {
        $divisi = Divisi::create(['nama_divisi' => 'Divisi Pegawai Uji']);
        $jabatan = Jabatan::create([
            'divisi_id' => $divisi->id,
            'nama_jabatan' => 'Pegawai Operasional',
            'grade' => 'Grade 1',
            'base_tukin' => 1500000,
        ]);
        $pegawaiUser = User::create([
            'name' => 'Pegawai Uji',
            'email' => 'pegawai.report@example.test',
            'password' => bcrypt('password'),
            'role' => 'pegawai',
        ]);
        Pegawai::create([
            'user_id' => $pegawaiUser->id,
            'nama' => 'Pegawai Uji',
            'jabatan_id' => $jabatan->id,
            'status' => 'aktif',
        ]);
        MasterTemplateLaporan::create([
            'divisi_id' => $divisi->id,
            'nama_field' => 'Jumlah pekerjaan',
            'tipe_field' => 'angka',
            'satuan' => 'pekerjaan',
        ]);

        $this->actingAs($pegawaiUser)
            ->get(route('pegawai.laporan-harian.create'))
            ->assertOk()
            ->assertDontSee('Tahapan pengisian laporan')
            ->assertSee(route('pegawai.laporan-harian.store'), false);

        $this->get(route('pegawai.laporan-harian.index'))->assertOk();
    }

    private function createAtasan(): array
    {
        $divisi = Divisi::create(['nama_divisi' => 'Divisi Operasional']);
        $jabatan = Jabatan::create([
            'divisi_id' => $divisi->id,
            'nama_jabatan' => 'Kepala Seksi Operasional',
            'grade' => 'Grade 2',
            'base_tukin' => 2000000,
        ]);
        $atasanUser = User::create([
            'name' => 'Atasan Operasional',
            'email' => 'atasan.operasional@example.test',
            'password' => bcrypt('password'),
            'role' => 'atasan',
        ]);
        Pegawai::create([
            'user_id' => $atasanUser->id,
            'nama' => 'Atasan Operasional',
            'jabatan_id' => $jabatan->id,
            'status' => 'aktif',
        ]);

        return [$atasanUser, $jabatan];
    }
}
