<?php

namespace App\Enums;

// Enum ini buat nyimpen status pendaftaran penyewa: menunggu, diterima, atau ditolak admin.
enum UserStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';

    // Function ini buat ngasih nama status pendaftaran dalam bahasa Indonesia yang tampil di layar.
    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Menunggu Verifikasi',
            self::Approved => 'Diterima',
            self::Rejected => 'Ditolak',
        };
    }
}
