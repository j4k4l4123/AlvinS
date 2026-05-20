<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Book extends Model
{
    protected $casts = [
        'reference_only' => 'boolean',
        'price' => 'decimal:2',
        'daily_late_fee' => 'decimal:2',
        'max_loan_days' => 'integer',
        'max_renewals' => 'integer',
    ];

    protected $fillable = [
        'id_buku',
        'author_id',
        'judul',
        'barcode',
        'copy_code_prefix',
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
        'copy_status',
        'copy_condition',
        'reference_only',
        'max_loan_days',
        'max_renewals',
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
        return $this->availableStock() > 0 && ! in_array($this->copy_status, ['lost', 'damaged', 'maintenance'], true);
    }

    public function isReservable(): bool
    {
        return ! $this->reference_only && ! $this->isAvailable() && ! in_array($this->copy_status, ['lost', 'damaged', 'maintenance'], true);
    }

    public function canBeBorrowed(): bool
    {
        return ! $this->reference_only && $this->availableStock() > 0 && ! in_array($this->copy_status, ['lost', 'damaged', 'maintenance'], true);
    }

    public function activeReservation(): ?BookReservation
    {
        return $this->reservations()
            ->whereIn('status', ['pending', 'approved'])
            ->where('expires_at', '>', now())
            ->orderBy('queue_position')
            ->first();
    }

    public function copyStatusLabel(): string
    {
        return match ($this->copy_status) {
            'available' => 'Tersedia',
            'borrowed' => 'Dipinjam',
            'reserved' => 'Direservasi',
            'lost' => 'Hilang',
            'damaged' => 'Rusak',
            'maintenance' => 'Perawatan',
            default => ucfirst((string) $this->copy_status),
        };
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
