<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AtasanLoginController extends Controller
{
    public function create(Request $request)
    {
        if ($request->user()) {
            return redirect()->intended(route('atasan.dashboard'));
        }

        return view('auth.atasan.login_atasan', [
            'loginRoute' => 'atasan.login.store',
            'loginTitle' => 'Masuk Atasan',
            'loginDescription' => 'Gunakan kredensial akun Atasan SIKINERJA.',
            'loginButton' => 'Masuk ke Dashboard Atasan',
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

        if (! Auth::attempt(array_merge($credentials, ['role' => 'atasan']), $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => 'Email atau password akun Atasan yang Anda masukkan salah.',
            ]);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('atasan.dashboard'));
    }
}
