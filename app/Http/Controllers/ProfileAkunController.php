<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class ProfileAkunController extends Controller
{
    private const ROLES = [
        'master',
        'atasan',
        'kepala_bagian',
        'direktur',
    ];

    private function authorizeRole(Request $request): void
    {
        abort_unless(
            in_array($request->user()?->role, self::ROLES, true),
            403
        );
    }

    public function index(Request $request)
    {
        $this->authorizeRole($request);

        $user = $request->user()->loadMissing('pegawai');
        $pegawai = $user->pegawai;

        $foto = null;

        if (Schema::hasColumn($user->getTable(), 'foto') && $user->foto) {
            $foto = Storage::disk('public')->url($user->foto);
        }

        return view('components.profile', compact('user', 'pegawai', 'foto'));
    }

    public function update(Request $request)
    {
        $this->authorizeRole($request);

        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique($user->getTable(), 'email')->ignore($user->getKey(), $user->getKeyName()),
            ],
            'password' => ['nullable', 'confirmed', Password::defaults()],
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        return redirect()
            ->route('profile.index')
            ->with('success', 'Informasi akun berhasil diperbarui.');
    }

    public function updatePhoto(Request $request)
    {
        $this->authorizeRole($request);

        $user = $request->user();

        if (!Schema::hasColumn($user->getTable(), 'foto')) {
            return redirect()
                ->route('profile.index')
                ->with('error', 'Kolom foto pada tabel pengguna belum tersedia.');
        }

        $validated = $request->validate([
            'foto' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $oldPhoto = $user->foto;
        $path = $validated['foto']->store('foto-profil', 'public');

        try {
            $user->foto = $path;
            $user->save();
        } catch (\Throwable $exception) {
            Storage::disk('public')->delete($path);
            throw $exception;
        }

        if ($oldPhoto && $oldPhoto !== $path) {
            Storage::disk('public')->delete($oldPhoto);
        }

        return redirect()
            ->route('profile.index')
            ->with('success', 'Foto profil berhasil diperbarui.');
    }
}