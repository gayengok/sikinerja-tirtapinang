<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Divisi;
use App\Models\Jabatan;
use App\Models\Pegawai;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AtasanRegistrationController extends Controller
{
    public function create(Request $request)
    {
        if ($request->user()) {
            return redirect()->route('atasan.login');
        }

        $divisis = Divisi::orderBy('nama_divisi')->get();
        $jabatans = Jabatan::with('divisi')
            ->where('nama_jabatan', 'like', 'Kepala%')
            ->orderBy('nama_jabatan')
            ->get();

        return view('auth.atasan.register_atasan', compact('divisis', 'jabatans'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
            'jabatan_id' => [
                'required',
                Rule::exists('jabatans', 'id')->where(
                    fn ($query) => $query->where('nama_jabatan', 'like', 'Kepala%')
                ),
            ],
            'tanggal_masuk' => ['nullable', 'date'],
            'status' => ['required', 'in:aktif,nonaktif'],
        ], [
            'nama.required' => 'Nama wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah terdaftar.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal 6 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'jabatan_id.required' => 'Jabatan wajib dipilih.',
            'jabatan_id.exists' => 'Jabatan Atasan harus merupakan jabatan Kepala.',
            'tanggal_masuk.date' => 'Tanggal masuk tidak valid.',
            'status.required' => 'Status wajib dipilih.',
        ]);

        DB::transaction(function () use ($validated) {
            $user = User::create([
                'name' => $validated['nama'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => 'atasan',
            ]);

            Pegawai::create([
                'user_id' => $user->id,
                'nama' => $validated['nama'],
                'jabatan_id' => $validated['jabatan_id'],
                'atasan_id' => null,
                'tanggal_masuk' => $validated['tanggal_masuk'] ?? null,
                'status' => $validated['status'],
            ]);
        });

        return redirect()->route('atasan.login')
            ->with('success', 'Registrasi Atasan berhasil. Silakan masuk dengan akun Anda.');
    }
}
