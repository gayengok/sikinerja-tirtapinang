<?php

namespace Tests\Feature;

use App\Models\Absensi;
use App\Models\Divisi;
use App\Models\Jabatan;
use App\Models\Pegawai;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AtasanAbsensiIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_atasan_can_view_attendance_dashboard_with_employee_table(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put(
            'absensi/bukti-presensi.png',
            base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAIAAACQd1PeAAAADUlEQVR4nGP4z8AAAAMBAQDJ/pLvAAAAAElFTkSuQmCC')
        );

        $divisi = Divisi::create(['nama_divisi' => 'Seksi Operasional']);
        $jabatan = Jabatan::create([
            'divisi_id' => $divisi->id,
            'nama_jabatan' => 'Staf Operasional',
            'grade' => 'Grade 1',
            'base_tukin' => 1500000,
        ]);
        $atasanUser = User::create([
            'name' => 'Atasan Uji',
            'email' => 'atasan.absensi@contoh.test',
            'password' => bcrypt('password'),
            'role' => 'atasan',
        ]);
        $atasan = Pegawai::create([
            'user_id' => $atasanUser->id,
            'nama' => 'Atasan Uji',
            'jabatan_id' => $jabatan->id,
            'atasan_id' => null,
            'tanggal_masuk' => '2020-01-01',
            'status' => 'aktif',
        ]);
        $pegawaiUser = User::create([
            'name' => 'Pegawai Uji',
            'email' => 'pegawai.absensi@contoh.test',
            'password' => bcrypt('password'),
            'role' => 'pegawai',
        ]);
        $pegawai = Pegawai::create([
            'user_id' => $pegawaiUser->id,
            'nama' => 'Pegawai Uji',
            'jabatan_id' => $jabatan->id,
            'atasan_id' => $atasan->id,
            'tanggal_masuk' => '2021-01-01',
            'status' => 'aktif',
        ]);
        $pegawaiBelumAbsenUser = User::create([
            'name' => 'Pegawai Belum Absen',
            'email' => 'pegawai.belum.absensi@contoh.test',
            'password' => bcrypt('password'),
            'role' => 'pegawai',
        ]);
        Pegawai::create([
            'user_id' => $pegawaiBelumAbsenUser->id,
            'nama' => 'Pegawai Belum Absen',
            'jabatan_id' => $jabatan->id,
            'atasan_id' => $atasan->id,
            'tanggal_masuk' => '2021-01-01',
            'status' => 'aktif',
        ]);
        Absensi::create([
            'pegawai_id' => $pegawai->id,
            'tanggal' => now()->toDateString(),
            'jam_masuk' => '08:00:00',
            'jam_pulang' => '16:00:00',
            'status' => 'hadir',
            'latitude' => -2.1500000,
            'longitude' => 106.1013270,
            'jarak_lokasi' => 7,
            'di_luar_kantor' => false,
            'akurasi_gps' => 5,
            'keterangan' => 'Presensi di area kantor.',
            'foto_path' => 'absensi/bukti-presensi.png',
        ]);

        $this->actingAs($atasanUser)
            ->get(route('atasan.absensi.index'))
            ->assertOk()
            ->assertSee('Data Absensi Pegawai')
            ->assertSee('desktop-atasan-attendance', false)
            ->assertDontSee('attendance-wide-navbar', false)
            ->assertSee('width: 82px;', false)
            ->assertSee('height: 34px;', false)
            ->assertDontSee('width: 100px !important;', false)
            ->assertDontSee('Ekspor CSV')
            ->assertDontSee('Cetak Rekap')
            ->assertSee('Rekap Presensi Harian Pegawai')
            ->assertSee('Pegawai Uji')
            ->assertSee('Pegawai Belum Absen')
            ->assertSee('Belum ada data periode ini')
            ->assertSee('Staf Operasional')
            ->assertSee('css/atasan/absensi/data-absensi.css', false)
            ->assertDontSee('Jarak GPS &amp; Status Lokasi')
            ->assertSee('attendanceTable', false)
            ->assertSee('Belum diverifikasi')
            ->assertSee(route('atasan.absensi.show', Absensi::first()), false);

        $this->actingAs($atasanUser)
            ->get(route('atasan.absensi.show', Absensi::first()))
            ->assertOk()
            ->assertDontSee('VERIFIKASI PRESENSI')
            ->assertSee('Pegawai Uji')
            ->assertSee('08:00')
            ->assertSee('Jarak dari kantor')
            ->assertSee('Akurasi GPS')
            ->assertSee('attendance-map-preview', false)
            ->assertSee('https://maps.google.com/maps?q=-2.15,106.101327&amp;z=16&amp;output=embed', false)
            ->assertSee('Presensi di area kantor.')
            ->assertSee('Bukti Foto Presensi')
            ->assertSee(route('atasan.absensi.foto', Absensi::first()), false);

        $absensi = Absensi::firstOrFail();
        $this->actingAs($atasanUser)
            ->post(route('atasan.absensi.verifikasi', $absensi), [
                'keputusan' => 'terverifikasi',
                'catatan_verifikasi' => 'Foto dan koordinat sesuai.',
            ])
            ->assertRedirect(route('atasan.absensi.show', $absensi))
            ->assertSessionHas('success', 'Data absensi berhasil diverifikasi.');

        $this->assertDatabaseHas('absensis', [
            'id' => $absensi->id,
            'status_verifikasi' => 'terverifikasi',
            'catatan_verifikasi' => 'Foto dan koordinat sesuai.',
            'diverifikasi_oleh' => $atasan->id,
        ]);
        $this->assertNotNull($absensi->fresh()->diverifikasi_pada);

        $this->actingAs($atasanUser)
            ->post(route('atasan.absensi.verifikasi', $absensi), [
                'keputusan' => 'perlu_klarifikasi',
                'catatan_verifikasi' => '',
            ])
            ->assertSessionHasErrors('catatan_verifikasi');

        $this->actingAs($atasanUser)
            ->post(route('atasan.absensi.verifikasi', $absensi), [
                'keputusan' => 'perlu_klarifikasi',
                'catatan_verifikasi' => 'Mohon konfirmasi lokasi presensi.',
            ])
            ->assertRedirect(route('atasan.absensi.show', $absensi))
            ->assertSessionHas('success', 'Data absensi ditandai perlu klarifikasi.');

        $this->assertDatabaseHas('absensis', [
            'id' => $absensi->id,
            'status_verifikasi' => 'perlu_klarifikasi',
            'catatan_verifikasi' => 'Mohon konfirmasi lokasi presensi.',
            'diverifikasi_oleh' => $atasan->id,
        ]);

        $this->actingAs($atasanUser)
            ->get(route('atasan.absensi.foto', $absensi))
            ->assertOk()
            ->assertHeader('Content-Type', 'image/png');

        $otherAtasanUser = User::create([
            'name' => 'Atasan Lain',
            'email' => 'atasan.lain.absensi@contoh.test',
            'password' => bcrypt('password'),
            'role' => 'atasan',
        ]);
        $otherAtasan = Pegawai::create([
            'user_id' => $otherAtasanUser->id,
            'nama' => 'Atasan Lain',
            'jabatan_id' => $jabatan->id,
            'atasan_id' => null,
            'tanggal_masuk' => '2020-01-01',
            'status' => 'aktif',
        ]);
        $pegawaiLainUser = User::create([
            'name' => 'Pegawai Lain',
            'email' => 'pegawai.lain.absensi@contoh.test',
            'password' => bcrypt('password'),
            'role' => 'pegawai',
        ]);
        $pegawaiLain = Pegawai::create([
            'user_id' => $pegawaiLainUser->id,
            'nama' => 'Pegawai Lain',
            'jabatan_id' => $jabatan->id,
            'atasan_id' => $otherAtasan->id,
            'tanggal_masuk' => '2021-01-01',
            'status' => 'aktif',
        ]);
        $absensiPegawaiLain = Absensi::create([
            'pegawai_id' => $pegawaiLain->id,
            'tanggal' => now()->toDateString(),
            'status' => 'hadir',
        ]);

        $this->actingAs($atasanUser)
            ->get(route('atasan.absensi.show', $absensiPegawaiLain))
            ->assertNotFound();

        $this->actingAs($atasanUser)
            ->get(route('atasan.absensi.foto', $absensiPegawaiLain))
            ->assertNotFound();

        $this->actingAs($atasanUser)
            ->post(route('atasan.absensi.verifikasi', $absensiPegawaiLain), [
                'keputusan' => 'terverifikasi',
            ])
            ->assertNotFound();
    }
}
