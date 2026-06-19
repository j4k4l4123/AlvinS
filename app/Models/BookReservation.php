<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookReservation extends Model
{
    protected $table = 'book_reservations';

    protected $fillable = [
        'user_id',
        'anggota_id',
        'book_id',
        'queue_position',
        'status',
        'approved_at',
        'expires_at',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function anggota(): BelongsTo
    {
        return $this->belongsTo(Anggota::class);
    }

    public function buku(): BelongsTo
    {
        return $this->belongsTo(Book::class, 'book_id');
    }

    public function isActive(): bool
    {
        return in_array($this->status, ['pending', 'approved'], true) && $this->expires_at && $this->expires_at->isFuture();
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved' && $this->expires_at && $this->expires_at->isFuture();
    }
}
