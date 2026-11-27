<?php

namespace Tests\Feature;

use App\Models\Absensi;
use App\Models\Divisi;
use App\Models\Jabatan;
use App\Models\MasterJamKerja;
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

    public function test_absen_masuk_is_rejected_after_the_configured_limit(): void
    {
        Storage::fake('public');
        [$user, $pegawai] = $this->createPegawai();
        $this->setAttendanceWindows([
            'jam_buka_absen_masuk' => '06:30:00',
            'batas_absen_masuk' => '07:45:00',
        ]);
        Carbon::setTestNow('2026-09-28 07:46:00');

        try {
            $this->actingAs($user)
                ->from(route('pegawai.absensi.index'))
                ->post(route('pegawai.absensi.store'), $this->attendancePayload('masuk'))
                ->assertRedirect(route('pegawai.absensi.index'))
                ->assertSessionHas('error', 'Batas absen masuk pukul 07:45 sudah lewat.');

            $this->assertDatabaseMissing('absensis', ['pegawai_id' => $pegawai->id]);
            $this->assertSame([], Storage::disk('public')->allFiles('absensi'));
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_absen_masuk_uses_wib_when_php_timezone_is_utc(): void
    {
        Storage::fake('public');
        [$user, $pegawai] = $this->createPegawai();
        $this->setAttendanceWindows([
            'jam_buka_absen_masuk' => '06:30:00',
            'batas_absen_masuk' => '15:00:00',
        ]);
        $originalTimezone = date_default_timezone_get();
        date_default_timezone_set('UTC');
        Carbon::setTestNow(Carbon::create(2026, 9, 28, 8, 0, 34, 'UTC'));

        try {
            $this->actingAs($user)
                ->from(route('pegawai.absensi.index'))
                ->post(route('pegawai.absensi.store'), $this->attendancePayload('masuk'))
                ->assertRedirect(route('pegawai.absensi.index'))
                ->assertSessionHas('error', 'Batas absen masuk pukul 15:00 sudah lewat.');

            $this->assertDatabaseMissing('absensis', ['pegawai_id' => $pegawai->id]);
        } finally {
            Carbon::setTestNow();
            date_default_timezone_set($originalTimezone);
        }
    }

    public function test_absen_masuk_is_rejected_before_the_configured_opening_time(): void
    {
        [$user, $pegawai] = $this->createPegawai();
        $this->setAttendanceWindows([
            'jam_buka_absen_masuk' => '06:30:00',
            'batas_absen_masuk' => '07:45:00',
        ]);
        Carbon::setTestNow('2026-09-28 06:29:00');

        try {
            $this->actingAs($user)
                ->from(route('pegawai.absensi.index'))
                ->post(route('pegawai.absensi.store'), $this->attendancePayload('masuk'))
                ->assertRedirect(route('pegawai.absensi.index'))
                ->assertSessionHas('error', 'Absen masuk baru dibuka pukul 06:30.');

            $this->assertDatabaseMissing('absensis', ['pegawai_id' => $pegawai->id]);
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_absen_pulang_is_rejected_after_the_configured_close_time(): void
    {
        [$user, $pegawai] = $this->createPegawai();
        $this->setAttendanceWindows([
            'jam_buka_absen_pulang' => '16:00:00',
            'jam_tutup_absen_pulang' => '18:00:00',
        ]);
        Absensi::create([
            'pegawai_id' => $pegawai->id,
            'tanggal' => '2026-09-28',
            'status' => 'hadir',
            'jam_masuk' => '07:30:00',
        ]);
        Carbon::setTestNow('2026-09-28 18:01:00');

        try {
            $this->actingAs($user)
                ->from(route('pegawai.absensi.index'))
                ->post(route('pegawai.absensi.store'), $this->attendancePayload('pulang'))
                ->assertRedirect(route('pegawai.absensi.index'))
                ->assertSessionHas('error', 'Batas absen pulang pukul 18:00 sudah lewat.');

            $this->assertDatabaseMissing('absensis', [
                'pegawai_id' => $pegawai->id,
                'jam_pulang' => '18:01:00',
            ]);
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_absen_masuk_is_allowed_at_the_configured_limit(): void
    {
        Storage::fake('public');
        [$user, $pegawai] = $this->createPegawai();
        $this->setAttendanceWindows([
            'jam_buka_absen_masuk' => '06:30:00',
            'batas_absen_masuk' => '07:45:00',
        ]);
        Carbon::setTestNow('2026-09-28 07:45:00');

        try {
            $this->actingAs($user)
                ->post(route('pegawai.absensi.store'), $this->attendancePayload('masuk'))
                ->assertRedirect(route('pegawai.absensi.index'))
                ->assertSessionHas('success', 'Presensi berhasil disimpan.');

            $this->assertDatabaseHas('absensis', [
                'pegawai_id' => $pegawai->id,
                'jam_masuk' => '07:45:00',
            ]);
        } finally {
            Carbon::setTestNow();
        }
    }

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
                ->assertSee('Hadir (Bekerja Di Luar)')
                ->assertSee('value="dinas_luar"', false)
                ->assertSee('Shift 1 (Operator)')
                ->assertSee('Shift 2 (Operator)')
                ->assertSee('Shift 3 (Operator)')
                ->assertSee('value="shift_1"', false)
                ->assertSee('value="shift_2"', false)
                ->assertSee('value="shift_3"', false)
                ->assertDontSee('id="holidayDialog"', false)
                ->assertDontSee('<fieldset class="manual-disabled"', false)
                ->assertDontSee('class="manual-page manual-atasan-page"', false)
                ->assertDontSee('manual-calendar-card', false)
                ->assertSee('data-show-on-load="false"', false);
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_pegawai_can_submit_attendance_as_working_outside_office(): void
    {
        Storage::fake('public');
        [$user, $pegawai] = $this->createPegawai();
        Carbon::setTestNow('2026-09-28 08:00:00');

        try {
            $payload = array_merge($this->attendancePayload('masuk'), [
                'status' => 'dinas_luar',
            ]);

            $this->actingAs($user)
                ->post(route('pegawai.absensi.store'), $payload)
                ->assertRedirect(route('pegawai.absensi.index'))
                ->assertSessionHas('success', 'Presensi berhasil disimpan.');

            $this->assertDatabaseHas('absensis', [
                'pegawai_id' => $pegawai->id,
                'status' => 'dinas_luar',
            ]);
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_pegawai_can_submit_attendance_with_a_selected_shift(): void
    {
        Storage::fake('public');
        [$user, $pegawai] = $this->createPegawai();
        MasterJamKerja::create([
            'hari' => 'Kerja',
            'jenis_jadwal' => 'shift',
            'nomor_shift' => 1,
            'jam_masuk' => '07:30:00',
            'jam_pulang' => '16:00:00',
            'toleransi_menit' => 15,
            'jam_buka_absen_masuk' => '00:00:00',
            'batas_absen_masuk' => '23:59:00',
            'jam_buka_absen_pulang' => '00:00:00',
            'jam_tutup_absen_pulang' => '23:59:00',
        ]);
        Carbon::setTestNow('2026-09-28 08:00:00');

        try {
            $payload = array_merge($this->attendancePayload('masuk'), [
                'status' => 'shift_1',
            ]);

            $this->actingAs($user)
                ->post(route('pegawai.absensi.store'), $payload)
                ->assertRedirect(route('pegawai.absensi.index'))
                ->assertSessionHas('success', 'Presensi berhasil disimpan.');

            $this->assertDatabaseHas('absensis', [
                'pegawai_id' => $pegawai->id,
                'status' => 'shift_1',
            ]);
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_shift_three_attendance_can_be_completed_after_midnight(): void
    {
        Storage::fake('public');
        [$user, $pegawai] = $this->createPegawai();
        MasterJamKerja::create([
            'hari' => 'Senin',
            'jenis_jadwal' => 'shift',
            'nomor_shift' => 3,
            'jam_masuk' => '23:00:00',
            'jam_pulang' => '07:00:00',
            'toleransi_menit' => 15,
            'jam_buka_absen_masuk' => '22:00:00',
            'batas_absen_masuk' => '23:30:00',
            'jam_buka_absen_pulang' => '06:30:00',
            'jam_tutup_absen_pulang' => '08:00:00',
        ]);
        Carbon::setTestNow('2026-09-28 23:10:00');

        try {
            $payloadMasuk = array_merge($this->attendancePayload('masuk'), [
                'status' => 'shift_3',
            ]);

            $this->actingAs($user)
                ->post(route('pegawai.absensi.store'), $payloadMasuk)
                ->assertRedirect(route('pegawai.absensi.index'))
                ->assertSessionHas('success', 'Presensi berhasil disimpan.');

            $this->assertTrue(Absensi::query()
                ->where('pegawai_id', $pegawai->id)
                ->whereDate('tanggal', '2026-09-28')
                ->where('status', 'shift_3')
                ->where('jam_masuk', '23:10:00')
                ->exists());

            Carbon::setTestNow('2026-09-29 07:00:00');
            $this->actingAs($user)
                ->get(route('pegawai.absensi.index'))
                ->assertOk()
                ->assertSee('name="status" value="shift_3"', false)
                ->assertSee('Pulang');

            $payloadPulang = array_merge($this->attendancePayload('pulang'), [
                'status' => 'shift_3',
            ]);

            $this->actingAs($user)
                ->post(route('pegawai.absensi.store'), $payloadPulang)
                ->assertRedirect(route('pegawai.absensi.index'))
                ->assertSessionHas('success', 'Presensi berhasil disimpan.');

            $this->assertTrue(Absensi::query()
                ->where('pegawai_id', $pegawai->id)
                ->whereDate('tanggal', '2026-09-28')
                ->where('status', 'shift_3')
                ->where('jam_pulang', '07:00:00')
                ->exists());
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
                ->assertDontSee('manual-atasan-page', false)
                ->assertSee('desktop-atasan-attendance', false)
                ->assertSee('Waktu Sistem Server')
                ->assertSee('Validasi Geolokasi GPS')
                ->assertSee('Foto Absensi')
                ->assertSee('manual-attendance-layout', false)
                ->assertDontSee('Tanggal Absensi')
                ->assertDontSee('manual-calendar-card', false);

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

    public function test_atasan_attendance_uses_atasan_navigation_without_pegawai_items(): void
    {
        [$user] = $this->createAtasan();

        $this->actingAs($user)
            ->get(route('atasan.absensi-saya.index'))
            ->assertOk()
            ->assertSee('class="navbar navbar-sikinerja navbar-expand"', false)
            ->assertSee('id="sidebar"', false)
            ->assertSee('mobile-bottom-nav', false)
            ->assertSee('attendance-dashboard-page', false)
            ->assertDontSee('manual-atasan-page', false)
            ->assertSee(route('atasan.dashboard'), false)
            ->assertSee(route('atasan.laporan.index'), false)
            ->assertSee(route('atasan.absensi.index'), false)
            ->assertSee(route('atasan.pegawai.index'), false)
            ->assertSee(route('atasan.hasil-penilaian.index'), false)
            ->assertDontSee('pegawai-navigation', false)
            ->assertDontSee(route('pegawai.dashboard'), false)
            ->assertDontSee(route('pegawai.laporan-harian.create'), false)
            ->assertDontSee('id="pegawaiProfileTrigger"', false)
            ->assertDontSee('id="pegawaiNotificationTrigger"', false)
            ->assertDontSee('manual-calendar-card', false);
    }

    public function test_atasan_dashboard_marks_both_attendance_records_with_red_state(): void
    {
        [$user, $pegawai] = $this->createAtasan();
        Absensi::create([
            'pegawai_id' => $pegawai->id,
            'tanggal' => now()->toDateString(),
            'status' => 'hadir',
            'jam_masuk' => '08:00:00',
            'jam_pulang' => '16:00:00',
        ]);

        $this->actingAs($user)
            ->get(route('atasan.dashboard'))
            ->assertOk()
            ->assertSee('is-attendance-complete', false)
            ->assertSee('Sudah Absensi')
            ->assertSee('Presensi masuk dan pulang hari ini sudah tercatat.')
            ->assertDontSee('Isi Absensi');
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
        $this->createAttendanceSchedule();

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
        $this->createAttendanceSchedule();

        return [$user, $pegawai];
    }

    private function createAttendanceSchedule(): void
    {
        MasterJamKerja::create([
            'hari' => 'Kerja',
            'jam_masuk' => '07:30:00',
            'jam_pulang' => '16:00:00',
            'toleransi_menit' => 15,
            'jam_buka_absen_masuk' => '00:00:00',
            'batas_absen_masuk' => '23:59:00',
            'jam_buka_absen_pulang' => '00:00:00',
            'jam_tutup_absen_pulang' => '23:59:00',
        ]);
    }

    private function setAttendanceWindows(array $values): void
    {
        MasterJamKerja::where('hari', 'Kerja')->update($values);
    }
}
