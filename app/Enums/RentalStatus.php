<?php

namespace App\Enums;

use Carbon\CarbonInterface;

/**
 * Status sewa berdasarkan tanggal: terjadwal, berjalan, atau selesai.
 */
enum RentalStatus: string
{
    case Scheduled = 'scheduled';
    case Active = 'active';
    case Completed = 'completed';

    /**
     * Status yang sesuai untuk periode [start, end) pada tanggal tertentu.
     */
    public static function forPeriod(CarbonInterface $start, CarbonInterface $end, CarbonInterface $today): self
    {
        return match (true) {
            $today->lessThan($start) => self::Scheduled,
            $today->lessThan($end) => self::Active,
            default => self::Completed,
        };
    }

    /**
     * Label status untuk ditampilkan di antarmuka.
     */
    public function label(): string
    {
        return match ($this) {
            self::Scheduled => 'Terjadwal',
            self::Active => 'Berjalan',
            self::Completed => 'Selesai',
        };
    }
}
