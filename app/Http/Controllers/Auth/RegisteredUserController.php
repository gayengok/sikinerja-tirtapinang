<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Divisi;
use App\Models\Jabatan;
use App\Models\Pegawai;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class RegisteredUserController extends Controller
{
    public function create(Request $request)
    {
        if ($request->user()) {
            return redirect()->route('login');
        }

        $divisis = Divisi::orderBy('nama_divisi')->get();
        $jabatans = Jabatan::with('divisi')
            ->where('nama_jabatan', 'not like', 'Kepala%')
            ->orderBy('nama_jabatan')
            ->get();
        $atasans = Pegawai::whereHas('user', function ($query) {
            $query->where('role', 'atasan');
        })
            ->where('status', 'aktif')
            ->with('jabatan')
            ->orderBy('nama')
            ->get();

        return view('auth.register', compact('divisis', 'jabatans', 'atasans'));
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
                    fn ($query) => $query->where('nama_jabatan', 'not like', 'Kepala%')
                ),
            ],
            'atasan_id' => [
                'required',
                'exists:pegawais,id',
                function ($attribute, $value, $fail) use ($request) {
                    $jabatan = Jabatan::find($request->input('jabatan_id'));
                    $atasan = Pegawai::whereHas('user', function ($query) {
                        $query->where('role', 'atasan');
                    })->with('jabatan')->find($value);

                    if (!$jabatan || !$atasan || $atasan->status !== 'aktif') {
                        $fail('Atasan yang dipilih tidak valid atau tidak aktif.');
                        return;
                    }

                    if (!$atasan->jabatan || $atasan->jabatan->divisi_id !== $jabatan->divisi_id) {
                        $fail('Atasan harus berasal dari divisi yang sama dengan jabatan.');
                    }
                },
            ],
            'tanggal_masuk' => ['nullable', 'date'],
            'status' => ['required', Rule::in(['aktif', 'nonaktif'])],
        ], [
            'nama.required' => 'Nama wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah terdaftar.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal 6 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'jabatan_id.required' => 'Jabatan wajib dipilih.',
            'jabatan_id.exists' => 'Jabatan tidak valid atau merupakan jabatan khusus atasan.',
            'atasan_id.required' => 'Atasan langsung wajib dipilih.',
            'atasan_id.exists' => 'Atasan yang dipilih tidak valid.',
            'tanggal_masuk.date' => 'Tanggal masuk tidak valid.',
            'status.required' => 'Status wajib dipilih.',
        ]);

        DB::transaction(function () use ($validated) {
            $user = User::create([
                'name' => $validated['nama'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => 'pegawai',
            ]);

            Pegawai::create([
                'user_id' => $user->id,
                'nama' => $validated['nama'],
                'jabatan_id' => $validated['jabatan_id'],
                'atasan_id' => $validated['atasan_id'],
                'tanggal_masuk' => $validated['tanggal_masuk'] ?? null,
                'status' => $validated['status'],
            ]);
        });

        return redirect()->route('login')->with('success', 'Registrasi berhasil. Silakan masuk dengan akun Anda.');
    }
}
