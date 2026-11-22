<?php

namespace App\Enums;

// Enum ini buat nyimpen status garasi: masih kosong (available) atau lagi dipakai (used).
enum GarageStatus: string
{
    case Available = 'available';
    case Used = 'used';

    // Function ini buat ngasih nama status garasi dalam bahasa Indonesia yang tampil di layar.
    public function label(): string
    {
        return match ($this) {
            self::Available => 'Tersedia',
            self::Used => 'Digunakan',
        };
    }
}
