<?php

namespace Database\Factories;

use App\Enums\RentalStatus;
use App\Enums\RentalType;
use App\Models\Garage;
use App\Models\Rental;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory untuk sewa: sewa harian 3 hari mobil bensin di garasi mobil.
 *
 * @extends Factory<Rental>
 */
class RentalFactory extends Factory
{
    /**
     * Mendefinisikan state bawaan model.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startDate = now()->startOfDay();

        return [
            'vehicle_id' => Vehicle::factory()->car(),
            'garage_code' => Garage::factory()->car(),
            'rental_type' => RentalType::Daily,
            'days_count' => 3,
            'start_date' => $startDate->toDateString(),
            'end_date' => $startDate->copy()->addDays(3)->toDateString(),
            'total_cost' => 150000,
            'payment_proof' => 'payment-proofs/example.jpg',
            'status' => RentalStatus::Active,
        ];
    }
}
