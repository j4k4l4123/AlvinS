<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PageController extends Controller
{
    public function login()
    {
        return view('login');
    }

    public function test()
    {
        return view('test');
    }

    public function logout(): RedirectResponse
    {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Berhasil logout.');
    }

    public function dashboard()
    {
        $user = Auth::user();

        if (! $user?->isLibrarian()) {
            return redirect()->route('member.dashboard');
        }

        $name = 'librarian';

        $totalBooks = DB::table('books')->count();

        $activeLoans = DB::table('pinjam')
            ->where('status', 'dipinjam')
            ->count();

        $members = DB::table('anggota')->count();

        $overdueBorrowings = DB::table('pinjam as p')
            ->join('books as b', 'p.book_id', '=', 'b.id')
            ->join('anggota as a', 'p.anggota_id', '=', 'a.id')
            ->where('p.status', 'dipinjam')
            ->whereDate('p.tanggal_kembali', '<', DB::raw('CURRENT_DATE'))
            ->orderBy('p.created_at', 'desc')
            ->limit(5)
            ->select('p.*', 'b.judul as book_judul', 'a.nama as anggota_nama')
            ->get();

        $overdueBorrowings->transform(function ($item) {
            $item->book = (object) ['judul' => $item->book_judul];
            $item->anggota = (object) ['nama' => $item->anggota_nama];
            $item->tanggal_kembali = $item->tanggal_kembali ? \Carbon\Carbon::parse($item->tanggal_kembali) : null;
            return $item;
        });

        $overdue = $overdueBorrowings->count();

        $recentBorrowings = DB::table('pinjam as p')
            ->join('books as b', 'p.book_id', '=', 'b.id')
            ->select('p.*', 'b.judul as book_judul')
            ->orderBy('p.created_at', 'desc')
            ->limit(5)
            ->get();

        $recentActivityRows = $recentBorrowings->map(function ($pinjam) {
            return [
                'type' => 'borrowing',
                'text' => 'Peminjaman baru - <strong>' . e($pinjam->book_judul ?? 'Unknown') . '</strong>',
                'time' => $pinjam->created_at ? \Carbon\Carbon::parse($pinjam->created_at) : null,
            ];
        });

        $recentReturnsRows = DB::table('pengembalian as pe')
            ->join('books as b', 'pe.book_id', '=', 'b.id')
            ->select('pe.*', 'b.judul as book_judul')
            ->orderBy('pe.created_at', 'desc')
            ->limit(5)
            ->get();

        $recentReturns = $recentReturnsRows->map(function ($pengembalian) {
            return [
                'type' => 'return',
                'text' => 'Pengembalian - <strong>' . e($pengembalian->book_judul ?? 'Unknown') . '</strong>',
                'time' => $pengembalian->created_at ? \Carbon\Carbon::parse($pengembalian->created_at) : null,
            ];
        });

        $recentMembers = DB::table('member_profiles')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $memberActivities = $recentMembers->map(function ($memberProfile) {
            return [
                'type' => 'member',
                'text' => 'Anggota baru - <strong>' . e($memberProfile->nama ?? 'Unknown') . '</strong>',
                'time' => $memberProfile->created_at ? \Carbon\Carbon::parse($memberProfile->created_at) : null,
            ];
        })->values();

        $recentActivity = collect()
            ->concat($recentActivityRows->values()->all())
            ->concat($recentReturns->values()->all())
            ->concat($memberActivities->all())
            ->sortByDesc(function (array $activity) {
                return $activity['time'] ? $activity['time']->timestamp : 0;
            })
            ->take(6)
            ->values();

        return view('librarian.dashboard', compact(
            'name',
            'totalBooks',
            'activeLoans',
            'members',
            'overdue',
            'recentActivity',
            'overdueBorrowings'
        ));
    }
}
