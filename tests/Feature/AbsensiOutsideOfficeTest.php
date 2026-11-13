<?php

namespace Tests\Feature;

use App\Models\Divisi;
use App\Models\Jabatan;
use App\Models\Pegawai;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AbsensiOutsideOfficeTest extends TestCase
{
    use RefreshDatabase;

    public function test_pegawai_can_submit_attendance_from_outside_office_and_location_is_flagged(): void
    {
        Storage::fake('public');

        [$user, $pegawai] = $this->createPegawai();

        $response = $this->actingAs($user)->post(route('pegawai.absensi.store'), [
            'status' => 'hadir',
            'jenis_presensi' => 'masuk',
            'latitude' => -2.1500000,
            'longitude' => 106.1013270,
            'accuracy' => 10,
            'foto' => UploadedFile::fake()->create('presensi.jpg', 10, 'image/jpeg'),
        ]);

        $response->assertRedirect(route('pegawai.absensi.index'));
        $response->assertSessionHas('success', 'Data absensi berhasil disimpan. Lokasi terdeteksi di luar kantor dan telah dicatat untuk verifikasi.');
        $this->assertDatabaseHas('absensis', [
            'pegawai_id' => $pegawai->id,
            'status' => 'hadir',
            'di_luar_kantor' => true,
        ]);
    }

    public function test_attendance_is_locked_and_rejected_on_a_holiday(): void
    {
        [$user, $pegawai] = $this->createPegawai();
        Carbon::setTestNow('2026-09-26 08:00:00');

        try {
            $this->actingAs($user)
                ->get(route('pegawai.absensi.index'))
                ->assertOk()
                ->assertSee('Hari Libur')
                ->assertSee('<fieldset class="manual-disabled" disabled>', false);

            $this->actingAs($user)
                ->post(route('pegawai.absensi.store'))
                ->assertRedirect(route('pegawai.absensi.index'))
                ->assertSessionHas('error', 'Presensi tidak tersedia pada hari libur akhir pekan (Sabtu).');

            $this->assertDatabaseMissing('absensis', ['pegawai_id' => $pegawai->id]);
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_attendance_is_open_on_a_regular_workday(): void
    {
        [$user] = $this->createPegawai();
        Carbon::setTestNow('2026-09-28 08:00:00');

        try {
            $this->actingAs($user)
                ->get(route('pegawai.absensi.index'))
                ->assertOk()
                ->assertDontSee('id="holidayDialog"', false)
                ->assertDontSee('<fieldset class="manual-disabled"', false)
                ->assertDontSee('class="manual-page manual-atasan-page"', false)
                ->assertDontSee('manual-calendar-card', false)
                ->assertSee('data-show-on-load="false"', false);
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_pegawai_can_submit_masuk_and_pulang_only_once_per_day(): void
    {
        Storage::fake('public');
        [$user, $pegawai] = $this->createPegawai();
        Carbon::setTestNow('2026-09-28 08:00:00');

        try {
            $this->actingAs($user)
                ->post(route('pegawai.absensi.store'), $this->attendancePayload('masuk'))
                ->assertRedirect(route('pegawai.absensi.index'))
                ->assertSessionHas('success', 'Data absensi hari ini berhasil disimpan.');

            $this->actingAs($user)
                ->get(route('pegawai.absensi.index'))
                ->assertOk()
                ->assertSee('data-completed="true"', false)
                ->assertSee('Sudah tercatat');

            $duplicateMasuk = $this->actingAs($user)
                ->post(route('pegawai.absensi.store'), $this->attendancePayload('masuk'));
            $duplicateMasuk->assertRedirect(route('pegawai.absensi.index'))
                ->assertSessionHas('attendance_warning');

            $this->actingAs($user)
                ->get(route('pegawai.absensi.index'))
                ->assertOk()
                ->assertSee('data-show-on-load="true"', false)
                ->assertSee('Presensi Sudah Tercatat');

            $this->actingAs($user)
                ->post(route('pegawai.absensi.store'), $this->attendancePayload('pulang'))
                ->assertRedirect(route('pegawai.absensi.index'))
                ->assertSessionHas('success', 'Data absensi hari ini berhasil disimpan.');

            $duplicatePulang = $this->actingAs($user)
                ->post(route('pegawai.absensi.store'), $this->attendancePayload('pulang'));
            $duplicatePulang->assertRedirect(route('pegawai.absensi.index'))
                ->assertSessionHas('attendance_warning');

            $this->assertDatabaseHas('absensis', [
                'pegawai_id' => $pegawai->id,
                'jam_masuk' => '08:00:00',
                'jam_pulang' => '08:00:00',
            ]);
            $this->assertCount(2, Storage::disk('public')->allFiles('absensi'));
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_atasan_can_open_dashboard_and_record_their_own_attendance(): void
    {
        Storage::fake('public');
        [$user, $pegawai] = $this->createAtasan();
        Carbon::setTestNow('2026-09-28 08:00:00');

        try {
            $this->actingAs($user)
                ->get(route('atasan.dashboard'))
                ->assertOk()
                ->assertSee('Presensi Atasan')
                ->assertSee(route('atasan.absensi-saya.index'), false)
                ->assertSee('Isi Absensi');

            $this->actingAs($user)
                ->get(route('atasan.absensi-saya.index'))
                ->assertOk()
                ->assertSee('Presensi Atasan')
                ->assertSee(route('atasan.absensi-saya.store'), false)
                ->assertSee('manual-atasan-page', false)
                ->assertSee('desktop-atasan-attendance', false)
                ->assertSee('Waktu Sistem Server')
                ->assertSee('Validasi Geolokasi GPS')
                ->assertSee('Foto Absensi')
                ->assertSee('manual-attendance-layout', false)
                ->assertSee('Tanggal Absensi')
                ->assertSee('manual-calendar-card', false)
                ->assertSee('aria-current="date"', false);

            $this->actingAs($user)
                ->post(route('atasan.absensi-saya.store'), $this->attendancePayload('masuk'))
                ->assertRedirect(route('atasan.absensi-saya.index'))
                ->assertSessionHas('success', 'Data absensi hari ini berhasil disimpan.');

            $this->actingAs($user)
                ->get(route('atasan.dashboard'))
                ->assertOk()
                ->assertSee('Isi Absensi Pulang')
                ->assertSee(route('atasan.absensi-saya.index'), false)
                ->assertDontSee('is-attendance-complete');

            $this->actingAs($user)
                ->post(route('atasan.absensi-saya.store'), $this->attendancePayload('pulang'))
                ->assertRedirect(route('atasan.absensi-saya.index'))
                ->assertSessionHas('success', 'Data absensi hari ini berhasil disimpan.');

            $this->actingAs($user)
                ->get(route('atasan.absensi-saya.index'))
                ->assertOk()
                ->assertSee('is-today is-complete', false);

            $this->actingAs($user)
                ->get(route('atasan.dashboard'))
                ->assertOk()
                ->assertSee('Sudah Absensi')
                ->assertSee('is-attendance-complete')
                ->assertSee('Presensi masuk dan pulang hari ini sudah tercatat.')
                ->assertSee(route('atasan.absensi-saya.index'), false);

            $absensi = $pegawai->absensis()->firstOrFail();
            $this->actingAs($user)
                ->get(route('atasan.absensi-saya.show', $absensi))
                ->assertOk()
                ->assertSee('Detail Absensi')
                ->assertSee(route('atasan.dashboard'), false)
                ->assertSee(route('atasan.absensi-saya.index'), false);
        } finally {
            Carbon::setTestNow();
        }
    }

    private function attendancePayload(string $jenisPresensi): array
    {
        return [
            'status' => 'hadir',
            'jenis_presensi' => $jenisPresensi,
            'latitude' => -2.146435,
            'longitude' => 106.101327,
            'accuracy' => 5,
            'foto' => UploadedFile::fake()->create('presensi.jpg', 10, 'image/jpeg'),
        ];
    }

    private function createPegawai(): array
    {
        $divisi = Divisi::create(['nama_divisi' => 'Divisi Uji']);
        $jabatan = Jabatan::create([
            'divisi_id' => $divisi->id,
            'nama_jabatan' => 'Pegawai Uji',
            'grade' => 'Grade 1',
            'base_tukin' => 1500000,
        ]);
        $user = User::create([
            'name' => 'Pegawai Uji',
            'email' => 'pegawai.absensi@contoh.test',
            'password' => bcrypt('password'),
            'role' => 'pegawai',
        ]);
        $pegawai = Pegawai::create([
            'user_id' => $user->id,
            'nama' => 'Pegawai Uji',
            'jabatan_id' => $jabatan->id,
            'tanggal_masuk' => '2021-01-01',
            'status' => 'aktif',
        ]);

        return [$user, $pegawai];
    }

    private function createAtasan(): array
    {
        $divisi = Divisi::create(['nama_divisi' => 'Divisi Uji']);
        $jabatan = Jabatan::create([
            'divisi_id' => $divisi->id,
            'nama_jabatan' => 'Atasan Uji',
            'grade' => 'Grade 2',
            'base_tukin' => 2500000,
        ]);
        $user = User::create([
            'name' => 'Atasan Uji',
            'email' => 'atasan.absensi@contoh.test',
            'password' => bcrypt('password'),
            'role' => 'atasan',
        ]);
        $pegawai = Pegawai::create([
            'user_id' => $user->id,
            'nama' => 'Atasan Uji',
            'jabatan_id' => $jabatan->id,
            'tanggal_masuk' => '2020-01-01',
            'status' => 'aktif',
        ]);

        return [$user, $pegawai];
    }
}
