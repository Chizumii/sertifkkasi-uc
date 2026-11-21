<?php

namespace App\Http\Controllers\Admin;

use App\Enums\GarageStatus;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Http\Controllers\Controller;
use App\Models\Garage;
use App\Models\Rental;
use App\Models\User;
use Illuminate\View\View;

/**
 * Dashboard admin: jumlah pendaftar baru, sewa aktif hari ini, dan status garasi.
 */
class DashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('admin.dashboard', [
            'pendingUsersCount' => User::query()->where('role', UserRole::Tenant)->where('verification_status', UserStatus::Pending)->count(),
            'activeRentalsCount' => Rental::query()->activeOn(today())->count(),
            'availableGaragesCount' => Garage::query()->where('status', GarageStatus::Available)->count(),
            'usedGaragesCount' => Garage::query()->where('status', GarageStatus::Used)->count(),
        ]);
    }
}
