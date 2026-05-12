<?php

namespace App\Http\Controllers;

use App\Models\Anggota;
use App\Models\LibraryCard;
use App\Models\User;
use App\Services\LibraryCardService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class LibraryCardController extends Controller
{
    public function index()
    {
        $cards = LibraryCard::with('user', 'anggota')->latest()->paginate(10);

        return view('library-cards.index', compact('cards'));
    }

    public function create()
    {
        $members = Anggota::orderBy('nama')->get();

        return view('library-cards.create', compact('members'));
    }

    public function store(Request $request, LibraryCardService $libraryCardService)
    {
        $validated = $request->validate([
            'anggota_id' => ['required', 'exists:anggota,id'],
            'expiry_date' => ['required', 'date', 'after:today'],
        ]);

        $anggota = Anggota::with('user')->findOrFail($validated['anggota_id']);
        $user = $anggota->user;

        if (! $user && $anggota->id_anggota) {
            $user = User::whereHas('memberProfile', function ($query) use ($anggota) {
                $query->where('id_anggota', $anggota->id_anggota);
            })->first();
        }

        if (! $user) {
            return back()->withErrors([
                'anggota_id' => 'Anggota ini belum terhubung ke akun user.',
            ])->withInput();
        }

        $existingCard = $anggota->libraryCard;

        $anggota->libraryCard()->updateOrCreate(
            ['anggota_id' => $anggota->id],
            [
                'user_id' => $user->id,
                'card_number' => $existingCard?->card_number ?? $libraryCardService->generateSequentialCardNumber(),
                'status' => 'active',
                'issued_date' => $existingCard?->issued_date ?? Carbon::today(),
                'expiry_date' => $validated['expiry_date'],
            ]
        );

        return redirect()->route('library-cards.index')->with('success', 'Kartu perpustakaan berhasil dibuat!');
    }

    public function show($id = null)
    {
        if ($id === null) {
            $user = auth()->user();
            $card = LibraryCard::with('user', 'anggota')
                ->where('user_id', $user?->id)
                ->latest()
                ->firstOrFail();
        } else {
            $card = LibraryCard::with('user', 'anggota')->findOrFail($id);
        }

        return view('library-cards.show', compact('card'));
    }

    public function toggleStatus($id)
    {
        $card = LibraryCard::findOrFail($id);
        $card->status = $card->status === 'active' ? 'cancelled' : 'active';
        $card->save();

        return redirect()->route('library-cards.index')->with('success', 'Status kartu diperbarui!');
    }
}
