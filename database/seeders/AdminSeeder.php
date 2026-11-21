<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Seeder akun admin bawaan.
 */
class AdminSeeder extends Seeder
{
    /**
     * Membuat atau memperbarui akun admin berstatus approved.
     *
     * Kredensial: admin@garasi.test / password.
     */
    public function run(): void
    {
        User::query()
            ->firstOrNew(['email' => 'admin@garasi.test'])
            ->forceFill([
                'nik' => null,
                'full_name' => 'Administrator',
                'phone_number' => '081200000001',
                'password' => 'password',
                'role' => UserRole::Admin,
                'verification_status' => UserStatus::Approved,
            ])
            ->save();
    }
}
