<?php

namespace App\Enums;

/**
 * Status ketersediaan garasi.
 */
enum GarageStatus: string
{
    case Available = 'available';
    case Used = 'used';

    /**
     * Label status untuk ditampilkan di antarmuka.
     */
    public function label(): string
    {
        return match ($this) {
            self::Available => 'Tersedia',
            self::Used => 'Digunakan',
        };
    }
}
