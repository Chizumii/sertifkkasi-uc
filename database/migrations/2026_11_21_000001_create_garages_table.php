<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Membuat tabel garages. Primary key id berisi kode garasi: S/C/E + 3 digit (misalnya C001).
     */
    public function up(): void
    {
        Schema::create('garages', function (Blueprint $table) {
            $table->char('id', 4)->primary();
            $table->enum('type', ['bicycle', 'car', 'electric']);
            $table->unsignedInteger('daily_rate');
            $table->unsignedInteger('kwh_rate')->nullable();
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
