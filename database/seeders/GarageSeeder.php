<?php

namespace Database\Seeders;

use App\Enums\GarageStatus;
use App\Enums\GarageType;
use App\Models\Garage;
use Illuminate\Database\Seeder;

/**
 * Seeder contoh garasi untuk demo.
 */
class GarageSeeder extends Seeder
{
    /**
     * Daftar kelompok garasi demo per tipe.
     *
     * @var list<array{type: GarageType, count: int, daily_price: int, kwh_price: int|null}>
     */
    private const array GARAGE_GROUPS = [
        ['type' => GarageType::Bike, 'count' => 10, 'daily_price' => 10000, 'kwh_price' => null],
        ['type' => GarageType::Car, 'count' => 10, 'daily_price' => 50000, 'kwh_price' => null],
        ['type' => GarageType::Electric, 'count' => 5, 'daily_price' => 75000, 'kwh_price' => 2500],
    ];

    /**
     * Membuat garasi S001-S010, C001-C010, dan E001-E005.
     *
     * Garasi yang kodenya sudah ada tidak diubah, sehingga aman dijalankan ulang.
     */
    public function run(): void
    {
        foreach (self::GARAGE_GROUPS as $group) {
            for ($sequence = 1; $sequence <= $group['count']; $sequence++) {
                Garage::query()->firstOrCreate(
                    ['code' => $group['type']->codePrefix().str_pad((string) $sequence, 3, '0', STR_PAD_LEFT)],
                    [
                        'type' => $group['type'],
                        'daily_price' => $group['daily_price'],
                        'kwh_price' => $group['kwh_price'],
                        'status' => GarageStatus::Available,
                    ],
                );
            }
        }
    }
}
