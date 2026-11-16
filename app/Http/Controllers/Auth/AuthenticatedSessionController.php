<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthenticatedSessionController extends Controller
{
    /**
     * Tampilkan halaman login.
     * Jika user sudah login, langsung arahkan ke dashboard sesuai role
     * (tidak mengandalkan middleware "guest" bawaan Laravel yang mengarah ke /dashboard).
     */
    public function create(Request $request)
    {
        if ($request->user()) {
            return $this->redirectByRole($request->user());
        }

        return view('auth.login', [
            'loginRoute' => 'login.store',
            'loginTitle' => 'Masuk SIKINERJA',
            'loginButton' => 'Masuk',
        ]);
    }

    /**
     * Proses login.
     */
    public function store(Request $request): RedirectResponse
    {
        return $this->authenticate($request);
    }

    private function authenticate(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'password.required' => 'Password wajib diisi.',
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => 'Email atau password yang Anda masukkan salah.',
            ]);
        }

        // Wajib untuk mencegah session fixation.
        $request->session()->regenerate();

        return $this->redirectByRole($request->user());
    }

    /**
     * Proses logout.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Anda berhasil logout.');
    }

    /**
     * Redirect user ke dashboard sesuai role.
     * Jika role tidak dikenali, paksa logout agar akun tidak masuk ke dashboard yang salah.
     */
    private function redirectByRole(User $user): RedirectResponse
    {
        return match ($user->role) {
            'master' => redirect()->intended(route('master.dashboard')),
            'direktur' => redirect()->intended(route('direktur.dashboard')),
            'kepala_bagian' => redirect()->intended(route('kepala_bagian.dashboard')),
            'atasan' => redirect()->intended(route('atasan.dashboard')),
            'pegawai' => redirect()->intended(route('pegawai.dashboard')),
            default => $this->forceLogoutInvalidRole(),
        };
    }

    private function forceLogoutInvalidRole(): RedirectResponse
    {
        Auth::guard('web')->logout();

        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()->route('login')->with('error', 'Akun Anda tidak memiliki role yang valid. Hubungi Administrator.');
    }
}
