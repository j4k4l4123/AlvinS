<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Query\Builder;

class Catalog
{
    public function search(?string $keyword = null, array $filters = []): Builder
    {
        $query = DB::table('books')
            ->leftJoin('racks', 'books.rack_id', '=', 'racks.id')
            ->select('books.*', 'racks.name as rack_name', 'racks.code as rack_code');

        if ($keyword) {
            $query->where(function ($q) use ($keyword) {
                $kw = strtolower((string) $keyword);
                $q->whereRaw('LOWER(judul) LIKE ?', ['%' . $kw . '%'])
                  ->orWhereRaw('LOWER(pengarang) LIKE ?', ['%' . $kw . '%'])
                  ->orWhereRaw('LOWER(kategori) LIKE ?', ['%' . $kw . '%'])
                  ->orWhereRaw('LOWER(subject) LIKE ?', ['%' . $kw . '%'])
                  ->orWhereRaw('LOWER(id_buku) LIKE ?', ['%' . $kw . '%'])
                  ->orWhereRaw('LOWER(COALESCE(barcode, \'\')) LIKE ?', ['%' . $kw . '%'])
                  ->orWhereRaw('LOWER(COALESCE(isbn, \'\')) LIKE ?', ['%' . $kw . '%']);

                if (preg_match('/\d+/', (string) $keyword) === 1) {
                    $digits = preg_replace('/\D/', '', (string) $keyword);
                    $q->orWhereRaw('CAST(thn_terbit AS TEXT) LIKE ?', ['%' . $digits . '%']);
                }
            });
        }

        if (! empty($filters['kategori'])) {
            $category = $filters['kategori'];
            $query->where(function ($q) use ($category) {
                $q->where('kategori', $category)
                  ->orWhereExists(function ($sub) use ($category) {
                      $sub->select(DB::raw(1))
                          ->from('categories')
                          ->whereColumn('categories.id', 'books.category_id')
                          ->where('categories.name', $category);
                  });
            });
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

        if (! empty($filters['from_year'])) {
            $query->where('thn_terbit', '>=', (int) $filters['from_year']);
        }

        if (! empty($filters['to_year'])) {
            $query->where('thn_terbit', '<=', (int) $filters['to_year']);
        }

        return $query;
    }
}
