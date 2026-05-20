<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Pinjam extends Model
{
    use HasFactory;

    protected $table = 'pinjam';

    protected $fillable = [
        'anggota_id',
        'book_id',
        'copy_code',
        'tanggal_pinjam',
        'tanggal_kembali',
        'status',
        'renewal_count',
        'lost_at',
        'damaged_at',
    ];

    protected $casts = [
        'tanggal_pinjam' => 'date',
        'tanggal_kembali' => 'date',
        'lost_at' => 'datetime',
        'damaged_at' => 'datetime',
    ];

    public function anggota(): BelongsTo
    {
        return $this->belongsTo(Anggota::class);
    }

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    public function pengembalian(): HasOne
    {
        return $this->hasOne(Pengembalian::class);
    }

    public function fine(): HasOne
    {
        return $this->hasOne(Fine::class, 'pinjam_id');
    }

    public function isOverdue(): bool
    {
        return $this->status === 'dipinjam' && Carbon::today()->gt($this->tanggal_kembali);
    }

    public function daysOverdue(): int
    {
        if (! $this->isOverdue()) {
            return 0;
        }

        return Carbon::today()->diffInDays($this->tanggal_kembali);
    }

    public function calculateFine(): int
    {
        if (! $this->isOverdue()) {
            return 0;
        }

        return $this->daysOverdue() * (int) round((float) ($this->book?->daily_late_fee ?? 5000));
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', 'dipinjam')
            ->whereDate('tanggal_kembali', '<', Carbon::today());
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'dipinjam');
    }
}
