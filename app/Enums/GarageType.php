<?php

namespace App\Enums;

// Enum ini buat nyimpen jenis garasi: sepeda, mobil, atau mobil listrik.
enum GarageType: string
{
    case Bicycle = 'bicycle';
    case Car = 'car';
    case Electric = 'electric';

    // Function ini buat ngasih huruf depan kode garasi: S untuk sepeda, C untuk mobil, E untuk mobil listrik.
    public function codePrefix(): string
    {
        return match ($this) {
            self::Bicycle => 'S',
            self::Car => 'C',
            self::Electric => 'E',
        };
    }

    // Function ini buat ngasih nama jenis garasi dalam bahasa Indonesia yang tampil di layar.
    public function label(): string
    {
        return match ($this) {
            self::Bicycle => 'Garasi Sepeda',
            self::Car => 'Garasi Mobil',
            self::Electric => 'Garasi Mobil Listrik',
        };
    }
}
