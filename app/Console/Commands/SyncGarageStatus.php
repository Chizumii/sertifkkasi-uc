<?php

namespace App\Console\Commands;

use App\Enums\GarageStatus;
use App\Enums\RentalStatus;
use App\Models\Garage;
use App\Models\Rental;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

// Command ini buat nyamain status garasi dan status sewa dengan tanggal hari ini. Jalan otomatis tiap hari jam 00:00 (diatur di routes/console.php).
#[Signature('garages:sync-status')]
#[Description('Menandai garasi used bila ada sewa aktif hari ini, selain itu available')]
class SyncGarageStatus extends Command
{
    // Function ini buat ngejalanin sinkronisasi: garasi yang disewa hari ini jadi "used", sisanya "available".
    public function handle(): int
    {
        // Kode garasi yang sedang dipakai hari ini.
        $usedGarageCodes = Rental::query()->activeOn(today())->pluck('garage_code')->unique();

        // Garasi yang dipakai hari ini -> used.
        $becameUsed = Garage::query()
            ->whereIn('id', $usedGarageCodes)
            ->where('status', GarageStatus::Available)
            ->update(['status' => GarageStatus::Used]);

        // Garasi yang tidak dipakai hari ini -> available.
        $becameAvailable = Garage::query()
            ->whereNotIn('id', $usedGarageCodes)
            ->where('status', GarageStatus::Used)
            ->update(['status' => GarageStatus::Available]);

        // Status sewa: berjalan bila aktif hari ini, selesai bila tanggal selesai sudah lewat.
        Rental::query()->activeOn(today())->where('status', '!=', RentalStatus::Active)->update(['status' => RentalStatus::Active]);
        Rental::query()->where('end_date', '<=', today())->where('status', '!=', RentalStatus::Completed)->update(['status' => RentalStatus::Completed]);

        $changed = $becameUsed + $becameAvailable;
        $this->info("Status garasi disinkronkan: {$changed} garasi berubah.");

        return self::SUCCESS;
    }
}
