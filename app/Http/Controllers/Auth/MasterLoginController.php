<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class MasterLoginController extends Controller
{
    public function create(Request $request)
    {
        if ($request->user()) {
            return redirect()->intended(route('master.dashboard'));
        }

        return view('auth.master.login_master', [
            'loginRoute' => 'master.login.store',
            'loginTitle' => 'Masuk Admin',
            'loginDescription' => 'Gunakan kredensial akun Admin SIKINERJA.',
            'loginButton' => 'Masuk ke Dashboard Admin',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'password.required' => 'Password wajib diisi.',
        ]);

        if (! Auth::attempt(array_merge($credentials, ['role' => 'master']), $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => 'Email atau password akun Admin yang Anda masukkan salah.',
            ]);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('master.dashboard'));
    }
}
