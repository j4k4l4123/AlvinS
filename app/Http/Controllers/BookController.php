<?php

namespace App\Http\Controllers;

use App\Http\Requests\BookRequest;
use App\Models\Book;
use App\Models\BookReservation;
use App\Models\Catalog;
use App\Models\racks;
use App\Services\InventoryService;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BookController extends Controller
{
    public function __construct(protected InventoryService $inventoryService)
    {
    }

    public function books(Request $request, Catalog $catalog)
    {
        DB::table('book_reservations')
            ->where('status', 'pending')
            ->where('expires_at', '<=', now())
            ->update(['status' => 'expired']);

        /** @var Builder $query */
        $query = $catalog->search($request->search, [
            'kategori' => $request->get('kategori'),
            'subject' => $request->get('subject'),
            'author' => $request->get('author'),
            'availability' => $request->get('availability'),
            'from_year' => $request->get('from_year'),
            'to_year' => $request->get('to_year'),
        ])->when($request->filled('subject_exact'), function ($q) use ($request) {
            // subject_exact: filter tambahan khusus untuk librarian untuk menghindari input ganda
            $q->where('subject', $request->get('subject_exact'));
        });

        // Handle release_sort shortcut (overrides sort/order)
        $releaseSortValue = $request->get('release_sort');
        if ($releaseSortValue === 'newest') {
            $sort = 'thn_terbit';
            $order = 'desc';
        } elseif ($releaseSortValue === 'oldest') {
            $sort = 'thn_terbit';
            $order = 'asc';
        } else {
            $allowedSorts = ['judul', 'pengarang', 'kategori', 'thn_terbit', 'created_at', 'stock'];
            $sort = $request->get('sort', 'judul');
            $order = strtolower((string) $request->get('order', 'asc')) === 'desc' ? 'desc' : 'asc';
            if (! in_array($sort, $allowedSorts, true)) {
                $sort = 'judul';
            }
        }
        $query->orderBy($sort, $order);

        $books = $query->paginate(9)->withQueryString();

        $books->getCollection()->transform(function ($book) {
            $book->rack = $book->rack_id ? (object) [
                'name' => $book->rack_name,
                'code' => $book->rack_code,
            ] : null;
            return $book;
        });

        return view('books.index', compact('books'));
    }

    public function create()
    {
        $next = 1;

        $last = DB::table('books')->orderBy('id', 'desc')->first();
        if ($last && preg_match('/(\d+)/', (string) $last->id_buku, $matches)) {
            $next = (int) $matches[1] + 1;
        }

        $nextIdBuku = 'BKU' . str_pad($next, 3, '0', STR_PAD_LEFT);
        $racks = DB::table('racks')->orderBy('name')->get();

        return view('books.create', compact('nextIdBuku', 'racks'));
    }

    public function store(BookRequest $request)
    {
        $validated = $request->validated();
        $validated['copy_code_prefix'] = $validated['copy_code_prefix'] ?? $validated['id_buku'];
        $validated['copy_status'] = $validated['copy_status'] ?? 'available';
        $validated['copy_condition'] = $validated['copy_condition'] ?? 'good';

        DB::table('books')->insert($validated);
        return redirect()->route('books.index')->with('success', 'Buku berhasil ditambahkan!');
    }

    public function show($id)
    {
        // Since Book is now query-builder style, fetch the base row directly.
        $book = Book::find($id);
        abort_if($book === null, 404);

        // Add commonly used related data for the view.
        $book->pinjam_dipinjam = DB::table('pinjam')
            ->where('book_id', $book->id)
            ->where('status', 'dipinjam')
            ->get();

        $book->rack = DB::table('racks')->where('id', $book->rack_id)->first();

        $book->reservasi = DB::table('book_reservations')
            ->where('book_id', $book->id)
            ->whereIn('status', ['pending', 'approved'])
            ->orderBy('queue_position')
            ->get();

        // Reservation -> anggota
        foreach ($book->reservasi as $reservation) {
            $reservation->anggota = DB::table('anggota')
                ->where('id', $reservation->anggota_id)
                ->first();
        }

        return view('books.show', compact('book'));
    }

    public function edit($id)
    {
        $book = Book::find($id);
        abort_if($book === null, 404);

        $racks = DB::table('racks')->orderBy('name')->get();
        return view('books.edit', compact('book', 'racks'));
    }

    public function update(BookRequest $request, $id)
    {
        $book = Book::find($id);
        abort_if($book === null, 404);

        $validated = $request->validated();
        $validated['copy_code_prefix'] = $validated['copy_code_prefix'] ?? $book->copy_code_prefix ?? $validated['id_buku'];
        $validated['copy_status'] = $validated['copy_status'] ?? $book->copy_status ?? 'available';
        $validated['copy_condition'] = $validated['copy_condition'] ?? $book->copy_condition ?? 'good';
        $validated['max_loan_days'] = $validated['max_loan_days'] ?? $book->max_loan_days ?? 14;
        $validated['max_renewals'] = $validated['max_renewals'] ?? $book->max_renewals ?? 1;

        DB::table('books')->where('id', $id)->update($validated);

    
        $stock = (int) DB::table('books')->where('id', $id)->value('stock');

        $bookRow = DB::table('books')->where('id', $id)->first();
        if ($bookRow) {
            $copyStatus = $bookRow->copy_status;

            $hasReservation = DB::table('book_reservations')
                ->where('book_id', $bookRow->id)
                ->whereIn('status', ['pending', 'approved'])
                ->where('expires_at', '>', now())
                ->exists();

            $activeBorrows = DB::table('pinjam')
                ->where('book_id', $bookRow->id)
                ->where('status', 'dipinjam')
                ->count();

            if (($bookRow->copy_status ?? null) === 'lost' || ($bookRow->copy_condition ?? null) === 'lost') {
                $copyStatus = 'lost';
            } elseif (($bookRow->copy_status ?? null) === 'maintenance') {
                $copyStatus = $copyStatus; // keep
            } elseif (($bookRow->copy_condition ?? null) === 'damaged' || ($bookRow->copy_status ?? null) === 'damaged') {
                $copyStatus = 'damaged';
            } elseif ($activeBorrows >= $stock) {
                $copyStatus = 'borrowed';
            } elseif ($hasReservation) {
                $copyStatus = 'reserved';
            } else {
                $copyStatus = 'available';
            }

            DB::table('books')->where('id', $id)->update(['copy_status' => $copyStatus]);
        }

        return redirect()->route('books.index')->with('success', 'Buku berhasil diperbarui!');
    }

    public function destroy($id)
    {
        $deleted = DB::table('books')->where('id', $id)->delete();
        abort_if($deleted === 0, 404);

        return redirect()->route('books.index')->with('success', 'Buku berhasil dihapus!');
    }
}


