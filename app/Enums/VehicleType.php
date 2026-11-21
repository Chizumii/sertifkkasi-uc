<?php

namespace App\Enums;

/**
 * Tipe kendaraan milik penyewa.
 */
enum VehicleType: string
{
    case Bicycle = 'bicycle';
    case Motorcycle = 'motorcycle';
    case Car = 'car';
    case ElectricCar = 'electricCar';

    /**
     * Label tipe kendaraan untuk ditampilkan di antarmuka.
     */
    public function label(): string
    {
        return match ($this) {
            self::Bicycle => 'Sepeda',
            self::Motorcycle => 'Sepeda Motor',
            self::Car => 'Mobil',
            self::ElectricCar => 'Mobil Listrik',
        };
    }

    /**
     * Menentukan apakah tipe kendaraan ini memiliki kapasitas baterai.
     */
    public function hasBattery(): bool
    {
        return $this === self::ElectricCar;
    }

    /**
     * Daftar tipe garasi yang boleh ditempati oleh tipe kendaraan ini.
     *
     * @return list<GarageType>
     */
    public function allowedGarageTypes(): array
    {
        return match ($this) {
            self::Bicycle, self::Motorcycle => [GarageType::Bicycle],
            self::Car => [GarageType::Car],
            self::ElectricCar => [GarageType::Car, GarageType::Electric],
        };
    }
}
