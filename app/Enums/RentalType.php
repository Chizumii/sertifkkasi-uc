<?php

namespace App\Enums;

/**
 * Jenis sewa garasi: harian atau bulanan.
 */
enum RentalType: string
{
    case Daily = 'daily';
    case Monthly = 'monthly';

    /**
     * Label jenis sewa untuk ditampilkan di antarmuka.
     */
    public function label(): string
    {
        return match ($this) {
            self::Daily => 'Harian',
            self::Monthly => 'Bulanan',
        };
    }
}
