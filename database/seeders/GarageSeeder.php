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
     * @var list<array{type: GarageType, count: int, daily_rate: int, kwh_rate: int|null}>
     */
    private const array GARAGE_GROUPS = [
        ['type' => GarageType::Bicycle, 'count' => 10, 'daily_rate' => 10000, 'kwh_rate' => null],
        ['type' => GarageType::Car, 'count' => 10, 'daily_rate' => 50000, 'kwh_rate' => null],
        ['type' => GarageType::Electric, 'count' => 5, 'daily_rate' => 75000, 'kwh_rate' => 2500],
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
                    ['id' => $group['type']->codePrefix().str_pad((string) $sequence, 3, '0', STR_PAD_LEFT)],
                    [
                        'type' => $group['type'],
                        'daily_rate' => $group['daily_rate'],
                        'kwh_rate' => $group['kwh_rate'],
                        'status' => GarageStatus::Available,
                    ],
                );
            }
        }
    }
}
