<?php

use App\Enums\GarageStatus;
use App\Enums\RentalType;
use App\Enums\UserRole;
use App\Enums\UserStatus;

test('memberi label Indonesia untuk status, peran, dan tipe sewa', function (UserStatus|UserRole|GarageStatus|RentalType $case, string $label) {
    expect($case->label())->toBe($label);
})->with([
    'menunggu' => [UserStatus::Pending, 'Menunggu Verifikasi'],
    'diterima' => [UserStatus::Approved, 'Diterima'],
    'ditolak' => [UserStatus::Rejected, 'Ditolak'],
    'admin' => [UserRole::Admin, 'Admin'],
    'penyewa' => [UserRole::Tenant, 'Penyewa'],
    'tersedia' => [GarageStatus::Available, 'Tersedia'],
    'digunakan' => [GarageStatus::Used, 'Digunakan'],
    'harian' => [RentalType::Daily, 'Harian'],
    'bulanan' => [RentalType::Monthly, 'Bulanan'],
]);
