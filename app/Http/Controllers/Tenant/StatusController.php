<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

// Controller ini buat nampilin status pendaftaran penyewa yang masih menunggu atau ditolak admin.
class StatusController extends Controller
{
    // Function ini buat nampilin halaman status pendaftaran. Penyewa yang sudah diterima langsung dialihkan ke halaman sewa.
    public function __invoke(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        // Penyewa yang sudah diterima tidak perlu halaman ini.
        if ($user->isApproved()) {
            return redirect()->route('tenant.rentals.create');
        }

        return view('tenant.pending', ['user' => $user]);
    }
}
