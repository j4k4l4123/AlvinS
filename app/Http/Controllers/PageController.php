<?php

namespace App\Http\Controllers;

use App\Models\Anggota;
use App\Models\Book;
use App\Models\MemberProfile;
use App\Models\Pengembalian;
use App\Models\Pinjam;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

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
        $role = $user?->roles()->first();

        if ($role && $role->name === 'librarian') {
            $name = $role->name;
            $totalBooks = Book::count();
            $activeLoans = Pinjam::where('status', 'dipinjam')->count();
            $members = Anggota::count();
            $overdue = Pinjam::overdue()->count();
            $recentMembers = MemberProfile::latest()->take(5)->get();
            $overdueBorrowings = Pinjam::overdue()->with('book', 'anggota')->latest()->take(5)->get();

            $recentActivity = Pinjam::with(['book', 'anggota'])
                ->latest()
                ->take(5)
                ->get()
                ->map(function ($pinjam) {
                    return [
                        'type' => 'borrowing',
                        'text' => 'Peminjaman baru - <strong>' . e($pinjam->book?->judul ?? 'Unknown') . '</strong>',
                        'time' => $pinjam->created_at,
                    ];
                });

            $recentReturns = Pengembalian::with(['book', 'anggota'])
                ->latest()
                ->take(5)
                ->get()
                ->map(function ($pengembalian) {
                    return [
                        'type' => 'return',
                        'text' => 'Pengembalian - <strong>' . e($pengembalian->book?->judul ?? 'Unknown') . '</strong>',
                        'time' => $pengembalian->created_at,
                    ];
                });

            $memberActivities = $recentMembers->map(function ($memberProfile) {
                return [
                    'type' => 'member',
                    'text' => 'Anggota baru - <strong>' . e($memberProfile->nama) . '</strong>',
                    'time' => $memberProfile->created_at,
                ];
            })->values();

            $recentActivity = collect()
                ->concat($recentActivity->values()->all())
                ->concat($recentReturns->values()->all())
                ->concat($memberActivities->all())
                ->sortByDesc(function (array $activity) {
                    return optional($activity['time'])->timestamp ?? 0;
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

        return redirect()->route('member.dashboard');
    }
}
