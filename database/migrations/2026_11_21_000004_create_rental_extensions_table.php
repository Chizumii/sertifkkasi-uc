<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Membuat tabel rental_extensions untuk riwayat perpanjangan sewa oleh admin.
     */
    public function up(): void
    {
        Schema::create('rental_extensions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rental_id')->constrained()->cascadeOnDelete();
            $table->date('old_end_date');
            $table->date('new_end_date');
            $table->decimal('additional_cost', 12, 2);
            $table->string('payment_proof');
            $table->timestamps();
        });
    }

    /**
     * Menghapus tabel rental_extensions.
     */
    public function down(): void
    {
        Schema::dropIfExists('rental_extensions');
    }
};
