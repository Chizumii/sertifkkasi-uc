<?php

namespace App\Enums;

// Enum ini buat nyimpen jenis sewa: harian atau bulanan.
enum RentalType: string
{
    case Daily = 'daily';
    case Monthly = 'monthly';

    // Function ini buat ngasih nama jenis sewa dalam bahasa Indonesia yang tampil di layar.
    public function label(): string
    {
        return match ($this) {
            self::Daily => 'Harian',
            self::Monthly => 'Bulanan',
        };
    }
}
