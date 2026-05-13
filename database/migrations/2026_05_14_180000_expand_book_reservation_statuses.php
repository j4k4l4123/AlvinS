<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE book_reservations DROP CONSTRAINT IF EXISTS book_reservations_status_check");
        DB::statement("ALTER TABLE book_reservations ADD CONSTRAINT book_reservations_status_check CHECK (status IN ('pending', 'approved', 'rejected', 'completed', 'cancelled', 'expired'))");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE book_reservations DROP CONSTRAINT IF EXISTS book_reservations_status_check");
        DB::statement("ALTER TABLE book_reservations ADD CONSTRAINT book_reservations_status_check CHECK (status IN ('pending', 'completed', 'cancelled', 'expired'))");
    }
};
