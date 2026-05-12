<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            // Guard against re-running when `name` already exists (e.g. due to corrupted migration history)
            if (!Schema::hasColumn('roles', 'name')) {
                $table->string('name')->unique()->after('id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->dropColumn('name');
        });
    }
};

