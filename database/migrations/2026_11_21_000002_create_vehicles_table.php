<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Membuat tabel vehicles milik penyewa (tenant_id).
     */
    public function up(): void
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('users')->cascadeOnDelete();
            $table->enum('type', ['bicycle', 'motorcycle', 'car', 'electricCar']);
            $table->string('license_plate', 15)->nullable()->unique();
            $table->string('brand_model');
            $table->decimal('battery_kwh', 8, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Menghapus tabel vehicles.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
