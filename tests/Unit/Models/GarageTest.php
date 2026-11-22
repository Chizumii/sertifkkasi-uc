<?php

use App\Enums\GarageType;
use App\Enums\RentalType;
use App\Models\Garage;

test('menghitung harga bulanan sebagai 25 kali harga harian', function () {
    $garage = new Garage(['type' => GarageType::Car, 'daily_rate' => 50000]);

    expect($garage->monthlyPrice())->toBe(1250000);
});

test('menghitung biaya sewa harian dan bulanan', function (int $days, RentalType $type, int $cost) {
    $garage = new Garage(['type' => GarageType::Car, 'daily_rate' => 50000]);

    expect($garage->rentalCost($days, $type))->toBe($cost);
})->with([
    'harian 1 hari' => [1, RentalType::Daily, 50000],
    'harian 3 hari' => [3, RentalType::Daily, 150000],
    'bulanan 31 hari' => [31, RentalType::Monthly, 1250000],
    'bulanan 28 hari (Februari)' => [28, RentalType::Monthly, 1250000],
]);

test('menyusun kode garasi dari tipe dan nomor', function (GarageType $type, int $number, string $code) {
    expect(Garage::makeCode($type, $number))->toBe($code);
})->with([
    'sepeda nomor 1' => [GarageType::Bicycle, 1, 'S001'],
    'mobil nomor 10' => [GarageType::Car, 10, 'C010'],
    'listrik nomor 999' => [GarageType::Electric, 999, 'E999'],
]);
