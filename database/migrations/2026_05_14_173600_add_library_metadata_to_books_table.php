<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->foreignId('rack_id')->nullable()->constrained('racks')->nullOnDelete()->after('category_id');
            $table->string('barcode')->nullable()->unique()->after('id_buku');
            $table->string('isbn')->nullable()->after('barcode');
            $table->string('language', 100)->nullable()->after('kategori');
            $table->string('subject')->nullable()->after('language');
            $table->unsignedInteger('number_of_pages')->nullable()->after('subject');
            $table->string('format', 100)->nullable()->after('number_of_pages');
            $table->decimal('price', 12, 2)->default(0)->after('format');
            $table->decimal('daily_late_fee', 12, 2)->default(0)->after('price');
        });
    }

    public function down(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->dropConstrainedForeignId('rack_id');
            $table->dropColumn(['barcode', 'isbn', 'language', 'subject', 'number_of_pages', 'format', 'price', 'daily_late_fee']);
        });
    }
};
