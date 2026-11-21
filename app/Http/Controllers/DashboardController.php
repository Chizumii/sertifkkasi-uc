<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Titik masuk setelah login: mengarahkan ke dashboard sesuai role.
 */
class DashboardController extends Controller
{
    /**
     * Mengarahkan admin ke dashboard admin dan penyewa ke dashboard penyewa.
     */
    public function __invoke(Request $request): RedirectResponse
    {
        return redirect()->route($request->user()->isAdmin() ? 'admin.dashboard' : 'tenant.dashboard');
    }
}
