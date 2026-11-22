<?php

namespace App\Models;

use App\Enums\GarageType;
use App\Enums\VehicleType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

// Model ini buat data kendaraan milik penyewa (tabel vehicles): sepeda, sepeda motor, mobil, atau mobil listrik.
#[Fillable(['tenant_id', 'type', 'license_plate', 'brand_model', 'battery_kwh'])]
class Vehicle extends Model
{
    use HasFactory;

    // Function ini buat ngubah kolom database ke tipe yang pas (enum, tanggal, angka) waktu datanya dibaca.
    protected function casts(): array
    {
        return [
            'type' => VehicleType::class,
            'battery_kwh' => 'decimal:2',
        ];
    }

    // Function ini buat ngambil penyewa pemilik kendaraan ini.
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'tenant_id');
    }

    // Function ini buat ngambil semua sewa yang pakai kendaraan ini.
    public function rentals(): HasMany
    {
        return $this->hasMany(Rental::class);
    }

    // Function ini buat ngecek kendaraan ini sudah diparkir di garasi lain atau belum pada tanggal $start sampai sebelum $end.
    public function isBusy(mixed $start, mixed $end): bool
    {
        return $this->rentals()->overlapping($start, $end)->exists();
    }

    // Function ini buat ngitung biaya isi baterai sampai penuh: (kapasitas - sisa baterai) x harga per kWh.
    // Cuma berlaku untuk mobil listrik yang parkir di garasi mobil listrik, selain itu biayanya 0.
    public function chargingCost(Garage $garage, ?float $remainingKwh): int
    {
        if ($this->type !== VehicleType::ElectricCar || $garage->type !== GarageType::Electric) {
            return 0;
        }

        $kwhToCharge = (float) $this->battery_kwh - (float) $remainingKwh;

        return (int) round($kwhToCharge * $garage->kwh_rate);
    }
}
