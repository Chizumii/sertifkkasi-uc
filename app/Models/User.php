<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * Pengguna aplikasi, berperan sebagai admin atau penyewa.
 *
 * Kolom role dan status sengaja tidak fillable agar tidak bisa diubah
 * lewat mass assignment dari input pengguna.
 */
#[Fillable(['nik', 'name', 'phone', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Mendapatkan atribut yang perlu di-cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
            'status' => UserStatus::class,
        ];
    }

    /**
     * Kendaraan yang dimiliki penyewa.
     *
     * @return HasMany<Vehicle, $this>
     */
    public function vehicles(): HasMany
    {
        return $this->hasMany(Vehicle::class);
    }

    /**
     * Riwayat sewa garasi milik penyewa.
     *
     * @return HasMany<Rental, $this>
     */
    public function rentals(): HasMany
    {
        return $this->hasMany(Rental::class);
    }

    /**
     * Menentukan apakah pengguna adalah admin.
     */
    public function isAdmin(): bool
    {
        return $this->role === UserRole::Admin;
    }

    /**
     * Menentukan apakah pengguna adalah penyewa.
     */
    public function isTenant(): bool
    {
        return $this->role === UserRole::Tenant;
    }

    /**
     * Menentukan apakah pendaftaran pengguna sudah diterima admin.
     */
    public function isApproved(): bool
    {
        return $this->status === UserStatus::Approved;
    }
}
