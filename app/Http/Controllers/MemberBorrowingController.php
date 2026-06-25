<?php

namespace App\Http\Controllers;

use App\Support\NotificationHelper;
use App\Services\BorrowingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class MemberBorrowingController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $anggota = $user?->anggota;
        
        $libraryCard = $anggota
            ? DB::table('library_cards')->where('anggota_id', $anggota->id)->first()
            : null;

        $activeBorrowings = $anggota
            ? DB::table('pinjam')
                ->leftJoin('books', 'pinjam.book_id', '=', 'books.id')
                ->leftJoin('fines', 'fines.pinjam_id', '=', 'pinjam.id')
                ->where('pinjam.anggota_id', $anggota->id)
                ->where('pinjam.status', 'dipinjam')
                ->orderByDesc('pinjam.created_at')
                ->select(
                    'pinjam.*', 
                    'books.judul as book_judul', 
                    'fines.amount as fine_amount', 
                    'fines.status as fine_status'
                )
                ->paginate(10, ['*'], 'borrow_page')
            : collect();

        if ($activeBorrowings instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator) {
            $activeBorrowings->getCollection()->transform(function ($item) {
                $item->book = (object) ['judul' => $item->book_judul ?? '-'];
                $item->fine = ($item->fine_amount !== null)
                    ? (object) ['amount' => $item->fine_amount, 'status' => $item->fine_status]
                    : null;
                $item->tanggal_pinjam = $item->tanggal_pinjam ? \Carbon\Carbon::parse($item->tanggal_pinjam) : null;
                $item->tanggal_kembali = $item->tanggal_kembali ? \Carbon\Carbon::parse($item->tanggal_kembali) : null;
                return $item;
            });
        }

        $pendingRenewals = $anggota
            ? DB::table('renewal_requests')
                ->where('anggota_id', $anggota->id)
                ->where('status', 'pending')
                ->pluck('notes', 'pinjam_id')
            : collect();

        $activeReservations = $anggota
            ? DB::table('book_reservations')
                ->leftJoin('books', 'book_reservations.book_id', '=', 'books.id')
                ->where('book_reservations.anggota_id', $anggota->id)
                ->whereIn('book_reservations.status', ['pending', 'approved'])
                ->where('book_reservations.expires_at', '>', now())
                ->orderByDesc('book_reservations.created_at')
                ->select('book_reservations.*', 'books.judul as book_judul')
                ->get()
            : collect();

        $activeReservations->transform(function ($item) {
            $item->book = (object) ['judul' => $item->book_judul ?? '-'];
            $item->expires_at = $item->expires_at ? \Carbon\Carbon::parse($item->expires_at) : null;
            return $item;
        });

        return view('member.borrowings', compact('activeBorrowings', 'libraryCard', 'pendingRenewals', 'activeReservations'));
    }

    public function store(Request $request, BorrowingService $borrowingService): RedirectResponse
    {
        $validated = $request->validate([
            'card_number' => ['required', 'string'],
            'book_barcode' => ['required', 'string'],
        ]);

        try {
            $pinjam = $borrowingService->borrowByBarcodes($validated['card_number'], $validated['book_barcode']);
            
            // Fetch book details from DB
            $book = DB::table('books')->where('id', $pinjam->book_id)->first();
            $rack = $book?->rack_id ? DB::table('racks')->where('id', $book->rack_id)->first() : null;

            $rackMessage = $rack?->name
                ? ' Rak buku: ' . $rack->name . '.'
                : '';

            NotificationHelper::send(
                $request->user()->id,
                'borrowing_created',
                'Peminjaman berhasil dibuat',
                'Buku "' . ($book?->judul ?? '-') . '" berhasil dipinjam.' . $rackMessage,
                ['pinjam_id' => $pinjam->id]
            );

            return back()->with('success', 'Buku berhasil dipinjam.' . $rackMessage);
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function renew(Request $request, $pinjamId): RedirectResponse
    {
        $pinjam = DB::table('pinjam')->where('id', $pinjamId)->first();
        abort_if(! $pinjam, 404);

        $anggota = DB::table('anggota')->where('id', $pinjam->anggota_id)->first();
        abort_if(! $anggota || $anggota->user_id !== $request->user()?->id, 403);
        abort_if($pinjam->status !== 'dipinjam', 422, 'Peminjaman ini tidak aktif.');

        $exists = DB::table('renewal_requests')
            ->where('anggota_id', $pinjam->anggota_id)
            ->where('status', 'pending')
            ->where('pinjam_id', $pinjam->id)
            ->exists();

        if ($exists) {
            return back()->with('error', 'Permintaan perpanjangan untuk buku ini sudah menunggu persetujuan pustakawan.');
        }

        $book = DB::table('books')->where('id', $pinjam->book_id)->first();

        $renewalId = DB::table('renewal_requests')->insertGetId([
            'user_id' => $request->user()->id,
            'anggota_id' => $pinjam->anggota_id,
            'pinjam_id' => $pinjam->id,
            'status' => 'pending',
            'notes' => 'Permintaan perpanjangan untuk buku: ' . ($book?->judul ?? '-'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        NotificationHelper::send(
            $request->user()->id,
            'renewal_request_created',
            'Permintaan perpanjangan dikirim',
            'Permintaan perpanjangan untuk buku "' . ($book?->judul ?? '-') . '" berhasil dikirim ke librarian.',
            ['renewal_request_id' => $renewalId]
        );

        return back()->with('success', 'Permintaan perpanjangan berhasil dikirim ke librarian untuk disetujui.');
    }

    public function reserve(Request $request, $bookId, BorrowingService $borrowingService): RedirectResponse
    {
        $anggota = $request->user()?->anggota;
        abort_unless($anggota, 403);

        $book = DB::table('books')->where('id', $bookId)->first();
        abort_if(! $book, 404);

        try {
            $reservation = $borrowingService->reserve($anggota->id, $book->id, $request->user()->id);

            NotificationHelper::send(
                $request->user()->id,
                'reservation_created',
                'Reservasi berhasil dibuat',
                'Reservasi buku "' . ($book->judul ?? '-') . '" berhasil diajukan dan sedang menunggu persetujuan librarian.',
                ['reservation_id' => $reservation->id]
            );
        } catch (\Throwable $e) {
            return back()->with('error', 'Reservasi gagal: ' . $e->getMessage());
        }

        return back()->with('success', 'Reservasi berhasil diajukan dan sedang menunggu persetujuan librarian.');
    }

    public function cancelReservation(Request $request, $reservationId): RedirectResponse
    {
        $reservation = DB::table('book_reservations')->where('id', $reservationId)->first();
        abort_if(! $reservation, 404);
        abort_if($reservation->user_id !== $request->user()?->id, 403);
        abort_if(! in_array($reservation->status, ['pending', 'approved'], true), 422, 'Reservasi ini tidak bisa dibatalkan.');

        $book = DB::table('books')->where('id', $reservation->book_id)->first();
        $title = $book?->judul ?? '-';

        DB::table('book_reservations')->where('id', $reservationId)->delete();

        NotificationHelper::send(
            $request->user()->id,
            'reservation_cancelled',
            'Reservasi dibatalkan',
            'Reservasi buku "' . $title . '" berhasil dibatalkan.',
            []
        );

        return back()->with('success', 'Reservasi berhasil dibatalkan.');
    }
}
