<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Book extends Model
{
    protected $table = 'books';

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

    public function penulis(): BelongsTo
    {
        return $this->belongsTo(authors::class, 'author_id');
    }

    public function kategori_relasi(): BelongsTo
    {
        return $this->belongsTo(categories::class, 'category_id');
    }

    public function rack(): BelongsTo
    {
        return $this->belongsTo(Racks::class, 'rack_id');
    }

    // Backward compatibility (old name)
    public function rak(): BelongsTo
    {
        return $this->rack();
    }




    public function pinjam(): HasMany

    {
        return $this->hasMany(Pinjam::class, 'book_id');
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(BookReservation::class, 'book_id');
    }

    public function reservasi(): HasMany
    {
        return $this->reservations();
    }


    public function pengembalian(): HasMany
    {
        return $this->hasMany(Pengembalian::class, 'book_id');
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
        return $this->reservasi()
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
            $kw = strtolower((string) $keyword);

            $q->whereRaw('LOWER(judul) LIKE ?', ['%' . $kw . '%'])
                ->orWhereRaw('LOWER(pengarang) LIKE ?', ['%' . $kw . '%'])
                ->orWhereRaw('LOWER(kategori) LIKE ?', ['%' . $kw . '%'])
                ->orWhereRaw('LOWER(subject) LIKE ?', ['%' . $kw . '%'])
                ->orWhereRaw('LOWER(id_buku) LIKE ?', ['%' . $kw . '%'])
                ->orWhereRaw('LOWER(COALESCE(barcode, \'\')) LIKE ?', ['%' . $kw . '%'])
                ->orWhereRaw('LOWER(COALESCE(isbn, \'\')) LIKE ?', ['%' . $kw . '%']);

            // `thn_terbit` is stored as integer (year).
            // Add year matching only when keyword contains digits.
            if (preg_match('/\d+/', (string) $keyword) === 1) {
                $digits = preg_replace('/\D/', '', (string) $keyword);
                $q->orWhereRaw('CAST(thn_terbit AS TEXT) LIKE ?', ['%' . $digits . '%']);
            }
        });
    }

    public function scopeFilterByCategory($query, ?string $category)
    {
        if ($category) {
            return $query->where(function ($q) use ($category) {
                $q->where('kategori', $category)
                    ->orWhereHas('kategori_relasi', function ($categoryQuery) use ($category) {
                        $categoryQuery->where('name', $category);
                    });
            });
        }

        return $query;
    }
}
