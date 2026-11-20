<?php

namespace Tests\Feature;

use App\Models\Divisi;
use App\Models\Jabatan;
use App\Models\Pegawai;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class MasterPegawaiIdentityTest extends TestCase
{
    use RefreshDatabase;

    public function test_master_can_create_pegawai_with_nip(): void
    {
        [$master, $jabatan] = $this->buatMasterDanJabatan();

        $this->actingAs($master)
            ->get(route('master.pegawai.create'))
            ->assertOk()
            ->assertSee('name="nip"', false)
            ->assertSee('name="password"', false);

        $this->actingAs($master)
            ->post(route('master.pegawai.store'), [
                'nama' => 'Budi Santoso',
                'nip' => '1987654321',
                'email' => 'budi@example.test',
                'password' => 'rahasia123',
                'role' => 'direktur',
                'jabatan_id' => $jabatan->id,
                'atasan_id' => '',
                'status' => 'aktif',
            ])
            ->assertRedirect(route('master.pegawai.index'));

        $pegawai = Pegawai::where('nip', '1987654321')->firstOrFail();
        $this->assertSame('Budi Santoso', $pegawai->nama);
        $this->assertTrue(Hash::check('rahasia123', $pegawai->user->password));
    }

    public function test_master_can_update_nip_and_reset_pegawai_password(): void
    {
        [$master, $jabatan] = $this->buatMasterDanJabatan();
        $user = User::create([
            'name' => 'Pegawai Lama',
            'email' => 'pegawai@example.test',
            'password' => 'password-lama',
            'role' => 'direktur',
        ]);
        $pegawai = Pegawai::create([
            'user_id' => $user->id,
            'nama' => 'Pegawai Lama',
            'nip' => '1234567890',
            'jabatan_id' => $jabatan->id,
            'status' => 'aktif',
        ]);

        $this->actingAs($master)
            ->get(route('master.pegawai.edit', $pegawai))
            ->assertOk()
            ->assertSee('name="nip"', false)
            ->assertSee('Reset Password');

        $this->actingAs($master)
            ->put(route('master.pegawai.update', $pegawai), [
                'nama' => 'Pegawai Baru',
                'nip' => '0987654321',
                'email' => 'pegawai@example.test',
                'password' => 'password-baru',
                'role' => 'direktur',
                'jabatan_id' => $jabatan->id,
                'atasan_id' => '',
                'status' => 'aktif',
            ])
            ->assertRedirect(route('master.pegawai.index'));

        $pegawai->refresh();
        $user->refresh();
        $this->assertSame('0987654321', $pegawai->nip);
        $this->assertTrue(Hash::check('password-baru', $user->password));
    }

    public function test_nip_must_be_unique_when_creating_pegawai(): void
    {
        [$master, $jabatan] = $this->buatMasterDanJabatan();
        $user = User::create([
            'name' => 'Pegawai Terdaftar',
            'email' => 'terdaftar@example.test',
            'password' => 'password-lama',
            'role' => 'direktur',
        ]);
        Pegawai::create([
            'user_id' => $user->id,
            'nama' => 'Pegawai Terdaftar',
            'nip' => '111222333',
            'jabatan_id' => $jabatan->id,
            'status' => 'aktif',
        ]);

        $this->actingAs($master)
            ->from(route('master.pegawai.create'))
            ->post(route('master.pegawai.store'), [
                'nama' => 'Pegawai Duplikat',
                'nip' => '111222333',
                'email' => 'duplikat@example.test',
                'password' => 'rahasia123',
                'role' => 'direktur',
                'jabatan_id' => $jabatan->id,
                'atasan_id' => '',
                'status' => 'aktif',
            ])
            ->assertRedirect(route('master.pegawai.create'))
            ->assertSessionHasErrors('nip');
    }

    public function test_admin_pegawai_list_displays_nip(): void
    {
        [$master, $jabatan] = $this->buatMasterDanJabatan();
        $user = User::create([
            'name' => 'Pegawai Tampil',
            'email' => 'tampil@example.test',
            'password' => 'password-lama',
            'role' => 'direktur',
        ]);
        Pegawai::create([
            'user_id' => $user->id,
            'nama' => 'Pegawai Tampil',
            'nip' => '987654321',
            'jabatan_id' => $jabatan->id,
            'status' => 'aktif',
        ]);

        $this->actingAs($master)
            ->get(route('master.pegawai.index'))
            ->assertOk()
            ->assertSee('NIP')
            ->assertSee('987654321');
    }

    private function buatMasterDanJabatan(): array
    {
        $master = User::create([
            'name' => 'Admin',
            'email' => 'admin@example.test',
            'password' => 'password-admin',
            'role' => 'master',
        ]);
        $divisi = Divisi::create(['nama_divisi' => 'Umum']);
        $jabatan = Jabatan::create([
            'divisi_id' => $divisi->id,
            'nama_jabatan' => 'Direktur',
            'base_tukin' => 0,
        ]);

        return [$master, $jabatan];
    }
}
