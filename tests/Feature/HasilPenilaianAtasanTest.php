<?php

namespace Tests\Feature;

use App\Models\Divisi;
use App\Models\HasilPenilaian;
use App\Models\Jabatan;
use App\Models\LaporanHarian;
use App\Models\MasterKompetensi;
use App\Models\MasterJamKerja;
use App\Models\MasterKpi;
use App\Models\MasterTemplateLaporan;
use App\Models\Pegawai;
use App\Models\PeriodePenilaian;
use App\Models\User;
use App\Models\Absensi;
use Carbon\Carbon;
use Database\Seeders\JenisPotonganSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HasilPenilaianAtasanTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_atasan_index_generates_hasil_penilaian_and_scores_missing_inputs_as_zero(): void
    {
        Carbon::setTestNow('2026-09-16 18:00:00');
        (new JenisPotonganSeeder)->run();

        $divisi = Divisi::create([
            'nama_divisi' => 'Seksi Uji Coba',
        ]);

        $jabatan = Jabatan::create([
            'divisi_id' => $divisi->id,
            'nama_jabatan' => 'Pelaksana Uji Coba',
            'grade' => 'Grade 1',
            'base_tukin' => 1500000,
        ]);

        $atasanUser = User::create([
            'name' => 'Atasan Uji Coba',
            'email' => 'atasan.uji@contoh.test',
            'password' => bcrypt('password'),
            'role' => 'atasan',
        ]);

        $atasanPegawai = Pegawai::create([
            'user_id' => $atasanUser->id,
            'nama' => 'Atasan Uji Coba',
            'jabatan_id' => $jabatan->id,
            'atasan_id' => null,
            'tanggal_masuk' => '2020-01-01',
            'status' => 'aktif',
        ]);

        $pegawaiUser = User::create([
            'name' => 'Pegawai Uji Coba',
            'email' => 'pegawai.uji@contoh.test',
            'password' => bcrypt('password'),
            'role' => 'pegawai',
        ]);

        $pegawai = Pegawai::create([
            'user_id' => $pegawaiUser->id,
            'nama' => 'Pegawai Uji Coba',
            'jabatan_id' => $jabatan->id,
            'atasan_id' => $atasanPegawai->id,
            'tanggal_masuk' => '2021-01-01',
            'status' => 'aktif',
        ]);

        $pegawaiBelumDinilaiUser = User::create([
            'name' => 'Pegawai Belum Dinilai',
            'email' => 'pegawai.belum-dinilai@contoh.test',
            'password' => bcrypt('password'),
            'role' => 'pegawai',
        ]);

        $pegawaiBelumDinilai = Pegawai::create([
            'user_id' => $pegawaiBelumDinilaiUser->id,
            'nama' => 'Pegawai Belum Dinilai',
            'jabatan_id' => $jabatan->id,
            'atasan_id' => $atasanPegawai->id,
            'tanggal_masuk' => '2021-01-01',
            'status' => 'aktif',
        ]);

        $periode = PeriodePenilaian::create([
            'nama_periode' => 'Uji Coba 2026',
            'tanggal_mulai' => '2026-09-15',
            'tanggal_selesai' => '2026-09-15',
            'status' => 'berjalan',
        ]);
        MasterJamKerja::create([
            'jabatan_id' => $jabatan->id,
            'hari' => 'Selasa',
            'jam_masuk' => '08:00:00',
            'jam_pulang' => '16:00:00',
            'toleransi_menit' => 0,
        ]);
        Absensi::create([
            'pegawai_id' => $pegawai->id,
            'tanggal' => '2026-09-15',
            'jam_masuk' => '08:05:00',
            'jam_pulang' => '16:00:00',
            'status' => 'hadir',
        ]);

        $masterKpi = MasterKpi::create([
            'jabatan_id' => $jabatan->id,
            'indikator' => 'Kinerja Uji Coba',
            'target_default' => 100,
            'bobot' => 60,
            'arah_capaian' => 'maksimal',
            'urutan' => 1,
        ]);

        $template = MasterTemplateLaporan::create([
            'divisi_id' => $divisi->id,
            'nama_field' => 'Output terverifikasi',
            'tipe_field' => 'angka',
            'satuan' => 'unit',
            'terhubung_ke_kpi_id' => $masterKpi->id,
        ]);

        $laporan = LaporanHarian::create([
            'pegawai_id' => $pegawai->id,
            'tanggal' => '2026-09-15',
            'status' => 'disetujui',
        ]);
        $laporan->details()->create([
            'master_template_laporan_id' => $template->id,
            'nilai' => '100',
        ]);

        $masterKompetensi = MasterKompetensi::create([
            'jabatan_id' => null,
            'nama_kompetensi' => 'Kedisiplinan',
            'definisi_perilaku' => 'Uji coba',
            'urutan' => 1,
        ]);

        $this->actingAs($atasanUser)
            ->post(route('atasan.penilaian-kpi.store', $pegawai), [
                'periode_id' => $periode->id,
                'kpi' => [[
                    'master_kpi_id' => $masterKpi->id,
                    'target' => 100,
                    'realisasi_final' => 90,
                    'skor' => 5,
                    'keterangan' => 'ok',
                ]],
            ])
            ->assertRedirect();

        $this->assertDatabaseMissing('hasil_penilaians', [
            'pegawai_id' => $pegawai->id,
            'periode_id' => $periode->id,
        ]);

        $this->actingAs($atasanUser)
            ->put(route('atasan.kompetensi.update', $pegawai), [
                'periode_id' => $periode->id,
                'kompetensi' => [[
                    'master_kompetensi_id' => $masterKompetensi->id,
                    'skor' => 5,
                    'catatan' => 'baik',
                ]],
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('hasil_penilaians', [
            'pegawai_id' => $pegawai->id,
            'periode_id' => $periode->id,
            'rata_skor_kpi' => 100,
            'rata_skor_kompetensi' => 100,
            'nilai_akhir' => 100,
            'status' => 'draft',
        ]);
        $hasil = HasilPenilaian::where('pegawai_id', $pegawai->id)
            ->where('periode_id', $periode->id)
            ->firstOrFail();
        $this->assertEquals(1, (float) $hasil->total_potongan_presensi);
        $this->assertEquals(15000, (float) $hasil->tukin_bruto - (float) $hasil->tukin_neto);

        $this->actingAs($atasanUser)
            ->get(route('atasan.hasil-penilaian.show', ['pegawai' => $pegawai, 'periode_id' => $periode->id]))
            ->assertOk()
            ->assertSee('Terlambat Ringan')
            ->assertSee('tarif 0.50%')
            ->assertSee('Total 1%');

        $response = $this->actingAs($atasanUser)
            ->get('/atasan/hasil-penilaian?periode_id='.$periode->id);

        $response->assertOk();
        $response->assertSee('Daftar Penilaian Pegawai (2)');
        $response->assertSee('Pegawai Uji Coba');
        $response->assertSee('Pegawai Belum Dinilai');
        $response->assertSee('Kurang');
        $this->assertDatabaseHas('hasil_penilaians', [
            'pegawai_id' => $pegawai->id,
            'periode_id' => $periode->id,
        ]);
        $this->assertDatabaseHas('hasil_penilaians', [
            'pegawai_id' => $pegawaiBelumDinilai->id,
            'periode_id' => $periode->id,
            'rata_skor_kpi' => 0,
            'rata_skor_kompetensi' => 0,
            'nilai_akhir' => 0,
            'tukin_bruto' => 0,
        ]);
        $this->assertDatabaseHas('penilaian_kpis', [
            'pegawai_id' => $pegawai->id,
            'master_kpi_id' => $masterKpi->id,
            'realisasi_otomatis' => 100,
            'realisasi_final' => 100,
        ]);
    }
}
