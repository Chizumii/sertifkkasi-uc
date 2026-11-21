<?php

namespace App\Enums;

/**
 * Peran pengguna di aplikasi penyewaan garasi.
 */
enum UserRole: string
{
    case Admin = 'admin';
    case Tenant = 'tenant';

    /**
     * Label peran untuk ditampilkan di antarmuka.
     */
    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Admin',
            self::Tenant => 'Penyewa',
        };
    }
}
