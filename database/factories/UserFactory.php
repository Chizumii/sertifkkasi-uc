<?php

namespace Database\Factories;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * Factory untuk membuat data pengguna (admin maupun penyewa).
 *
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * Password yang sedang dipakai oleh factory.
     */
    protected static ?string $password;

    /**
     * Mendefinisikan state bawaan model: penyewa yang menunggu verifikasi.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nik' => fake()->unique()->numerify('################'),
            'full_name' => fake()->name(),
            'phone_number' => fake()->numerify('08##########'),
            'email' => fake()->unique()->safeEmail(),
            'password' => static::$password ??= Hash::make('password'),
            'role' => UserRole::Tenant,
            'verification_status' => UserStatus::Pending,
        ];
    }

    /**
     * Menjadikan pengguna sebagai admin yang sudah diterima (tanpa NIK).
     */
    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'nik' => null,
            'role' => UserRole::Admin,
            'verification_status' => UserStatus::Approved,
        ]);
    }

    /**
     * Menandai pendaftaran pengguna sudah diterima admin.
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'verification_status' => UserStatus::Approved,
        ]);
    }

    /**
     * Menandai pendaftaran pengguna masih menunggu verifikasi.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'verification_status' => UserStatus::Pending,
        ]);
    }

    /**
     * Menandai pendaftaran pengguna ditolak admin.
     */
    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'verification_status' => UserStatus::Rejected,
        ]);
    }
}
