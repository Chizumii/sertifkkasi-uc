<?php

namespace App\Enums;

use Carbon\CarbonInterface;

// Enum ini buat nyimpen status sewa: terjadwal (belum mulai), berjalan, atau selesai.
enum RentalStatus: string
{
    case Scheduled = 'scheduled';
    case Active = 'active';
    case Completed = 'completed';

    // Function ini buat nentuin status sewa dari tanggalnya.
    // Sebelum tanggal mulai = terjadwal, dari tanggal mulai sampai sehari sebelum tanggal selesai = berjalan, sisanya = selesai.
    public static function forPeriod(CarbonInterface $start, CarbonInterface $end, CarbonInterface $today): self
    {
        return match (true) {
            $today->lessThan($start) => self::Scheduled,
            $today->lessThan($end) => self::Active,
            default => self::Completed,
        };
    }

    // Function ini buat ngasih nama status sewa dalam bahasa Indonesia yang tampil di layar.
    public function label(): string
    {
        return match ($this) {
            self::Scheduled => 'Terjadwal',
            self::Active => 'Berjalan',
            self::Completed => 'Selesai',
        };
    }
}
