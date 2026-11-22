<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

// Controller ini buat login dan logout, baik admin maupun penyewa.
class LoginController extends Controller
{
    // Function ini buat nampilin halaman login.
    public function create(): View
    {
        return view('auth.login');
    }

    // Function ini buat ngecek email dan password, lalu masukin pengguna kalau benar.
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

        if (! Auth::attempt($credentials)) {
            return back()->onlyInput('email')->withErrors(['email' => 'Email atau password salah.']);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('home'));
    }

    // Function ini buat logout. Kalau yang ditekan tombol "daftar ulang" (penyewa ditolak), langsung diarahkan ke form pendaftaran.
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
