<?php

namespace Database\Factories;

use App\Enums\VehicleType;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory untuk membuat data kendaraan per tipe.
 *
 * @extends Factory<Vehicle>
 */
class VehicleFactory extends Factory
{
    /**
     * Mendefinisikan state bawaan model: mobil bensin milik penyewa yang sudah diterima.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tenant_id' => User::factory()->approved(),
            'type' => VehicleType::Car,
            'license_plate' => $this->uniqueLicensePlate(),
            'brand_model' => fake()->randomElement(['Toyota Avanza', 'Honda Brio', 'Suzuki Ertiga', 'Daihatsu Xenia']),
            'battery_kwh' => null,
        ];
    }

    /**
     * Sepeda: tanpa nomor plat dan tanpa baterai.
     */
    public function bicycle(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => VehicleType::Bicycle,
            'license_plate' => null,
            'brand_model' => fake()->randomElement(['Polygon Xtrada', 'United Detroit', 'Brompton M6L']),
            'battery_kwh' => null,
        ]);
    }

    /**
     * Sepeda motor.
     */
    public function motorcycle(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => VehicleType::Motorcycle,
            'license_plate' => $this->uniqueLicensePlate(),
            'brand_model' => fake()->randomElement(['Honda Vario 125', 'Yamaha NMAX', 'Honda Beat']),
            'battery_kwh' => null,
        ]);
    }

    /**
     * Mobil bensin.
     */
    public function car(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => VehicleType::Car,
            'license_plate' => $this->uniqueLicensePlate(),
            'brand_model' => fake()->randomElement(['Toyota Avanza', 'Honda Brio', 'Suzuki Ertiga']),
            'battery_kwh' => null,
        ]);
    }

    /**
     * Mobil listrik dengan kapasitas baterai (kWh).
     */
    public function electricCar(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => VehicleType::ElectricCar,
            'license_plate' => $this->uniqueLicensePlate(),
            'brand_model' => fake()->randomElement(['Hyundai Ioniq 5', 'Wuling Air ev', 'BYD Atto 3']),
            'battery_kwh' => fake()->randomElement([26.7, 50.0, 60.5, 72.6]),
        ]);
    }

    /**
     * Membuat nomor plat acak yang unik, misalnya B 1234 XYZ.
     */
    private function uniqueLicensePlate(): string
    {
        return strtoupper(fake()->unique()->bothify('? #### ???'));
    }
}
