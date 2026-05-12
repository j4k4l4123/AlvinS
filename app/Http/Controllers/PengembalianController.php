<?php

namespace App\Http\Controllers;

use App\Http\Requests\PengembalianRequest;
use App\Models\Pengembalian;
use App\Services\FineService;
use Illuminate\Http\Request;

class PengembalianController extends Controller
{
    protected FineService $fineService;

    public function __construct(FineService $fineService)
    {
        $this->fineService = $fineService;
    }

    public function index(Request $request)
    {
        $query = Pengembalian::with(['anggota', 'book', 'pinjam']);

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

        $pengembalian = $query->latest()->paginate(10)->withQueryString();
        $pinjamAktif = \App\Models\Pinjam::with(['anggota', 'book'])->where('status', 'dipinjam')->get();

        return view('pengembalian.index', compact('pengembalian', 'pinjamAktif'));
    }

    public function create()
    {
        $pinjamList = \App\Models\Pinjam::with(['anggota', 'book'])->where('status', 'dipinjam')->get();
        return view('pengembalian.create', compact('pinjamList'));
    }

    public function store(PengembalianRequest $request)
    {
        try {
            $result = $this->fineService->processReturn(
                $request->pinjam_id,
                $request->tanggal_dikembalikan
            );

            $message = 'Data berhasil disimpan.';
            if (($result['denda'] ?? 0) > 0) {
                $message .= ' Denda: Rp ' . number_format(($result['denda'] ?? 0), 0, ',', '.');
            }

            return redirect()->route('pengembalian.index')->with('success', $message);
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function show($id)
    {
        $pengembalian = Pengembalian::with(['anggota', 'book', 'pinjam'])->findOrFail($id);
        return view('pengembalian.show', compact('pengembalian'));
    }

    public function destroy($id)
    {
        $pengembalian = Pengembalian::findOrFail($id);
        $pinjam = \App\Models\Pinjam::findOrFail($pengembalian->pinjam_id);

        $pinjam->update(['status' => 'dipinjam']);
        $pengembalian->delete();

        return redirect()->route('pengembalian.index')->with('success', 'Data berhasil dihapus dan status dipinjam dikembalikan.');
    }

    /**
     * Show fines summary for a specific member.
     */
    public function fines(Request $request, $anggotaId)
    {
        $anggota = \App\Models\Anggota::findOrFail($anggotaId);
        $totalFines = $this->fineService->getTotalFines($anggotaId);
        $history = Pengembalian::with('book')->where('anggota_id', $anggotaId)->latest()->get();

        return view('pengembalian.fines', compact('anggota', 'totalFines', 'history'));
    }
}

