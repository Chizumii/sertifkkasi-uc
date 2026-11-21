<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Pendaftaran penyewa baru. Akun baru berstatus pending sampai diverifikasi admin.
 */
class RegisterController extends Controller
{
    /**
     * Form pendaftaran.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Simpan akun penyewa baru, login otomatis, lalu tampilkan halaman status.
     *
     * Penyewa yang pernah ditolak boleh daftar ulang dengan NIK/email yang sama:
     * akun lama yang ditolak (dan belum pernah menyewa) dihapus dulu.
     * Semua dijalankan dalam transaksi, jadi bila validasi gagal, akun lama tidak jadi terhapus.
     */
    public function store(Request $request): RedirectResponse
    {
        // Rapikan input: buang spasi di NIK dan nomor telepon, email huruf kecil.
        $request->merge([
            'nik' => preg_replace('/\s+/', '', (string) $request->input('nik')),
            'phone_number' => preg_replace('/[\s-]+/', '', (string) $request->input('phone_number')),
            'email' => strtolower(trim((string) $request->input('email'))),
        ]);

        $user = DB::transaction(function () use ($request): User {
            User::query()
                ->where('role', UserRole::Tenant)
                ->where('verification_status', UserStatus::Rejected)
                ->where(fn ($query) => $query->where('nik', $request->input('nik'))->orWhere('email', $request->input('email')))
                ->whereDoesntHave('rentals')
                ->delete();

            $validated = $request->validate([
                'nik' => ['required', 'digits:16', 'unique:users,nik'],
                'full_name' => ['required', 'string', 'max:255'],
                'phone_number' => ['required', 'regex:/^[0-9]{9,15}$/'],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
                'password' => ['required', 'string', 'min:8', 'confirmed'],
            ], [
                'required' => ':Attribute wajib diisi.',
                'nik.digits' => 'NIK harus 16 digit angka.',
                'nik.unique' => 'NIK ini sudah terdaftar.',
                'phone_number.regex' => 'Nomor telepon harus 9 sampai 15 digit angka.',
                'email.email' => 'Format email tidak valid.',
                'email.unique' => 'Email ini sudah terdaftar.',
                'max' => ':Attribute maksimal :max karakter.',
                'password.confirmed' => 'Ulangi password tidak sama.',
                'password.min' => 'Password minimal 8 karakter.',
            ], [
                'nik' => 'NIK',
                'full_name' => 'nama lengkap',
                'phone_number' => 'nomor telepon',
                'email' => 'email',
                'password' => 'password',
            ]);

            $user = new User($validated);
            $user->role = UserRole::Tenant;
            $user->verification_status = UserStatus::Pending;
            $user->save();

            return $user;
        });

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()
            ->route('tenant.dashboard')
            ->with('success', 'Pendaftaran berhasil. Admin akan memverifikasi data Anda.');
    }
}
