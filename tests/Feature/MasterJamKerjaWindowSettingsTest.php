<?php

namespace Tests\Feature;

use App\Models\MasterJamKerja;
use App\Models\User;
use Database\Seeders\JamKerjaSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MasterJamKerjaWindowSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_schedule_seeder_populates_custom_attendance_windows(): void
    {
        $this->seed(JamKerjaSeeder::class);

        $this->assertDatabaseHas('master_jam_kerjas', [
            'hari' => 'Senin',
            'jam_buka_absen_masuk' => '06:30:00',
            'batas_absen_masuk' => '07:45:00',
            'jam_buka_absen_pulang' => '16:00:00',
            'jam_tutup_absen_pulang' => '18:00:00',
        ]);
        $this->assertDatabaseHas('master_jam_kerjas', [
            'hari' => 'Jumat',
            'jam_buka_absen_masuk' => '06:30:00',
            'batas_absen_masuk' => '07:45:00',
            'jam_buka_absen_pulang' => '16:30:00',
            'jam_tutup_absen_pulang' => '18:30:00',
        ]);
        $this->assertDatabaseCount('master_jam_kerjas', 8);
        $this->assertDatabaseHas('master_jam_kerjas', [
            'hari' => 'Kerja',
            'jenis_jadwal' => 'shift',
            'nomor_shift' => 1,
            'jam_masuk' => '07:00:00',
            'jam_pulang' => '15:00:00',
            'jam_buka_absen_masuk' => '06:00:00',
            'batas_absen_masuk' => '07:15:00',
            'jam_buka_absen_pulang' => '15:00:00',
            'jam_tutup_absen_pulang' => '17:00:00',
        ]);
        $this->assertDatabaseHas('master_jam_kerjas', [
            'hari' => 'Kerja',
            'jenis_jadwal' => 'shift',
            'nomor_shift' => 2,
            'jam_masuk' => '15:00:00',
            'jam_pulang' => '23:00:00',
            'jam_tutup_absen_pulang' => '23:59:00',
        ]);
        $this->assertDatabaseHas('master_jam_kerjas', [
            'hari' => 'Kerja',
            'jenis_jadwal' => 'shift',
            'nomor_shift' => 3,
            'jam_masuk' => '23:00:00',
            'jam_pulang' => '07:00:00',
            'jam_buka_absen_pulang' => '07:00:00',
            'jam_tutup_absen_pulang' => '09:00:00',
        ]);

        MasterJamKerja::where('hari', 'Senin')
            ->update(['jam_buka_absen_masuk' => '06:10:00']);
        MasterJamKerja::where('jenis_jadwal', 'shift')
            ->where('nomor_shift', 1)
            ->update(['jam_buka_absen_masuk' => '05:45:00']);
        $this->seed(JamKerjaSeeder::class);

        $this->assertDatabaseHas('master_jam_kerjas', [
            'hari' => 'Senin',
            'jam_buka_absen_masuk' => '06:10:00',
        ]);
        $this->assertDatabaseHas('master_jam_kerjas', [
            'jenis_jadwal' => 'shift',
            'nomor_shift' => 1,
            'jam_buka_absen_masuk' => '05:45:00',
        ]);
    }

    public function test_master_can_create_and_update_manual_attendance_windows(): void
    {
        $master = User::create([
            'name' => 'Admin',
            'email' => 'admin@example.test',
            'password' => 'password-admin',
            'role' => 'master',
        ]);

        $this->actingAs($master)
            ->get(route('master.jam-kerja.create'))
            ->assertOk()
            ->assertSee('name="jam_buka_absen_masuk"', false)
            ->assertSee('name="batas_absen_masuk"', false)
            ->assertSee('name="jam_buka_absen_pulang"', false)
            ->assertSee('name="jam_tutup_absen_pulang"', false);

        $this->actingAs($master)
            ->post(route('master.jam-kerja.store'), $this->jadwalData())
            ->assertRedirect(route('master.jam-kerja.index'));

        $jamKerja = MasterJamKerja::firstOrFail();
        $this->assertSame('06:20', $jamKerja->jam_buka_absen_masuk);
        $this->assertSame('07:55', $jamKerja->batas_absen_masuk);
        $this->assertSame('16:15', $jamKerja->jam_buka_absen_pulang);
        $this->assertSame('19:00', $jamKerja->jam_tutup_absen_pulang);

        $this->actingAs($master)
            ->get(route('master.jam-kerja.index'))
            ->assertOk()
            ->assertSee('Rentang: 06:20')
            ->assertSee('07:55')
            ->assertSee('Rentang: 16:15')
            ->assertSee('19:00');

        $this->actingAs($master)
            ->get(route('master.jam-kerja.edit', $jamKerja))
            ->assertOk()
            ->assertSee('name="jam_buka_absen_masuk"', false)
            ->assertSee('value="06:20"', false)
            ->assertSee('name="jam_tutup_absen_pulang"', false)
            ->assertSee('value="19:00"', false);

        $this->actingAs($master)
            ->put(route('master.jam-kerja.update', $jamKerja), $this->jadwalData([
                'jam_buka_absen_masuk' => '06:40',
                'batas_absen_masuk' => '08:00',
                'jam_buka_absen_pulang' => '16:30',
                'jam_tutup_absen_pulang' => '18:45',
            ]))
            ->assertRedirect(route('master.jam-kerja.index'));

        $this->assertDatabaseHas('master_jam_kerjas', [
            'id' => $jamKerja->id,
            'jam_buka_absen_masuk' => '06:40',
            'batas_absen_masuk' => '08:00',
            'jam_buka_absen_pulang' => '16:30',
            'jam_tutup_absen_pulang' => '18:45',
        ]);
    }

    public function test_master_cannot_save_a_window_that_ends_before_it_opens(): void
    {
        $master = User::create([
            'name' => 'Admin',
            'email' => 'admin@example.test',
            'password' => 'password-admin',
            'role' => 'master',
        ]);

        $this->actingAs($master)
            ->from(route('master.jam-kerja.create'))
            ->post(route('master.jam-kerja.store'), $this->jadwalData([
                'batas_absen_masuk' => '06:19',
                'jam_tutup_absen_pulang' => '16:14',
            ]))
            ->assertRedirect(route('master.jam-kerja.create'))
            ->assertSessionHasErrors([
                'batas_absen_masuk',
                'jam_tutup_absen_pulang',
            ]);

        $this->assertDatabaseCount('master_jam_kerjas', 0);
    }

    public function test_master_can_create_a_shift_three_schedule_that_ends_the_next_day(): void
    {
        $master = User::create([
            'name' => 'Admin',
            'email' => 'admin@example.test',
            'password' => 'password-admin',
            'role' => 'master',
        ]);

        $this->actingAs($master)
            ->get(route('master.jam-kerja.index', ['jenis_jadwal' => 'shift']))
            ->assertOk()
            ->assertSee('Jam Shift');

        $this->actingAs($master)
            ->get(route('master.jam-kerja.create', ['jenis_jadwal' => 'shift']))
            ->assertOk()
            ->assertSee('name="jenis_jadwal" value="shift"', false)
            ->assertSee('name="nomor_shift"', false)
            ->assertSee('Shift 1 (Operator)')
            ->assertSee('Shift 2 (Operator)')
            ->assertSee('Shift 3 (Operator)');

        $this->actingAs($master)
            ->post(route('master.jam-kerja.store'), $this->jadwalData([
                'jenis_jadwal' => 'shift',
                'nomor_shift' => 3,
                'jam_masuk' => '23:00',
                'jam_pulang' => '07:00',
                'jam_buka_absen_masuk' => '22:00',
                'batas_absen_masuk' => '23:30',
                'jam_buka_absen_pulang' => '06:30',
                'jam_tutup_absen_pulang' => '08:00',
            ]))
            ->assertRedirect(route('master.jam-kerja.index', ['jenis_jadwal' => 'shift']));

        $this->assertDatabaseHas('master_jam_kerjas', [
            'jenis_jadwal' => 'shift',
            'nomor_shift' => 3,
            'jam_masuk' => '23:00',
            'jam_pulang' => '07:00',
        ]);
    }

    private function jadwalData(array $overrides = []): array
    {
        return array_merge([
            'jabatan_id' => '',
            'hari' => 'Senin',
            'jam_masuk' => '07:30',
            'jam_pulang' => '16:30',
            'toleransi_menit' => 15,
            'jam_buka_absen_masuk' => '06:20',
            'batas_absen_masuk' => '07:55',
            'jam_buka_absen_pulang' => '16:15',
            'jam_tutup_absen_pulang' => '19:00',
        ], $overrides);
    }
}
