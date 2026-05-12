<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('member_profiles', function (Blueprint $table) {
            if (! Schema::hasColumn('member_profiles', 'membership_status')) {
                $table->enum('membership_status', ['active', 'pending_cancellation', 'cancelled'])
                    ->default('active')
                    ->after('tanggal_daftar');
            }
        });

        Schema::table('books', function (Blueprint $table) {
            if (! Schema::hasColumn('books', 'stock')) {
                $table->unsignedInteger('stock')->default(1)->after('keterangan');
            }
        });

        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'remember_token')) {
                $table->rememberToken();
            }
        });
    }

    public function down(): void
    {
        Schema::table('member_profiles', function (Blueprint $table) {
            if (Schema::hasColumn('member_profiles', 'membership_status')) {
                $table->dropColumn('membership_status');
            }
        });

        Schema::table('books', function (Blueprint $table) {
            if (Schema::hasColumn('books', 'stock')) {
                $table->dropColumn('stock');
            }
        });
    }
};
