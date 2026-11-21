<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Membuat tabel users untuk admin dan penyewa. NIK kosong untuk admin.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->char('nik', 16)->nullable()->unique();
            $table->string('email')->unique();
            $table->string('full_name');
            $table->string('phone_number', 20);
            $table->string('password');
            $table->enum('role', ['admin', 'tenant'])->default('tenant');
            $table->enum('verification_status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Menghapus tabel users.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
