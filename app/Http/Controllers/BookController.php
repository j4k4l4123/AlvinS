<?php

namespace App\Http\Controllers;

use App\Http\Requests\BookRequest;
use App\Models\Book;
use App\Models\BookReservation;
use App\Models\Catalog;
use App\Models\racks;
use App\Services\InventoryService;
use Illuminate\Http\Request;

class BookController extends Controller
{
    public function __construct(protected InventoryService $inventoryService)
    {
    }

    public function books(Request $request, Catalog $catalog)
    {
        BookReservation::where('status', 'pending')
            ->where('expires_at', '<=', now())
            ->update(['status' => 'expired']);

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
        })->with(['pinjam' => function ($q) {
            $q->where('status', 'dipinjam');
        }]);

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

        return view('books.index', compact('books'));
    }

    public function create()
    {
        $next = 1;
        $last = Book::orderBy('id', 'desc')->first();

        if ($last && preg_match('/(\d+)/', $last->id_buku, $matches)) {
            $next = (int) $matches[1] + 1;
        }

        $nextIdBuku = 'BKU' . str_pad($next, 3, '0', STR_PAD_LEFT);
        $racks = racks::orderBy('name')->get();

        return view('books.create', compact('nextIdBuku', 'racks'));
    }

    public function store(BookRequest $request)
    {
        $validated = $request->validated();
        $validated['copy_code_prefix'] = $validated['copy_code_prefix'] ?? $validated['id_buku'];
        $validated['copy_status'] = $validated['copy_status'] ?? 'available';
        $validated['copy_condition'] = $validated['copy_condition'] ?? 'good';

        Book::create($validated);
        return redirect()->route('books.index')->with('success', 'Buku berhasil ditambahkan!');
    }

    public function show($id)
    {
        $book = Book::with(['pinjam' => function ($query) {
            $query->where('status', 'dipinjam');
        }, 'rak', 'reservasi.anggota'])->findOrFail($id);
        return view('books.show', compact('book'));
    }

    public function edit($id)
    {
        $book = Book::findOrFail($id);
        $racks = racks::orderBy('name')->get();
        return view('books.edit', compact('book', 'racks'));
    }

    public function update(BookRequest $request, $id)
    {
        $book = Book::findOrFail($id);
        $validated = $request->validated();
        $validated['copy_code_prefix'] = $validated['copy_code_prefix'] ?? $book->copy_code_prefix ?? $validated['id_buku'];
        $validated['copy_status'] = $validated['copy_status'] ?? $book->copy_status ?? 'available';
        $validated['copy_condition'] = $validated['copy_condition'] ?? $book->copy_condition ?? 'good';
        $validated['max_loan_days'] = $validated['max_loan_days'] ?? $book->max_loan_days ?? 14;
        $validated['max_renewals'] = $validated['max_renewals'] ?? $book->max_renewals ?? 1;

        $book->update($validated);
        $this->inventoryService->refreshBookStatus($book->fresh());
        return redirect()->route('books.index')->with('success', 'Buku berhasil diperbarui!');
    }

    public function destroy($id)
    {
        $book = Book::findOrFail($id);
        $book->delete();
        return redirect()->route('books.index')->with('success', 'Buku berhasil dihapus!');
    }
}

