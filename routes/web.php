<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\GarageController;
use App\Http\Controllers\Admin\RentalController as AdminRentalController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\UserVerificationController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Tenant\DashboardController as TenantDashboardController;
use App\Http\Controllers\Tenant\RentalController as TenantRentalController;
use App\Http\Controllers\Tenant\VehicleController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');

Route::middleware('guest')->group(function () {
    Route::get('login', [LoginController::class, 'create'])->name('login');
    Route::post('login', [LoginController::class, 'store'])->name('login.store');
    Route::get('register', [RegisterController::class, 'create'])->name('register');
    Route::post('register', [RegisterController::class, 'store'])->name('register.store');
});

Route::middleware('auth')->group(function () {
    Route::post('logout', [LoginController::class, 'destroy'])->name('logout');
    Route::get('dashboard', DashboardController::class)->name('dashboard');

    Route::middleware('role:tenant')->prefix('user')->name('tenant.')->group(function () {
        Route::get('dashboard', TenantDashboardController::class)->name('dashboard');

        Route::middleware('approved')->group(function () {
            Route::post('vehicles', [VehicleController::class, 'store'])->name('vehicles.store');
            Route::resource('rentals', TenantRentalController::class)->only(['index', 'create', 'store', 'show']);
        });
    });

    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('dashboard', AdminDashboardController::class)->name('dashboard');

        Route::get('users', [UserVerificationController::class, 'index'])->name('users.index');
        Route::post('users/{user}/approve', [UserVerificationController::class, 'approve'])->name('users.approve');
        Route::post('users/{user}/reject', [UserVerificationController::class, 'reject'])->name('users.reject');

        Route::resource('garages', GarageController::class)->only(['index', 'create', 'store']);

        Route::get('rentals', [AdminRentalController::class, 'index'])->name('rentals.index');
        Route::get('rentals/{rental}', [AdminRentalController::class, 'show'])->name('rentals.show');
        Route::get('rentals/{rental}/extend', [AdminRentalController::class, 'extend'])->name('rentals.extend');
        Route::post('rentals/{rental}/extend', [AdminRentalController::class, 'storeExtension'])->name('rentals.extend.store');

        Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
    });
});
