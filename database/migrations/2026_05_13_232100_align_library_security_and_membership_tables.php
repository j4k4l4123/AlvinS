<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('anggota', function (Blueprint $table) {
            if (! Schema::hasColumn('anggota', 'user_id')) {
                $table->foreignId('user_id')->nullable()->after('tanggal_daftar')->constrained('users')->nullOnDelete();
            }
        });

        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'deleted_at')) {
                $table->softDeletes();
            }
        });

        Schema::table('membership_requests', function (Blueprint $table) {
            if (! Schema::hasColumn('membership_requests', 'deleted_at')) {
                $table->softDeletes();
            }
        });
    }

    public function down(): void
    {
        Schema::table('membership_requests', function (Blueprint $table) {
            if (Schema::hasColumn('membership_requests', 'deleted_at')) {
                $table->dropSoftDeletes();
            }
        });

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'deleted_at')) {
                $table->dropSoftDeletes();
            }
        });

        Schema::table('anggota', function (Blueprint $table) {
            if (Schema::hasColumn('anggota', 'user_id')) {
                $table->dropConstrainedForeignId('user_id');
            }
        });
    }
};
