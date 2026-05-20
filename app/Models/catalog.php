<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;

class Catalog
{
    public function search(?string $keyword = null, array $filters = []): Builder
    {
        $query = Book::query()->with(['rack', 'reservations.anggota']);

        if ($keyword) {
            $query->search($keyword);
        }

        if (! empty($filters['kategori'])) {
            $query->filterByCategory($filters['kategori']);
        }

        if (! empty($filters['subject'])) {
            $query->whereRaw('LOWER(subject) LIKE ?', ['%' . strtolower($filters['subject']) . '%']);
        }

        if (! empty($filters['author'])) {
            $query->whereRaw('LOWER(pengarang) LIKE ?', ['%' . strtolower($filters['author']) . '%']);
        }

        if (! empty($filters['availability'])) {
            if ($filters['availability'] === 'available') {
                $query->where('reference_only', false)
                    ->whereNotIn('copy_status', ['lost', 'damaged', 'maintenance']);
            }

            if ($filters['availability'] === 'reference_only') {
                $query->where('reference_only', true);
            }
        }

        return $query;
    }
}
