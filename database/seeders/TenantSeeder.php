<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Enums\VehicleType;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Seeder contoh penyewa dan kendaraannya (tanpa data sewa).
 */
class TenantSeeder extends Seeder
{
    /**
     * Data penyewa demo dengan campuran status; hanya penyewa approved yang punya kendaraan.
     *
     * @var list<array{nik: string, name: string, phone: string, email: string, status: UserStatus, vehicles: list<array{type: VehicleType, plate_number: string|null, brand_model: string, battery_capacity_kwh: float|null}>}>
     */
    private const array TENANTS = [
        [
            'nik' => '3578011203950002',
            'name' => 'Budi Santoso',
            'phone' => '081234567801',
            'email' => 'budi@garasi.test',
            'status' => UserStatus::Approved,
            'vehicles' => [
                ['type' => VehicleType::Car, 'plate_number' => 'L 1234 AB', 'brand_model' => 'Toyota Avanza', 'battery_capacity_kwh' => null],
                ['type' => VehicleType::ElectricCar, 'plate_number' => 'L 2024 EV', 'brand_model' => 'Hyundai Ioniq 5', 'battery_capacity_kwh' => 72.6],
                ['type' => VehicleType::Bicycle, 'plate_number' => null, 'brand_model' => 'Polygon Xtrada 5', 'battery_capacity_kwh' => null],
            ],
        ],
        [
            'nik' => '3578014507980003',
            'name' => 'Siti Rahmawati',
            'phone' => '081234567802',
            'email' => 'siti@garasi.test',
            'status' => UserStatus::Approved,
            'vehicles' => [
                ['type' => VehicleType::Motorcycle, 'plate_number' => 'L 4321 XY', 'brand_model' => 'Honda Vario 125', 'battery_capacity_kwh' => null],
                ['type' => VehicleType::ElectricCar, 'plate_number' => 'L 88 WEV', 'brand_model' => 'Wuling Air ev', 'battery_capacity_kwh' => 26.7],
            ],
        ],
        [
            'nik' => '3578012009000004',
            'name' => 'Andi Wijaya',
            'phone' => '081234567803',
            'email' => 'andi@garasi.test',
            'status' => UserStatus::Pending,
            'vehicles' => [],
        ],
        [
            'nik' => '3578016611010005',
            'name' => 'Rina Kusuma',
            'phone' => '081234567804',
            'email' => 'rina@garasi.test',
            'status' => UserStatus::Pending,
            'vehicles' => [],
        ],
        [
            'nik' => '3578010302970006',
            'name' => 'Dedi Pratama',
            'phone' => '081234567805',
            'email' => 'dedi@garasi.test',
            'status' => UserStatus::Rejected,
            'vehicles' => [],
        ],
    ];

    /**
     * Membuat atau memperbarui penyewa demo beserta kendaraannya.
     *
     * Semua akun memakai password "password".
     */
    public function run(): void
    {
        foreach (self::TENANTS as $tenantData) {
            $tenant = User::query()->firstOrNew(['email' => $tenantData['email']]);
            $tenant->forceFill([
                'nik' => $tenantData['nik'],
                'name' => $tenantData['name'],
                'phone' => $tenantData['phone'],
                'password' => 'password',
                'role' => UserRole::Tenant,
                'status' => $tenantData['status'],
                'email_verified_at' => now(),
            ])->save();

            foreach ($tenantData['vehicles'] as $vehicleData) {
                $tenant->vehicles()->updateOrCreate(
                    ['type' => $vehicleData['type'], 'brand_model' => $vehicleData['brand_model']],
                    [
                        'plate_number' => $vehicleData['plate_number'],
                        'battery_capacity_kwh' => $vehicleData['battery_capacity_kwh'],
                    ],
                );
            }
        }
    }
}
