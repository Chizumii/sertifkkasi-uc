<?php

namespace App\Models;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * Pengguna aplikasi, berperan sebagai admin atau penyewa.
 *
 * Kolom role dan verification_status sengaja tidak fillable agar tidak bisa diubah
 * lewat mass assignment dari input pengguna.
 */
#[Fillable(['nik', 'email', 'full_name', 'phone_number', 'password'])]
#[Hidden(['password'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Tabel users tidak punya kolom remember_token, jadi fitur "ingat saya" tidak dipakai.
     *
     * @var string
     */
    protected $rememberTokenName = '';

    /**
     * Mendapatkan atribut yang perlu di-cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'role' => UserRole::class,
            'verification_status' => UserStatus::class,
        ];
    }

    /**
     * Kendaraan yang dimiliki penyewa.
     *
     * @return HasMany<Vehicle, $this>
     */
    public function vehicles(): HasMany
    {
        return $this->hasMany(Vehicle::class, 'tenant_id');
    }

    /**
     * Riwayat sewa garasi milik penyewa, lewat kendaraannya.
     *
     * @return HasManyThrough<Rental, Vehicle, $this>
     */
    public function rentals(): HasManyThrough
    {
        return $this->hasManyThrough(Rental::class, Vehicle::class, 'tenant_id');
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
        return $this->verification_status === UserStatus::Approved;
    }
}
