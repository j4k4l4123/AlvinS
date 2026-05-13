<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Book extends Model
{
    protected $casts = [
        'reference_only' => 'boolean',
    ];

    protected $fillable = [
        'id_buku',
        'author_id',
        'judul',
        'barcode',
        'isbn',
        'pengarang',
        'penerbit',
        'thn_terbit',
        'category_id',
        'rack_id',
        'kategori',
        'language',
        'subject',
        'number_of_pages',
        'format',
        'price',
        'daily_late_fee',
        'keterangan',
        'stock',
        'reference_only',
    ];

    public function author(): BelongsTo
    {
        return $this->belongsTo(Author::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function rack(): BelongsTo
    {
        return $this->belongsTo(Rack::class);
    }

    public function pinjam(): HasMany
    {
        return $this->hasMany(Pinjam::class);
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(BookReservation::class);
    }

    public function pengembalian(): HasMany
    {
        return $this->hasMany(Pengembalian::class);
    }

    public function activeBorrowingsCount(): int
    {
        return $this->pinjam()->where('status', 'dipinjam')->count();
    }

    public function availableStock(): int
    {
        return max(0, (int) $this->stock - $this->activeBorrowingsCount());
    }

    public function isAvailable(): bool
    {
        return $this->availableStock() > 0;
    }

    public function canBeBorrowed(): bool
    {
        return ! $this->reference_only && $this->isAvailable();
    }

    public function activeReservation(): ?BookReservation
    {
        return $this->reservations()
            ->where('status', 'pending')
            ->where('expires_at', '>', now())
            ->latest()
            ->first();
    }

    public function scopeSearch($query, string $keyword)
    {
        return $query->where(function ($q) use ($keyword) {
            $q->whereRaw('LOWER(judul) LIKE ?', ['%' . strtolower($keyword) . '%'])
                ->orWhereRaw('LOWER(pengarang) LIKE ?', ['%' . strtolower($keyword) . '%'])
                ->orWhereRaw('LOWER(kategori) LIKE ?', ['%' . strtolower($keyword) . '%'])
                ->orWhereRaw('CAST(thn_terbit AS CHAR) LIKE ?', ['%' . strtolower($keyword) . '%']);
        });
    }

    public function scopeFilterByCategory($query, ?string $category)
    {
        if ($category) {
            return $query->where(function ($q) use ($category) {
                $q->where('kategori', $category)
                    ->orWhereHas('category', function ($categoryQuery) use ($category) {
                        $categoryQuery->where('name', $category);
                    });
            });
        }

        return $query;
    }
}
