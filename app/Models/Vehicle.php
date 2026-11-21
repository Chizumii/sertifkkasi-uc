<?php

namespace App\Models;

use App\Enums\GarageType;
use App\Enums\VehicleType;
use Database\Factories\VehicleFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Kendaraan milik penyewa (sepeda, sepeda motor, mobil, atau mobil listrik).
 */
#[Fillable(['tenant_id', 'type', 'license_plate', 'brand_model', 'battery_kwh'])]
class Vehicle extends Model
{
    /** @use HasFactory<VehicleFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => VehicleType::class,
            'battery_kwh' => 'decimal:2',
        ];
    }

    /**
     * Penyewa pemilik kendaraan.
     *
     * @return BelongsTo<User, $this>
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'tenant_id');
    }

    /**
     * Semua sewa yang memakai kendaraan ini.
     *
     * @return HasMany<Rental, $this>
     */
    public function rentals(): HasMany
    {
        return $this->hasMany(Rental::class);
    }

    /**
     * Cek apakah kendaraan sudah diparkir di garasi lain pada tanggal $start sampai sebelum $end.
     */
    public function isBusy(mixed $start, mixed $end): bool
    {
        return $this->rentals()->overlapping($start, $end)->exists();
    }

    /**
     * Biaya isi baterai sampai penuh: (kapasitas - sisa baterai) x harga per kWh.
     * Hanya untuk mobil listrik yang parkir di garasi mobil listrik; selain itu 0.
     */
    public function chargingCost(Garage $garage, ?float $remainingKwh): int
    {
        if ($this->type !== VehicleType::ElectricCar || $garage->type !== GarageType::Electric) {
            return 0;
        }

        $kwhToCharge = (float) $this->battery_kwh - (float) $remainingKwh;

        return (int) round($kwhToCharge * $garage->kwh_rate);
    }
}
