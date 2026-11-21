<?php

namespace Database\Factories;

use App\Enums\GarageStatus;
use App\Enums\GarageType;
use App\Models\Garage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory untuk membuat data garasi per tipe.
 *
 * @extends Factory<Garage>
 */
class GarageFactory extends Factory
{
    /**
     * Mendefinisikan state bawaan model: garasi sepeda yang tersedia.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => $this->uniqueCode(GarageType::Bicycle),
            'type' => GarageType::Bicycle,
            'daily_rate' => 10000,
            'kwh_rate' => null,
            'status' => GarageStatus::Available,
        ];
    }

    /**
     * Garasi sepeda (kode S).
     */
    public function bicycle(): static
    {
        return $this->state(fn (array $attributes) => [
            'id' => $this->uniqueCode(GarageType::Bicycle),
            'type' => GarageType::Bicycle,
            'daily_rate' => 10000,
            'kwh_rate' => null,
        ]);
    }

    /**
     * Garasi mobil bensin (kode C).
     */
    public function car(): static
    {
        return $this->state(fn (array $attributes) => [
            'id' => $this->uniqueCode(GarageType::Car),
            'type' => GarageType::Car,
            'daily_rate' => 50000,
            'kwh_rate' => null,
        ]);
    }

    /**
     * Garasi mobil listrik (kode E) dengan harga isi listrik per kWh.
     */
    public function electric(): static
    {
        return $this->state(fn (array $attributes) => [
            'id' => $this->uniqueCode(GarageType::Electric),
            'type' => GarageType::Electric,
            'daily_rate' => 75000,
            'kwh_rate' => 2500,
        ]);
    }

    /**
     * Garasi yang sedang digunakan.
     */
    public function used(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => GarageStatus::Used,
        ]);
    }

    /**
     * Membuat kode garasi acak yang unik, misalnya S123.
     */
    private function uniqueCode(GarageType $type): string
    {
        return $type->codePrefix().fake()->unique()->numerify('###');
    }
}
