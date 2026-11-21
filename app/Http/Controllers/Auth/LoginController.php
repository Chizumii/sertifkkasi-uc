<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * Login dan logout untuk admin maupun penyewa.
 */
class LoginController extends Controller
{
    /**
     * Form login.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Proses login. Setelah 5 kali gagal dalam 1 menit, login dikunci sementara.
     */
    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'password.required' => 'Password wajib diisi.',
        ]);

        // Kunci pembatas percobaan: email + alamat IP.
        $throttleKey = Str::lower($credentials['email']).'|'.$request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            return back()->onlyInput('email')->withErrors(['email' => "Terlalu banyak percobaan login. Coba lagi dalam {$seconds} detik."]);
        }

        if (! Auth::attempt($credentials)) {
            RateLimiter::hit($throttleKey);

            return back()->onlyInput('email')->withErrors(['email' => 'Email atau password salah.']);
        }

        RateLimiter::clear($throttleKey);
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    /**
     * Logout. Bila next=register (tombol daftar ulang untuk penyewa yang ditolak), langsung ke form pendaftaran.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($request->input('next') === 'register') {
            return redirect()->route('register')->with('info', 'Silakan daftar ulang dengan data KTP yang benar.');
        }

        return redirect()->route('login')->with('success', 'Anda sudah keluar.');
    }
}
