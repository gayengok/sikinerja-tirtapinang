<?php

namespace Tests\Feature;

use App\Models\Absensi;
use App\Models\Divisi;
use App\Models\Jabatan;
use App\Models\LaporanHarian;
use App\Models\Pegawai;
use App\Models\PegawaiNotification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PegawaiNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_pegawai_receives_notifications_for_report_and_attendance_decisions(): void
    {
        $divisi = Divisi::create(['nama_divisi' => 'Operasional']);
        $jabatan = Jabatan::create([
            'divisi_id' => $divisi->id,
            'nama_jabatan' => 'Staf',
        ]);

        $atasanUser = User::create([
            'name' => 'Atasan Uji',
            'email' => 'atasan.notifikasi@example.test',
            'password' => bcrypt('password'),
            'role' => 'atasan',
        ]);
        $atasan = Pegawai::create([
            'user_id' => $atasanUser->id,
            'nama' => 'Atasan Uji',
            'jabatan_id' => $jabatan->id,
        ]);

        $pegawaiUser = User::create([
            'name' => 'Pegawai Uji',
            'email' => 'pegawai.notifikasi@example.test',
            'password' => bcrypt('password'),
            'role' => 'pegawai',
        ]);
        $pegawai = Pegawai::create([
            'user_id' => $pegawaiUser->id,
            'nama' => 'Pegawai Uji',
            'jabatan_id' => $jabatan->id,
            'atasan_id' => $atasan->id,
        ]);

        $laporanDisetujui = LaporanHarian::create([
            'pegawai_id' => $pegawai->id,
            'tanggal' => now()->subDays(2)->toDateString(),
            'status' => 'diajukan',
        ]);
        $laporanDitolak = LaporanHarian::create([
            'pegawai_id' => $pegawai->id,
            'tanggal' => now()->subDay()->toDateString(),
            'status' => 'diajukan',
        ]);
        $absensi = Absensi::create([
            'pegawai_id' => $pegawai->id,
            'tanggal' => now()->toDateString(),
        ]);

        $this->actingAs($atasanUser)
            ->post(route('atasan.laporan.approve', $laporanDisetujui))
            ->assertRedirect(route('atasan.laporan.index'));

        $this->post(route('atasan.laporan.reject', $laporanDitolak), [
            'alasan_penolakan' => 'Mohon lengkapi bukti kegiatan.',
        ])->assertRedirect(route('atasan.laporan.index'));

        $this->post(route('atasan.absensi.verifikasi', $absensi), [
            'keputusan' => 'terverifikasi',
        ])->assertRedirect(route('atasan.absensi.show', $absensi));

        $this->post(route('atasan.absensi.verifikasi', $absensi), [
            'keputusan' => 'perlu_klarifikasi',
            'catatan_verifikasi' => 'Mohon konfirmasi lokasi presensi.',
        ])->assertRedirect(route('atasan.absensi.show', $absensi));

        $notifications = PegawaiNotification::where('user_id', $pegawaiUser->id)
            ->orderBy('id')
            ->get();

        $this->assertCount(4, $notifications);
        $this->assertSame('Laporan disetujui', $notifications[0]->judul);
        $this->assertSame('Laporan ditolak', $notifications[1]->judul);
        $this->assertSame('Absensi terverifikasi', $notifications[2]->judul);
        $this->assertSame('Absensi perlu diperbaiki', $notifications[3]->judul);
        $this->assertSame(
            route('pegawai.laporan-harian.create', [
                'tanggal' => $laporanDisetujui->tanggal->toDateString(),
            ], false),
            $notifications[0]->url
        );

        $this->actingAs($pegawaiUser)
            ->get(route('pegawai.laporan-harian.show', $laporanDitolak))
            ->assertRedirect(route('pegawai.laporan-harian.create', [
                'tanggal' => $laporanDitolak->tanggal->toDateString(),
            ]));

        $this->get(route('pegawai.laporan-harian.create', [
            'tanggal' => $laporanDisetujui->tanggal->toDateString(),
        ]))
            ->assertOk()
            ->assertSee('daily-report-approval-toast', false)
            ->assertSee('Laporan sudah diverifikasi oleh Kepala Bagian.')
            ->assertSee('sudah disetujui dan hanya dapat dilihat.');

        $this->actingAs($pegawaiUser)
            ->get(route('pegawai.profile.index'))
            ->assertOk()
            ->assertSee('Laporan disetujui')
            ->assertSee('Laporan ditolak')
            ->assertSee('Mohon lengkapi bukti kegiatan.')
            ->assertSee('Absensi terverifikasi')
            ->assertSee('Absensi perlu diperbaiki')
            ->assertSee('Mohon konfirmasi lokasi presensi.');

        $notification = $notifications[0];
        $this->get(route('pegawai.notifikasi.open', $notification))
            ->assertRedirect($notification->url);

        $this->assertNotNull($notification->fresh()->dibaca_pada);

        $this->get(route('pegawai.profile.index'))
            ->assertOk()
            ->assertSee('Sudah dilihat');
    }
}
