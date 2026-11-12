<?php

namespace Tests\Feature;

use App\Models\Absensi;
use App\Models\Divisi;
use App\Models\HasilPenilaian;
use App\Models\Jabatan;
use App\Models\Pegawai;
use App\Models\PeriodePenilaian;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KepalaBagianRekapDataTest extends TestCase
{
    use RefreshDatabase;

    public function test_kepala_bagian_can_view_monthly_attendance_and_tukin_across_all_divisions(): void
    {
        $divisi = Divisi::create(['nama_divisi' => 'Divisi Keuangan']);
        $jabatan = Jabatan::create([
            'divisi_id' => $divisi->id,
            'nama_jabatan' => 'Staf Keuangan',
            'grade' => 'Grade 1',
            'base_tukin' => 1500000,
        ]);
        $divisiLain = Divisi::create(['nama_divisi' => 'Divisi Pelayanan']);
        $jabatanLain = Jabatan::create([
            'divisi_id' => $divisiLain->id,
            'nama_jabatan' => 'Staf Pelayanan',
            'grade' => 'Grade 1',
            'base_tukin' => 1500000,
        ]);
        $kepalaBagianUser = $this->createUser('Kepala Bagian', 'kepala.rekap@example.test', 'kepala_bagian');
        $kepalaBagian = $this->createPegawai($kepalaBagianUser, $jabatan, null, 'Kepala Bagian');
        $atasanUser = $this->createUser('Atasan Tim', 'atasan.rekap@example.test', 'atasan');
        $atasan = $this->createPegawai($atasanUser, $jabatan, $kepalaBagian, 'Atasan Tim');
        $pegawaiUser = $this->createUser('Andi Pratama', 'andi.rekap@example.test', 'pegawai');
        $pegawai = $this->createPegawai($pegawaiUser, $jabatan, $atasan, 'Andi Pratama');

        $otherAtasanUser = $this->createUser('Atasan Lain', 'atasan.lain.rekap@example.test', 'atasan');
        $otherAtasan = $this->createPegawai($otherAtasanUser, $jabatanLain, null, 'Atasan Lain');
        $otherPegawaiUser = $this->createUser('Pegawai Lain', 'pegawai.lain.rekap@example.test', 'pegawai');
        $otherPegawai = $this->createPegawai($otherPegawaiUser, $jabatanLain, $otherAtasan, 'Pegawai Lain');

        $periode = PeriodePenilaian::create([
            'nama_periode' => 'Oktober 2026',
            'tanggal_mulai' => '2026-10-05',
            'tanggal_selesai' => '2026-10-25',
            'status' => 'final',
        ]);
        HasilPenilaian::create([
            'pegawai_id' => $pegawai->id,
            'periode_id' => $periode->id,
            'tukin_neto' => 1100000,
        ]);
        HasilPenilaian::create([
            'pegawai_id' => $otherPegawai->id,
            'periode_id' => $periode->id,
            'tukin_neto' => 2000000,
        ]);

        Absensi::create([
            'pegawai_id' => $pegawai->id,
            'tanggal' => '2026-10-05',
            'status' => 'hadir',
        ]);
        Absensi::create([
            'pegawai_id' => $pegawai->id,
            'tanggal' => '2026-10-06',
            'status' => 'alpa',
        ]);
        Absensi::create([
            'pegawai_id' => $pegawai->id,
            'tanggal' => '2026-11-01',
            'status' => 'hadir',
        ]);
        Absensi::create([
            'pegawai_id' => $otherPegawai->id,
            'tanggal' => '2026-10-05',
            'status' => 'hadir',
        ]);

        $this->actingAs($kepalaBagianUser)
            ->get(route('kepala_bagian.rekap-data.index', ['bulan' => 10, 'tahun' => 2026]))
            ->assertOk()
            ->assertSee('Rekap Data Kehadiran')
            ->assertSee(route('kepala_bagian.rekap-data.index'), false)
            ->assertSee('Jabatan')
            ->assertSee('Staf Keuangan')
            ->assertSee('Staf Pelayanan')
            ->assertSee('Andi Pratama')
            ->assertSee('Divisi Keuangan')
            ->assertSee('Pegawai Lain')
            ->assertSee('Divisi Pelayanan')
            ->assertSee('01-10-2026 s/d 31-10-2026')
            ->assertSee('1 hari')
            ->assertSee('2 hari')
            ->assertSee('Rp 1.100.000')
            ->assertSee('Rp 2.000.000')
            ->assertSee('Rp 3.100.000')
            ->assertSee('Periode tunjangan:')
            ->assertSee('Tersedia')
            ->assertSee('Total');

        $pdfResponse = $this->get(route('kepala_bagian.rekap-data.pdf', ['bulan' => 10, 'tahun' => 2026]));

        $pdfResponse->assertOk()
            ->assertHeader('content-type', 'application/pdf');
        $this->assertStringStartsWith('%PDF', $pdfResponse->getContent());
    }

    private function createUser(string $name, string $email, string $role): User
    {
        return User::create([
            'name' => $name,
            'email' => $email,
            'password' => bcrypt('password'),
            'role' => $role,
        ]);
    }

    private function createPegawai(User $user, Jabatan $jabatan, ?Pegawai $atasan, string $nama): Pegawai
    {
        return Pegawai::create([
            'user_id' => $user->id,
            'nama' => $nama,
            'jabatan_id' => $jabatan->id,
            'atasan_id' => $atasan?->id,
            'status' => 'aktif',
        ]);
    }
}
