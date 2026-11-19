<?php

namespace Tests\Feature;

use App\Models\Divisi;
use App\Models\Jabatan;
use App\Models\Pegawai;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PegawaiProfilePhotoTest extends TestCase
{
    use RefreshDatabase;

    public function test_uploaded_photo_uses_a_same_origin_storage_url(): void
    {
        Storage::fake('public');

        /** @var User $user */
        $user = User::factory()->create(['role' => 'pegawai']);
        $divisi = Divisi::create(['nama_divisi' => 'Operasional']);
        $jabatan = Jabatan::create([
            'divisi_id' => $divisi->id,
            'nama_jabatan' => 'Staf',
        ]);

        Pegawai::create([
            'user_id' => $user->id,
            'nama' => $user->name,
            'jabatan_id' => $jabatan->id,
        ]);

        $response = $this->actingAs($user)
            ->put(route('pegawai.profile.foto'), [
                'foto_profil' => UploadedFile::fake()
                    ->create('profil.jpg', 10)
                    ->mimeType('image/jpeg'),
            ]);

        $photoPath = Storage::disk('public')->allFiles('foto-profil')[0];

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('foto_url', '/storage/' . $photoPath);

        $this->assertTrue(Storage::disk('public')->exists($photoPath));

        $this->get(route('pegawai.profile.index'))
            ->assertOk()
            ->assertSee('src="/storage/' . $photoPath . '"', false);
    }
}
