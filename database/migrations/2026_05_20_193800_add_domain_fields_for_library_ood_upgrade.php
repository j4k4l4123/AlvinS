<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('books', function (Blueprint $table) {
            if (! Schema::hasColumn('books', 'copy_code_prefix')) {
                $table->string('copy_code_prefix')->nullable()->after('barcode');
            }

            if (! Schema::hasColumn('books', 'copy_status')) {
                $table->string('copy_status', 50)->default('available')->after('stock');
            }

            if (! Schema::hasColumn('books', 'copy_condition')) {
                $table->string('copy_condition', 50)->default('good')->after('copy_status');
            }

            if (! Schema::hasColumn('books', 'max_loan_days')) {
                $table->unsignedInteger('max_loan_days')->default(14)->after('daily_late_fee');
            }

            if (! Schema::hasColumn('books', 'max_renewals')) {
                $table->unsignedInteger('max_renewals')->default(1)->after('max_loan_days');
            }
        });

        Schema::table('pinjam', function (Blueprint $table) {
            if (! Schema::hasColumn('pinjam', 'copy_code')) {
                $table->string('copy_code')->nullable()->after('book_id');
            }

            if (! Schema::hasColumn('pinjam', 'renewal_count')) {
                $table->unsignedInteger('renewal_count')->default(0)->after('status');
            }

            if (! Schema::hasColumn('pinjam', 'lost_at')) {
                $table->timestamp('lost_at')->nullable()->after('renewal_count');
            }

            if (! Schema::hasColumn('pinjam', 'damaged_at')) {
                $table->timestamp('damaged_at')->nullable()->after('lost_at');
            }
        });

        Schema::table('book_reservations', function (Blueprint $table) {
            if (! Schema::hasColumn('book_reservations', 'queue_position')) {
                $table->unsignedInteger('queue_position')->default(1)->after('book_id');
            }

            if (! Schema::hasColumn('book_reservations', 'approved_at')) {
                $table->timestamp('approved_at')->nullable()->after('status');
            }
        });

        Schema::table('fines', function (Blueprint $table) {
            if (! Schema::hasColumn('fines', 'type')) {
                $table->string('type', 50)->default('late')->after('amount');
            }
        });
    }

    public function down(): void
    {
        Schema::table('fines', function (Blueprint $table) {
            $drops = [];
            if (Schema::hasColumn('fines', 'type')) {
                $drops[] = 'type';
            }
            if ($drops) {
                $table->dropColumn($drops);
            }
        });

        Schema::table('book_reservations', function (Blueprint $table) {
            $drops = [];
            if (Schema::hasColumn('book_reservations', 'queue_position')) {
                $drops[] = 'queue_position';
            }
            if (Schema::hasColumn('book_reservations', 'approved_at')) {
                $drops[] = 'approved_at';
            }
            if ($drops) {
                $table->dropColumn($drops);
            }
        });

        Schema::table('pinjam', function (Blueprint $table) {
            $drops = [];
            foreach (['copy_code', 'renewal_count', 'lost_at', 'damaged_at'] as $column) {
                if (Schema::hasColumn('pinjam', $column)) {
                    $drops[] = $column;
                }
            }
            if ($drops) {
                $table->dropColumn($drops);
            }
        });

        Schema::table('books', function (Blueprint $table) {
            $drops = [];
            foreach (['copy_code_prefix', 'copy_status', 'copy_condition', 'max_loan_days', 'max_renewals'] as $column) {
                if (Schema::hasColumn('books', $column)) {
                    $drops[] = $column;
                }
            }
            if ($drops) {
                $table->dropColumn($drops);
            }
        });
    }
};
