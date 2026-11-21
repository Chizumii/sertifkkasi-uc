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
     * @var list<array{nik: string, full_name: string, phone_number: string, email: string, verification_status: UserStatus, vehicles: list<array{type: VehicleType, license_plate: string|null, brand_model: string, battery_kwh: float|null}>}>
     */
    private const array TENANTS = [
        [
            'nik' => '3578011203950002',
            'full_name' => 'Budi Santoso',
            'phone_number' => '081234567801',
            'email' => 'budi@garasi.test',
            'verification_status' => UserStatus::Approved,
            'vehicles' => [
                ['type' => VehicleType::Car, 'license_plate' => 'L 1234 AB', 'brand_model' => 'Toyota Avanza', 'battery_kwh' => null],
                ['type' => VehicleType::ElectricCar, 'license_plate' => 'L 2024 EV', 'brand_model' => 'Hyundai Ioniq 5', 'battery_kwh' => 72.6],
                ['type' => VehicleType::Bicycle, 'license_plate' => null, 'brand_model' => 'Polygon Xtrada 5', 'battery_kwh' => null],
            ],
        ],
        [
            'nik' => '3578014507980003',
            'full_name' => 'Siti Rahmawati',
            'phone_number' => '081234567802',
            'email' => 'siti@garasi.test',
            'verification_status' => UserStatus::Approved,
            'vehicles' => [
                ['type' => VehicleType::Motorcycle, 'license_plate' => 'L 4321 XY', 'brand_model' => 'Honda Vario 125', 'battery_kwh' => null],
                ['type' => VehicleType::ElectricCar, 'license_plate' => 'L 88 WEV', 'brand_model' => 'Wuling Air ev', 'battery_kwh' => 26.7],
            ],
        ],
        [
            'nik' => '3578012009000004',
            'full_name' => 'Andi Wijaya',
            'phone_number' => '081234567803',
            'email' => 'andi@garasi.test',
            'verification_status' => UserStatus::Pending,
            'vehicles' => [],
        ],
        [
            'nik' => '3578016611010005',
            'full_name' => 'Rina Kusuma',
            'phone_number' => '081234567804',
            'email' => 'rina@garasi.test',
            'verification_status' => UserStatus::Pending,
            'vehicles' => [],
        ],
        [
            'nik' => '3578010302970006',
            'full_name' => 'Dedi Pratama',
            'phone_number' => '081234567805',
            'email' => 'dedi@garasi.test',
            'verification_status' => UserStatus::Rejected,
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
                'full_name' => $tenantData['full_name'],
                'phone_number' => $tenantData['phone_number'],
                'password' => 'password',
                'role' => UserRole::Tenant,
                'verification_status' => $tenantData['verification_status'],
            ])->save();

            foreach ($tenantData['vehicles'] as $vehicleData) {
                $tenant->vehicles()->updateOrCreate(
                    ['type' => $vehicleData['type'], 'brand_model' => $vehicleData['brand_model']],
                    [
                        'license_plate' => $vehicleData['license_plate'],
                        'battery_kwh' => $vehicleData['battery_kwh'],
                    ],
                );
            }
        }
    }
}
