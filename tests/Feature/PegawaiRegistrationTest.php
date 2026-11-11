<?php

namespace Tests\Feature;

use App\Models\Divisi;
use App\Models\Jabatan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PegawaiRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_pegawai_registration_does_not_list_kepala_positions(): void
    {
        $divisi = Divisi::create(['nama_divisi' => 'Divisi Uji']);
        Jabatan::create([
            'divisi_id' => $divisi->id,
            'nama_jabatan' => 'Kepala Divisi Uji',
            'grade' => 'Grade 1',
        ]);
        Jabatan::create([
            'divisi_id' => $divisi->id,
            'nama_jabatan' => 'Staf Divisi Uji',
            'grade' => 'Grade 2',
        ]);

        $this->get(route('register'))
            ->assertOk()
            ->assertSee('Staf Divisi Uji')
            ->assertDontSee('Kepala Divisi Uji');
    }

    public function test_pegawai_registration_rejects_a_kepala_position_submitted_directly(): void
    {
        $divisi = Divisi::create(['nama_divisi' => 'Divisi Uji']);
        $jabatanKepala = Jabatan::create([
            'divisi_id' => $divisi->id,
            'nama_jabatan' => 'Kepala Divisi Uji',
            'grade' => 'Grade 1',
        ]);

        $this->post(route('register.store'), ['jabatan_id' => $jabatanKepala->id])
            ->assertSessionHasErrors('jabatan_id');
    }
}
