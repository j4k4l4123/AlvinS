<?php

namespace App\Http\Controllers;

use App\Models\Fine;
use App\Services\FineService;

class FineController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $anggota = $user?->anggota;

        $fines = Fine::with(['borrowing.book', 'returnRecord'])
            ->when($anggota, fn ($query) => $query->where('anggota_id', $anggota->id))
            ->latest()
            ->paginate(10);

        return view('member.fines', compact('fines'));
    }

    public function pay(Fine $fine, FineService $fineService)
    {
        $user = auth()->user();
        abort_if($fine->anggota?->user_id !== $user?->id, 403);

        $fineService->markFineAsPaid($fine->borrowing);

        return back()->with('success', 'Denda berhasil ditandai lunas.');
    }
}
