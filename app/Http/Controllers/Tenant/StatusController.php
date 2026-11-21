<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Status pendaftaran penyewa yang masih menunggu verifikasi atau ditolak admin.
 */
class StatusController extends Controller
{
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
