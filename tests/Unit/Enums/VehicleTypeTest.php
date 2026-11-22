<?php

use App\Enums\GarageType;
use App\Enums\VehicleType;

test('menentukan garasi yang boleh ditempati tiap tipe kendaraan', function (VehicleType $type, array $garages) {
    expect($type->allowedGarageTypes())->toBe($garages);
})->with([
    'sepeda hanya garasi sepeda' => [VehicleType::Bicycle, [GarageType::Bicycle]],
    'motor hanya garasi sepeda' => [VehicleType::Motorcycle, [GarageType::Bicycle]],
    'mobil hanya garasi mobil' => [VehicleType::Car, [GarageType::Car]],
    'mobil listrik di garasi mobil atau listrik' => [VehicleType::ElectricCar, [GarageType::Car, GarageType::Electric]],
]);

test('hanya mobil listrik yang memiliki baterai', function () {
    expect(collect(VehicleType::cases())->filter->hasBattery()->values()->all())->toBe([VehicleType::ElectricCar]);
});

test('memberi label Indonesia untuk tiap tipe kendaraan', function () {
    expect(collect(VehicleType::cases())->map->label()->all())->toBe(['Sepeda', 'Sepeda Motor', 'Mobil', 'Mobil Listrik']);
});
