<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Membuat tabel rentals beserta snapshot data kendaraan dan harga saat sewa dibuat.
     *
     * Kolom end_date bersifat eksklusif: sewa 3 hari mulai tanggal 1 berakhir tanggal 4.
     */
    public function up(): void
    {
        Schema::create('rentals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->foreignId('garage_id')->constrained()->restrictOnDelete();
            $table->foreignId('vehicle_id')->nullable()->constrained()->nullOnDelete();

            $table->enum('vehicle_type', ['bicycle', 'motorcycle', 'car', 'electric_car']);
            $table->string('plate_number', 15)->nullable();
            $table->string('brand_model');
            $table->decimal('battery_capacity_kwh', 8, 2)->nullable();
            $table->decimal('current_battery_kwh', 8, 2)->nullable();

            $table->enum('rental_type', ['daily', 'monthly']);
            $table->unsignedSmallInteger('days')->nullable();
            $table->date('start_date');
            $table->date('end_date');

            $table->decimal('daily_price', 12, 2);
            $table->decimal('kwh_price', 12, 2)->nullable();
            $table->decimal('rental_cost', 12, 2);
            $table->decimal('charging_kwh', 8, 2)->default(0);
            $table->decimal('charging_cost', 12, 2)->default(0);
            $table->decimal('total_cost', 12, 2);
            $table->string('payment_proof');
            $table->timestamps();

            $table->index(['garage_id', 'start_date', 'end_date']);
            $table->index('start_date');
        });
    }

    /**
     * Menghapus tabel rentals.
     */
    public function down(): void
    {
        Schema::dropIfExists('rentals');
    }
};
