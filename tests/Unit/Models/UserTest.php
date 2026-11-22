<?php

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\User;

test('mengenali peran dan status verifikasi pengguna', function (UserRole $role, UserStatus $status, bool $isAdmin, bool $isApproved) {
    $user = (new User)->forceFill(['role' => $role, 'verification_status' => $status]);

    expect($user->isAdmin())->toBe($isAdmin)
        ->and($user->isApproved())->toBe($isApproved);
})->with([
    'admin' => [UserRole::Admin, UserStatus::Approved, true, true],
    'penyewa diterima' => [UserRole::Tenant, UserStatus::Approved, false, true],
    'penyewa menunggu' => [UserRole::Tenant, UserStatus::Pending, false, false],
    'penyewa ditolak' => [UserRole::Tenant, UserStatus::Rejected, false, false],
]);

test('tidak mengizinkan role dan status diisi lewat mass assignment', function () {
    $user = new User(['full_name' => 'X', 'role' => 'admin', 'verification_status' => 'approved']);

    expect($user->role)->toBeNull()->and($user->verification_status)->toBeNull();
});
