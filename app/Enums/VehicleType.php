<?php

namespace App\Enums;

// Enum ini buat nyimpen jenis kendaraan: sepeda, sepeda motor, mobil, atau mobil listrik.
enum VehicleType: string
{
    case Bicycle = 'bicycle';
    case Motorcycle = 'motorcycle';
    case Car = 'car';
    case ElectricCar = 'electricCar';

    // Function ini buat ngasih nama jenis kendaraan dalam bahasa Indonesia yang tampil di layar.
    public function label(): string
    {
        return match ($this) {
            self::Bicycle => 'Sepeda',
            self::Motorcycle => 'Sepeda Motor',
            self::Car => 'Mobil',
            self::ElectricCar => 'Mobil Listrik',
        };
    }

    // Function ini buat ngecek kendaraan ini punya baterai atau nggak (cuma mobil listrik yang punya).
    public function hasBattery(): bool
    {
        return $this === self::ElectricCar;
    }

    // Function ini buat ngasih daftar garasi yang boleh dipakai kendaraan ini.
    // Sepeda dan motor di garasi sepeda, mobil di garasi mobil, mobil listrik boleh di garasi mobil atau garasi listrik.
    public function allowedGarageTypes(): array
    {
        return match ($this) {
            self::Bicycle, self::Motorcycle => [GarageType::Bicycle],
            self::Car => [GarageType::Car],
            self::ElectricCar => [GarageType::Car, GarageType::Electric],
        };
    }
}
