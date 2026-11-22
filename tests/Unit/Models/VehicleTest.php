<?php

use App\Enums\GarageType;
use App\Enums\VehicleType;
use App\Models\Garage;
use App\Models\Vehicle;

test('menghitung biaya isi baterai sampai penuh hanya untuk mobil listrik di garasi listrik', function (VehicleType $vehicleType, GarageType $garageType, ?float $remainingKwh, int $cost) {
    $vehicle = new Vehicle([
        'type' => $vehicleType,
        'battery_kwh' => $vehicleType === VehicleType::ElectricCar ? 60 : null,
    ]);
    $garage = new Garage([
        'type' => $garageType,
        'kwh_rate' => $garageType === GarageType::Electric ? 2500 : null,
    ]);

    expect($vehicle->chargingCost($garage, $remainingKwh))->toBe($cost);
})->with([
    'mobil listrik di garasi listrik, sisa 20 dari 60 kWh' => [VehicleType::ElectricCar, GarageType::Electric, 20.0, 100000],
    'baterai kosong' => [VehicleType::ElectricCar, GarageType::Electric, 0.0, 150000],
    'baterai sudah penuh' => [VehicleType::ElectricCar, GarageType::Electric, 60.0, 0],
    'mobil listrik di garasi mobil' => [VehicleType::ElectricCar, GarageType::Car, 20.0, 0],
    'mobil bensin' => [VehicleType::Car, GarageType::Car, null, 0],
]);
