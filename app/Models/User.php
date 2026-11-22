<?php

namespace App\Models;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

// Model ini buat data pengguna (tabel users), bisa admin atau penyewa.
// Kolom role dan verification_status sengaja nggak bisa diisi langsung dari form,
// supaya pengguna nggak bisa bikin dirinya jadi admin atau langsung diterima.
#[Fillable(['nik', 'email', 'full_name', 'phone_number', 'password'])]
#[Hidden(['password'])]
class User extends Authenticatable
{
    use HasFactory, Notifiable;

    // Tabel users nggak punya kolom remember_token, jadi fitur "ingat saya" nggak dipakai.
    protected $rememberTokenName = '';

    // Function ini buat ngubah kolom database ke tipe yang pas (enum, tanggal, angka) waktu datanya dibaca.
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'role' => UserRole::class,
            'verification_status' => UserStatus::class,
        ];
    }

    // Function ini buat ngambil semua kendaraan milik penyewa ini.
    public function vehicles(): HasMany
    {
        return $this->hasMany(Vehicle::class, 'tenant_id');
    }

    // Function ini buat ngambil semua sewa milik penyewa ini (lewat kendaraannya).
    public function rentals(): HasManyThrough
    {
        return $this->hasManyThrough(Rental::class, Vehicle::class, 'tenant_id');
    }

    // Function ini buat ngecek pengguna ini admin atau bukan.
    public function isAdmin(): bool
    {
        return $this->role === UserRole::Admin;
    }

    // Function ini buat ngecek pendaftaran pengguna ini sudah diterima admin atau belum.
    public function isApproved(): bool
    {
        return $this->verification_status === UserStatus::Approved;
    }
}
