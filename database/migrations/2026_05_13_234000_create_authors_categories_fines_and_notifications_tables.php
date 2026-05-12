<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('authors', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->timestamps();
        });

        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('fines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pinjam_id')->constrained('pinjam')->cascadeOnDelete();
            $table->foreignId('pengembalian_id')->nullable()->constrained('pengembalian')->nullOnDelete();
            $table->foreignId('anggota_id')->constrained('anggota')->cascadeOnDelete();
            $table->unsignedInteger('amount');
            $table->enum('status', ['unpaid', 'paid'])->default('unpaid');
            $table->timestamp('paid_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('type');
            $table->string('title');
            $table->text('message');
            $table->json('data')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });

        Schema::table('books', function (Blueprint $table) {
            if (! Schema::hasColumn('books', 'author_id')) {
                $table->foreignId('author_id')->nullable()->after('id_buku')->constrained('authors')->nullOnDelete();
            }

            if (! Schema::hasColumn('books', 'category_id')) {
                $table->foreignId('category_id')->nullable()->after('thn_terbit')->constrained('categories')->nullOnDelete();
            }
        });

        $authors = DB::table('books')
            ->select('pengarang')
            ->whereNotNull('pengarang')
            ->distinct()
            ->pluck('pengarang');

        foreach ($authors as $authorName) {
            DB::table('authors')->updateOrInsert(
                ['name' => $authorName],
                ['slug' => Str::slug($authorName) ?: Str::random(8), 'created_at' => now(), 'updated_at' => now()]
            );
        }

        $categories = DB::table('books')
            ->select('kategori')
            ->whereNotNull('kategori')
            ->distinct()
            ->pluck('kategori');

        foreach ($categories as $categoryName) {
            DB::table('categories')->updateOrInsert(
                ['name' => $categoryName],
                ['slug' => Str::slug($categoryName) ?: Str::random(8), 'created_at' => now(), 'updated_at' => now()]
            );
        }

        $books = DB::table('books')->get();

        foreach ($books as $book) {
            $authorId = DB::table('authors')->where('name', $book->pengarang)->value('id');
            $categoryId = DB::table('categories')->where('name', $book->kategori)->value('id');

            DB::table('books')->where('id', $book->id)->update([
                'author_id' => $authorId,
                'category_id' => $categoryId,
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('books', function (Blueprint $table) {
            if (Schema::hasColumn('books', 'author_id')) {
                $table->dropConstrainedForeignId('author_id');
            }

            if (Schema::hasColumn('books', 'category_id')) {
                $table->dropConstrainedForeignId('category_id');
            }
        });

        Schema::dropIfExists('notifications');
        Schema::dropIfExists('fines');
        Schema::dropIfExists('categories');
        Schema::dropIfExists('authors');
    }
};
