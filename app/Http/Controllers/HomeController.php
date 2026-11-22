<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

// Controller ini buat halaman awal setelah login. Nggak punya tampilan sendiri, cuma ngarahin:
// - admin ke Verifikasi pendaftar,
// - penyewa yang sudah diterima ke Sewa Garasi,
// - penyewa yang menunggu/ditolak ke Status Pendaftaran.
class HomeController extends Controller
{
    // Function ini buat ngarahin pengguna ke halaman yang sesuai dengan peran dan statusnya.
    public function __invoke(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->isAdmin()) {
            return redirect()->route('admin.users.index');
        }

        if ($user->isApproved()) {
            return redirect()->route('tenant.rentals.create');
        }

        return redirect()->route('tenant.status');
    }
}
