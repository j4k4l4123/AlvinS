<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LibraryCard extends Model
{
    protected $fillable = [
        'user_id',
        'anggota_id',
        'card_number',
        'status',
        'issued_date',
        'expiry_date',
    ];

    protected $casts = [
        'issued_date' => 'date',
        'expiry_date' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function anggota(): BelongsTo
    {
        return $this->belongsTo(Anggota::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active'
            && $this->expiry_date !== null
            && $this->expiry_date->isFuture();
    }
}

