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
        $query = Pinjam::with(['anggota', 'book']);

        if ($request->filled('search')) {
            $search = strtolower($request->search);
            $query->where(function ($q) use ($search) {
                $q->whereHas('book', function ($qb) use ($search) {
                    $qb->whereRaw('LOWER(judul) LIKE ?', ['%' . $search . '%']);
                })->orWhereHas('anggota', function ($qa) use ($search) {
                    $qa->whereRaw('LOWER(nama) LIKE ?', ['%' . $search . '%']);
                });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $pinjam = $query->latest()->paginate(10)->withQueryString();

        return view('pinjam.index', compact('pinjam'));
    }

    public function create()
    {
        BookReservation::where('status', 'pending')
            ->where('expires_at', '<=', now())
            ->update(['status' => 'expired']);

        $anggota = Anggota::with(['user.memberProfile', 'libraryCard'])->orderBy('nama')->get();
        $books = Book::with(['reservations' => function ($query) {
                $query->where('status', 'pending')->where('expires_at', '>', now());
            }])
            ->orderBy('judul')
            ->get()
            ->filter(fn (Book $book) => $book->isAvailable());

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
        $pinjam = Pinjam::with(['anggota', 'book', 'pengembalian'])->findOrFail($id);
        $fineAmount = $pinjam->calculateFine();

        return view('pinjam.show', compact('pinjam', 'fineAmount'));
    }

    public function edit($id)
    {
        $pinjam = Pinjam::findOrFail($id);
        $anggota = Anggota::orderBy('nama')->get();
        $books = Book::orderBy('judul')->get();

        return view('pinjam.edit', compact('pinjam', 'anggota', 'books'));
    }

    public function update(PinjamRequest $request, $id)
    {
        $pinjam = Pinjam::findOrFail($id);
        $validated = $request->validated();

        if ((int) $validated['book_id'] !== (int) $pinjam->book_id) {
            $book = Book::find($validated['book_id']);
            if ($book && ! $book->isAvailable()) {
                return redirect()->back()->withInput()->with('error', 'Buku ini sedang dipinjam!');
            }
        }

        $pinjam->update($validated);

        return redirect()->route('pinjam.index')->with('success', 'Data peminjaman berhasil diperbarui!');
    }

    public function destroy($id)
    {
        $this->borrowingService->cancel($id);

        return redirect()->route('pinjam.index')->with('success', 'Data peminjaman berhasil dihapus!');
    }

    public function overdue()
    {
        $overdue = $this->borrowingService->getOverdueBorrowings();

        return view('pinjam.overdue', compact('overdue'));
    }
}
