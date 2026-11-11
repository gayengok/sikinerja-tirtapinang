<?php

namespace App\Http\Controllers\Pegawai;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function index(Request $request)
    {
        $pegawai = $this->pegawaiLogin($request);

        $pegawai->load(['user', 'jabatan.divisi', 'atasan']);

        return view('pegawai.profile.index', compact('pegawai'));
    }

    public function update(Request $request)
    {
        $pegawai = $this->pegawaiLogin($request);
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:6', 'confirmed'],
        ], [
            'name.required' => 'Nama wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah digunakan oleh akun lain.',
            'password.min' => 'Password minimal 6 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak sesuai.',
        ]);

        $dataUpdate = [
            'name' => $validated['name'],
            'email' => $validated['email'],
        ];

        // Jika password tidak diisi, password lama tetap digunakan.
        if (!empty($validated['password'])) {
            $dataUpdate['password'] = Hash::make($validated['password']);
        }

        // Sengaja tidak menyertakan: role, jabatan_id, atasan_id, status.
        // Field tersebut hanya dapat diubah oleh Master.
        $user->update($dataUpdate);

        $pegawai->update([
            'nama' => $validated['name'],
        ]);

        return redirect()
            ->route('pegawai.profile.index')
            ->with('success', 'Profil berhasil diperbarui.');
    }

    private function pegawaiLogin(Request $request)
    {
        $pegawai = $request->user()->pegawai;

        abort_if(!$pegawai, 403, 'Data pegawai untuk akun Anda tidak ditemukan.');

        return $pegawai;
    }
}
