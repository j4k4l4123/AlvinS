<?php

namespace App\Http\Controllers;

use App\Models\Anggota;
use App\Models\LibraryCard\LibraryCard;
use App\Models\User;
use App\Services\LibraryCardService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LibraryCardController extends Controller
{
    public function index()
    {
        // Expire cards whose expiry_date is in the past.
        DB::table('library_cards')
            ->where('status', 'active')
            ->whereDate('expiry_date', '<', now()->toDateString())
            ->update(['status' => 'expired']);

        // Fetch cards excluding cancelled, and attach user/anggota pseudo-relations for Blade.
        $cards = DB::table('library_cards')
            ->leftJoin('users', 'library_cards.user_id', '=', 'users.id')
            ->leftJoin('anggota', 'library_cards.anggota_id', '=', 'anggota.id')
            ->select([
                'library_cards.*',
                'users.name as user_name',
                'anggota.nama as anggota_nama',
                'anggota.id_anggota as anggota_id_anggota',
            ])
            ->where('library_cards.status', '!=', 'cancelled')
            ->orderByDesc('library_cards.id')
            ->paginate(10);

        // Blade expects: $card->anggota?->nama and $card->user?->name and $card->anggota?->id_anggota
        foreach ($cards as $card) {
            $card->anggota = (object) [
                'nama' => $card->anggota_nama,
                'id_anggota' => $card->anggota_id_anggota ?? null,
            ];
            $card->user = (object) ['name' => $card->user_name];

            if ($card->issued_date) {
                $card->issued_date = Carbon::parse($card->issued_date);
            }
            if ($card->expiry_date) {
                $card->expiry_date = Carbon::parse($card->expiry_date);
            }
        }

        return view('library-cards.index', compact('cards'));
    }

    public function create()
    {
        $members = DB::table('anggota')
            ->select(['id', 'nama', 'id_anggota'])
            ->orderBy('nama')
            ->get();

        return view('library-cards.create', compact('members'));
    }

    public function store(Request $request, LibraryCardService $libraryCardService)
    {
        $validated = $request->validate([
            'anggota_id' => ['required', 'exists:anggota,id'],
            'expiry_date' => ['required', 'date', 'after:today'],
        ]);

        $anggotaRow = DB::table('anggota')->where('id', $validated['anggota_id'])->first();
        if (! $anggotaRow) {
            abort(404);
        }

        $userId = null;
        if (! empty($anggotaRow->user_id)) {
            $userId = $anggotaRow->user_id;
        } elseif (! empty($anggotaRow->id_anggota)) {
            $userId = DB::table('member_profiles')
                ->where('id_anggota', $anggotaRow->id_anggota)
                ->value('user_id');
        }

        if (! $userId) {
            return back()->withErrors([
                'anggota_id' => 'Anggota ini belum terhubung ke akun user.',
            ])->withInput();
        }

        $userExists = DB::table('users')->where('id', $userId)->exists();
        if (! $userExists) {
            return back()->withErrors([
                'anggota_id' => 'Akun user untuk anggota ini tidak ditemukan.',
            ])->withInput();
        }

        $existingCard = DB::table('library_cards')
            ->where('anggota_id', $anggotaRow->id)
            ->first();

        DB::table('library_cards')->updateOrInsert(
            ['anggota_id' => $anggotaRow->id],
            [
                'user_id' => $userId,
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
            $card = DB::table('library_cards')
                ->where('user_id', $user?->id)
                ->orderByDesc('id')
                ->first();

            if (! $card) {
                abort(404);
            }
        } else {
            $card = DB::table('library_cards')
                ->where('id', $id)
                ->first();

            if (! $card) {
                abort(404);
            }
        }

        $anggota = DB::table('anggota')->where('id', $card->anggota_id)->first();
        $user = DB::table('users')->where('id', $card->user_id)->first();

        $card->anggota = $anggota ? (object) [
            'nama' => $anggota->nama,
            'id_anggota' => $anggota->id_anggota,
        ] : null;
        $card->user = $user ? (object) [
            'name' => $user->name,
        ] : null;

        if ($card->issued_date) {
            $card->issued_date = Carbon::parse($card->issued_date);
        }
        if ($card->expiry_date) {
            $card->expiry_date = Carbon::parse($card->expiry_date);
        }

        return view('library-cards.show', compact('card'));
    }

    public function toggleStatus($id)
    {
        $card = LibraryCard::find($id);
        if (! $card) {
            abort(404);
        }

        $newStatus = ($card->status === 'active') ? 'cancelled' : 'active';

        DB::table('library_cards')
            ->where('id', $id)
            ->update(['status' => $newStatus]);

        return redirect()->route('library-cards.index')->with('success', 'Status kartu diperbarui!');
    }
}
