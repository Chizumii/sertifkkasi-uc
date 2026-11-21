<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Rental;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Dashboard penyewa. Penyewa yang belum diterima hanya melihat halaman status pendaftaran.
 */
class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();

        if (! $user->isApproved()) {
            return view('tenant.pending', ['user' => $user]);
        }

        // Sewa milik penyewa yang sedang berjalan hari ini.
        $activeRentals = Rental::query()
            ->ownedBy($user)
            ->activeOn(today())
            ->with(['garage', 'vehicle'])
            ->orderBy('end_date')
            ->get();

        return view('tenant.dashboard', [
            'user' => $user,
            'activeRentals' => $activeRentals,
            'vehicleCount' => $user->vehicles()->count(),
        ]);
    }
}
