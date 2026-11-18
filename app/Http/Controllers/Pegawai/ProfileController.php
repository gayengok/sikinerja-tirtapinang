<?php

namespace App\Http\Controllers\Pegawai;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function index(Request $request)
    {
        $pegawai = $this->pegawaiLogin($request);

        $pegawai->load([
            'user',
            'jabatan.divisi',
            'atasan',
        ]);

        return view('pegawai.profile.index', compact('pegawai'));
    }

    public function update(Request $request)
    {
        $pegawai = $this->pegawaiLogin($request);
        $user = $request->user();

        $validated = $request->validate([
            'nip' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('pegawais', 'nip')->ignore($pegawai->id),
            ],
            'name' => [
                'required',
                'string',
                'max:255',
            ],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'password' => [
                'nullable',
                'string',
                'min:6',
                'confirmed',
            ],
        ], [
            'nip.unique' => 'NIP sudah digunakan pegawai lain.',
            'name.required' => 'Nama wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah digunakan akun lain.',
            'password.min' => 'Password minimal 6 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak sesuai.',
        ]);

        DB::transaction(function () use ($pegawai, $user, $validated) {
            $dataUser = [
                'name' => $validated['name'],
                'email' => $validated['email'],
            ];

            if (!empty($validated['password'])) {
                $dataUser['password'] = Hash::make($validated['password']);
            }

            $user->update($dataUser);

            $pegawai->update([
                'nama' => $validated['name'],
                'nip' => $validated['nip'] ?? null,
            ]);
        });

        return redirect()
            ->route('pegawai.profile.index')
            ->with('success', 'Informasi akun berhasil diperbarui.');
    }

    public function updateFoto(Request $request)
    {
        $pegawai = $this->pegawaiLogin($request);

        $request->validate([
            'foto_profil' => [
                'required',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],
        ], [
            'foto_profil.required' => 'Silakan pilih foto profil.',
            'foto_profil.image' => 'File harus berupa gambar.',
            'foto_profil.mimes' => 'Foto harus JPG, PNG, atau WebP.',
            'foto_profil.max' => 'Ukuran foto maksimal 2 MB.',
        ]);

        $disk = Storage::disk('public');

        $fotoLama = $pegawai->foto_profil;

        // Simpan file baru ke disk public.
        $fotoBaru = $request->file('foto_profil')
            ->store('foto-profil', 'public');

        // Jangan mengubah database jika file gagal disimpan.
        if (!$fotoBaru || !$disk->exists($fotoBaru)) {
            return response()->json([
                'success' => false,
                'message' => 'Foto gagal disimpan ke Storage.',
            ], 500);
        }

        try {
            // Update database setelah file baru tersedia.
            $pegawai->update([
                'foto_profil' => $fotoBaru,
            ]);
        } catch (\Throwable $e) {
            // Bersihkan file baru jika database gagal.
            $disk->delete($fotoBaru);

            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui foto profil.',
            ], 500);
        }

        // Hapus foto lama hanya setelah database berhasil diperbarui.
        if (
            $fotoLama &&
            $fotoLama !== $fotoBaru &&
            str_starts_with($fotoLama, 'foto-profil/')
        ) {
            try {
                $disk->delete($fotoLama);
            } catch (\Throwable $e) {
                report($e);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Foto profil berhasil disimpan.',
            'foto_url' => $disk->url($fotoBaru),
        ]);
    }

    private function pegawaiLogin(Request $request)
    {
        $pegawai = $request->user()->pegawai;

        abort_if(
            !$pegawai,
            403,
            'Data pegawai untuk akun Anda tidak ditemukan.'
        );

        return $pegawai;
    }
}
