<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('member_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('id_anggota')->unique()->nullable();
            $table->string('nama')->nullable();
            $table->text('alamat')->nullable();
            $table->string('no_tlp', 20)->nullable();
            $table->date('tanggal_daftar')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('member_profiles');
    }
};
