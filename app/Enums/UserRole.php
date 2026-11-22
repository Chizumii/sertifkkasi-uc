<?php

namespace App\Enums;

// Enum ini buat nyimpen peran pengguna: admin atau penyewa.
enum UserRole: string
{
    case Admin = 'admin';
    case Tenant = 'tenant';

    // Function ini buat ngasih nama peran dalam bahasa Indonesia yang tampil di layar.
    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Admin',
            self::Tenant => 'Penyewa',
        };
    }
}
