<?php

namespace Database\Factories;

use App\Models\Rental;
use App\Models\RentalExtension;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory untuk perpanjangan sewa: tambah 2 hari dari tanggal selesai lama dengan biaya 100.000.
 *
 * @extends Factory<RentalExtension>
 */
class RentalExtensionFactory extends Factory
{
    /**
     * Mendefinisikan state bawaan model.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $oldEndDate = now()->startOfDay()->addDays(3);

        return [
            'rental_id' => Rental::factory(),
            'admin_id' => User::factory()->admin(),
            'previous_end_date' => $oldEndDate->toDateString(),
            'new_end_date' => $oldEndDate->copy()->addDays(2)->toDateString(),
            'remaining_cost' => 100000,
            'payment_proof' => 'payment-proofs/example-extension.jpg',
        ];
    }
}
