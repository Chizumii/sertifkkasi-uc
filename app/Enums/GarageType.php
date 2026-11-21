<?php

namespace App\Enums;

/**
 * Tipe garasi beserta prefiks kode garasinya.
 */
enum GarageType: string
{
    case Bicycle = 'bicycle';
    case Car = 'car';
    case Electric = 'electric';

    /**
     * Huruf prefiks kode garasi (S, C, atau E).
     */
    public function codePrefix(): string
    {
        return match ($this) {
            self::Bicycle => 'S',
            self::Car => 'C',
            self::Electric => 'E',
        };
    }

    /**
     * Label tipe garasi untuk ditampilkan di antarmuka.
     */
    public function label(): string
    {
        return match ($this) {
            self::Bicycle => 'Garasi Sepeda',
            self::Car => 'Garasi Mobil',
            self::Electric => 'Garasi Mobil Listrik',
        };
    }
}
