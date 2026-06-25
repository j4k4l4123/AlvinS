<?php

namespace App\Http\Controllers;

use App\Services\FineService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class MemberController extends Controller
{
    public function dashboard(FineService $fineService)
    {
        $user = auth()->user();
        $profile = $user?->memberProfile;
        $anggota = $user?->anggota;

        if (! $anggota && $profile?->id_anggota) {
            $anggota = DB::table('anggota')
                ->where('id_anggota', $profile->id_anggota)
                ->first();
        }

        if ($anggota) {
            $activeBorrowingsQuery = DB::table('pinjam')
                ->leftJoin('books', 'pinjam.book_id', '=', 'books.id')
                ->where('pinjam.anggota_id', $anggota->id)
                ->where('pinjam.status', 'dipinjam')
                ->orderByDesc('pinjam.created_at')
                ->select('pinjam.*', 'books.judul as book_judul');

            $activeBorrowings = $activeBorrowingsQuery->paginate(5, ['*'], 'active_page');

            $activeBorrowings->getCollection()->transform(function ($item) {
                $item->book = (object) ['judul' => $item->book_judul ?? '-'];
                $item->tanggal_pinjam = $item->tanggal_pinjam ? Carbon::parse($item->tanggal_pinjam) : null;
                $item->tanggal_kembali = $item->tanggal_kembali ? Carbon::parse($item->tanggal_kembali) : null;
                return $item;
            });

            $historyQuery = DB::table('pinjam')
                ->leftJoin('books', 'pinjam.book_id', '=', 'books.id')
                ->leftJoin('pengembalian', 'pengembalian.pinjam_id', '=', 'pinjam.id')
                ->leftJoin('fines', 'fines.pinjam_id', '=', 'pinjam.id')
                ->where('pinjam.anggota_id', $anggota->id)
                ->orderByDesc('pinjam.created_at')
                ->select(
                    'pinjam.*',
                    'books.judul as book_judul',
                    'pengembalian.tanggal_dikembalikan',
                    'fines.amount as fine_amount',
                    'fines.status as fine_status'
                );

            $borrowingHistory = $historyQuery->paginate(10, ['*'], 'history_page');

            $borrowingHistory->getCollection()->transform(function ($item) {
                $item->book = (object) ['judul' => $item->book_judul ?? '-'];
                $item->tanggal_pinjam = $item->tanggal_pinjam ? Carbon::parse($item->tanggal_pinjam) : null;
                $item->tanggal_kembali = $item->tanggal_kembali ? Carbon::parse($item->tanggal_kembali) : null;
                $item->pengembalian = $item->tanggal_dikembalikan
                    ? (object) ['tanggal_dikembalikan' => Carbon::parse($item->tanggal_dikembalikan)]
                    : null;
                $item->fine = ($item->fine_amount !== null)
                    ? (object) ['amount' => $item->fine_amount, 'status' => $item->fine_status]
                    : null;
                return $item;
            });
        } else {
            $activeBorrowings = collect();
            $borrowingHistory = collect();
        }

        $libraryCard = $anggota
            ? DB::table('library_cards')->where('anggota_id', $anggota->id)->first()
            : null;

        $pendingCancellation = $user
            ? DB::table('membership_requests')
                ->where('user_id', $user->id)
                ->where('type', 'cancellation')
                ->where('status', 'pending')
                ->whereNull('deleted_at')
                ->orderByDesc('created_at')
                ->first()
            : null;

        $totalFines = $anggota ? $fineService->getTotalFines($anggota->id) : 0;

        return view('member.dashboard', compact(
            'profile',
            'anggota',
            'activeBorrowings',
            'borrowingHistory',
            'libraryCard',
            'pendingCancellation',
            'totalFines'
        ));
    }
}
