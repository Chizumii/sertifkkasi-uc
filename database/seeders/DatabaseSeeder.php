<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

/**
 * Seeder utama yang memanggil seeder per tanggung jawab.
 */
class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Mengisi database aplikasi dengan data demo.
     */
    public function run(): void
    {
        $this->call([
            AdminSeeder::class,
            GarageSeeder::class,
            TenantSeeder::class,
        ]);
    }
}
