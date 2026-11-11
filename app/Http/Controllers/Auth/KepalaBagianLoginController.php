<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class KepalaBagianLoginController extends Controller
{
    public function create(Request $request)
    {
        if ($request->user()) {
            return redirect()->intended(route('kepala_bagian.dashboard'));
        }

        return view('auth.atasan.login_atasan', [
            'loginRoute' => 'kepala_bagian.login.store',
            'loginTitle' => 'Masuk Kepala Bagian',
            'loginDescription' => 'Gunakan kredensial akun Kepala Bagian SIKINERJA.',
            'loginButton' => 'Masuk ke Dashboard Kepala Bagian',
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

        if (! Auth::attempt(array_merge($credentials, ['role' => 'kepala_bagian']), $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => 'Email atau password akun Kepala Bagian yang Anda masukkan salah.',
            ]);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('kepala_bagian.dashboard'));
    }
}
