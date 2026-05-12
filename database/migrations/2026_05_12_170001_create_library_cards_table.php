<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('library_cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('anggota_id')->constrained('anggota')->onDelete('cascade');
            $table->string('card_number')->unique();
            $table->enum('status', ['active', 'expired', 'cancelled'])->default('active');
            $table->date('issued_date');
            $table->date('expiry_date');
            $table->timestamps();

            $table->index(['card_number', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('library_cards');
    }
};
