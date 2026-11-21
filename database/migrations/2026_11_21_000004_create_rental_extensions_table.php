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
            $table->foreignId('admin_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('rental_id')->constrained()->cascadeOnDelete();
            $table->date('previous_end_date');
            $table->date('new_end_date');
            $table->unsignedInteger('remaining_cost');
            $table->string('payment_proof');
            $table->timestamp('created_at')->useCurrent();
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
