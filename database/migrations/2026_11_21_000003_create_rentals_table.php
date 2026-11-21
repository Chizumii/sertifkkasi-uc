<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Membuat tabel rentals. Penyewa diambil lewat kendaraan (vehicles.tenant_id).
     */
    public function up(): void
    {
        Schema::create('rentals', function (Blueprint $table) {
            $table->id();
            $table->char('garage_code', 4);
            $table->foreignId('vehicle_id')->constrained()->restrictOnDelete();
            $table->enum('rental_type', ['daily', 'monthly']);
            $table->unsignedSmallInteger('days_count');
            $table->date('start_date');
            $table->date('end_date');
            $table->unsignedInteger('total_cost');
            $table->string('payment_proof');
            $table->enum('status', ['scheduled', 'active', 'completed'])->default('scheduled');

            $table->foreign('garage_code')->references('id')->on('garages')->restrictOnDelete();
            $table->index(['garage_code', 'start_date', 'end_date']);
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
