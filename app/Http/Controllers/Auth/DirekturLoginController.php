<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class DirekturLoginController extends Controller
{
    public function create(Request $request)
    {
        if ($request->user()) {
            return redirect()->intended(route('direktur.dashboard'));
        }

        return view('auth.atasan.login_atasan', [
            'loginRoute' => 'direktur.login.store',
            'loginTitle' => 'Masuk Direktur',
            'loginDescription' => 'Gunakan kredensial akun Direktur SIKINERJA.',
            'loginButton' => 'Masuk ke Dashboard Direktur',
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

        if (! Auth::attempt(array_merge($credentials, ['role' => 'direktur']), $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => 'Email atau password akun Direktur yang Anda masukkan salah.',
            ]);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('direktur.dashboard'));
    }
}
