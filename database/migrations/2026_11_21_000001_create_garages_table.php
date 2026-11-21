<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Membuat tabel garages untuk garasi sepeda (S), mobil (C), dan mobil listrik (E).
     */
    public function up(): void
    {
        Schema::create('garages', function (Blueprint $table) {
            $table->id();
            $table->char('code', 4)->unique();
            $table->enum('type', ['bike', 'car', 'electric']);
            $table->decimal('daily_price', 12, 2);
            $table->decimal('kwh_price', 12, 2)->nullable();
            $table->enum('status', ['available', 'used'])->default('available');
            $table->timestamps();

            $table->index(['type', 'status']);
        });
    }

    /**
     * Menghapus tabel garages.
     */
    public function down(): void
    {
        Schema::dropIfExists('garages');
    }
};
