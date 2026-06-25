<?php

namespace App\Http\Controllers;

use App\Http\Requests\PinjamRequest;
use App\Models\Anggota;
use App\Models\Book;
use App\Models\BookReservation;
use App\Models\Pinjam;
use App\Services\BorrowingService;
use App\Services\FineService;
use Illuminate\Http\Request;

class PinjamController extends Controller
{
    protected BorrowingService $borrowingService;
    protected FineService $fineService;

    public function __construct(BorrowingService $borrowingService, FineService $fineService)
    {
        $this->borrowingService = $borrowingService;
        $this->fineService = $fineService;
    }

    public function index(Request $request)
    {
        $search = $request->filled('search') ? strtolower((string) $request->search) : null;
        $status = $request->filled('status') ? (string) $request->status : null;

        $query = \Illuminate\Support\Facades\DB::table('pinjam')
            ->select([
                'pinjam.*',
                'anggota.nama as anggota_nama',
                'anggota.id_anggota as anggota_id_anggota',
                'books.judul as book_judul',
                'books.id_buku as book_id_buku',
            ])
            ->leftJoin('anggota', 'anggota.id', '=', 'pinjam.anggota_id')
            ->leftJoin('books', 'books.id', '=', 'pinjam.book_id');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(books.judul) LIKE ?', ['%' . $search . '%'])
                    ->orWhereRaw('LOWER(books.id_buku) LIKE ?', ['%' . $search . '%'])
                    ->orWhereRaw('LOWER(anggota.nama) LIKE ?', ['%' . $search . '%'])
                    ->orWhereRaw('LOWER(anggota.id_anggota) LIKE ?', ['%' . $search . '%']);
            });
        }

        if ($status) {
            $query->where('pinjam.status', $status);
        }

        $pinjam = $query
            ->orderByDesc('pinjam.id')
            ->paginate(10)
            ->withQueryString();

        $pinjam->getCollection()->transform(function ($p) {
            $p->book = (object) [
                'id_buku' => $p->book_id_buku,
                'judul' => $p->book_judul,
            ];
            $p->anggota = (object) [
                'nama' => $p->anggota_nama,
                'id_anggota' => $p->anggota_id_anggota,
            ];
            if ($p->tanggal_pinjam) {
                $p->tanggal_pinjam = \Carbon\Carbon::parse($p->tanggal_pinjam);
            }
            if ($p->tanggal_kembali) {
                $p->tanggal_kembali = \Carbon\Carbon::parse($p->tanggal_kembali);
            }
            return $p;
        });

        return view('pinjam.index', compact('pinjam'));
    }

    public function create()
    {
        \Illuminate\Support\Facades\DB::table('book_reservations')
            ->where('status', 'pending')
            ->where('expires_at', '<=', now())
            ->update(['status' => 'expired']);

        // Anggota is query-builder based now (no Eloquent relations).
        $anggota = \Illuminate\Support\Facades\DB::table('anggota')
            ->orderBy('nama')
            ->get();

        $rawBooks = \Illuminate\Support\Facades\DB::table('books')
            ->orderBy('judul')
            ->get();

        $books = collect();

        foreach ($rawBooks as $book) {
            if (Book::canBeBorrowed($book)) {
                $reservations = \Illuminate\Support\Facades\DB::table('book_reservations')
                    ->where('book_id', $book->id)
                    ->where('status', 'pending')
                    ->where('expires_at', '>', now())
                    ->get()
                    ->map(function ($res) {
                        if ($res->expires_at) {
                            $res->expires_at = \Illuminate\Support\Carbon::parse($res->expires_at);
                        }
                        return $res;
                    });

                $book->reservations = $reservations;
                $books->push($book);
            }
        }

        return view('pinjam.create', compact('anggota', 'books'));
    }

    public function store(PinjamRequest $request)
    {
        try {
            $this->borrowingService->borrow(
                $request->integer('anggota_id'),
                $request->integer('book_id'),
                $request->input('tanggal_pinjam'),
                $request->input('tanggal_kembali')
            );

            return redirect()->route('pinjam.index')->with('success', 'Buku berhasil dipinjam!');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function show($id)
    {
        $pinjam = Pinjam::find($id);
        if (!$pinjam) {
            abort(404);
        }

        $anggota = Anggota::find($pinjam->anggota_id);
        $book = Book::find($pinjam->book_id);
        $pengembalian = null;

        $pinjamObj = (object) array_merge((array) $pinjam, [
            'anggota' => $anggota,
            'book' => $book,
            'pengembalian' => $pengembalian,
        ]);

        $fineAmount = Pinjam::calculateFine($pinjamObj);

        return view('pinjam.show', ['pinjam' => $pinjamObj, 'fineAmount' => $fineAmount]);
    }

    public function edit($id)
    {
        $pinjam = Pinjam::find($id);
        if (!$pinjam) {
            abort(404);
        }

        $anggota = \Illuminate\Support\Facades\DB::table('anggota')
            ->orderBy('nama')
            ->get();

        $books = \Illuminate\Support\Facades\DB::table('books')
            ->orderBy('judul')
            ->get();

        return view('pinjam.edit', ['pinjam' => $pinjam, 'anggota' => $anggota, 'books' => $books]);
    }

    public function update(PinjamRequest $request, $id)
    {
        $pinjam = Pinjam::find($id);
        if (!$pinjam) {
            abort(404);
        }

        $validated = $request->validated();

        $oldBookId = (int) $pinjam->book_id;
        $newBookId = (int) $validated['book_id'];

        if ($newBookId !== $oldBookId) {
            $book = \Illuminate\Support\Facades\DB::table('books')->where('id', $newBookId)->first();
            if ($book && isset($book->stock) && ((int) $book->stock) <= 0) {
                return redirect()->back()->withInput()->with('error', 'Buku ini sedang dipinjam!');
            }
        }

        \Illuminate\Support\Facades\DB::table('pinjam')->where('id', $id)->update($validated);

        if ($newBookId !== $oldBookId) {
            $newBook = \Illuminate\Support\Facades\DB::table('books')->where('id', $newBookId)->first();
            $copyCodePrefix = $newBook?->copy_code_prefix;

            \Illuminate\Support\Facades\DB::table('pinjam')->where('id', $id)->update([
                'copy_code' => $copyCodePrefix
                    ? strtoupper((string) $copyCodePrefix) . '-MANUAL-' . str_pad((string) $id, 5, '0', STR_PAD_LEFT)
                    : ($validated['copy_code'] ?? $pinjam->copy_code),
            ]);

            $oldBook = Book::find($oldBookId);
            $newBook = Book::find($newBookId);
            if ($oldBook) {
                $this->borrowingService->refreshBookInventory($oldBook);
            }
            if ($newBook) {
                $this->borrowingService->refreshBookInventory($newBook);
            }
        }

        return redirect()->route('pinjam.index')->with('success', 'Data peminjaman berhasil diperbarui!');
    }

    public function destroy($id)
    {
        $this->borrowingService->cancel($id);

        return redirect()->route('pinjam.index')->with('success', 'Data peminjaman berhasil dihapus!');
    }

    public function markLost($id)
    {
        $pinjam = Pinjam::find($id);
        if (!$pinjam) {
            abort(404);
        }

        $this->borrowingService->markLost($pinjam);

        return redirect()->route('pinjam.show', $pinjam->id)->with('success', 'Peminjaman ditandai sebagai buku hilang dan denda sudah dibuat.');
    }

    public function markDamaged($id)
    {
        $pinjam = Pinjam::find($id);
        if (!$pinjam) {
            abort(404);
        }

        $this->borrowingService->markDamaged($pinjam);

        return redirect()->route('pinjam.show', $pinjam->id)->with('success', 'Peminjaman ditandai sebagai buku rusak dan denda sudah dibuat.');
    }

    public function overdue()
    {
        $overdue = $this->borrowingService->getOverdueBorrowings();

        return view('pinjam.overdue', compact('overdue'));
    }
}
