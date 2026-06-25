<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RackController extends Controller
{
    public function index()
    {
        $racks = \Illuminate\Support\Facades\DB::table('racks')
            ->select('racks.*')
            ->selectRaw('(SELECT COUNT(*) FROM books WHERE books.rack_id = racks.id) as books_count')
            ->selectRaw('(SELECT COALESCE(SUM(books.stock),0) FROM books WHERE books.rack_id = racks.id) as total_stock')
            ->orderBy('racks.created_at', 'desc')
            ->paginate(12);

        return view('racks.index', compact('racks'));
    }

    public function create()
    {
        return view('racks.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:100', 'unique:racks,code'],
            'location_note' => ['nullable', 'string', 'max:255'],
        ]);

        DB::table('racks')->insert([
            'name' => 'Rak ' . $validated['code'],
            'code' => $validated['code'],
            'location_note' => $validated['location_note'] ?? null,
            'capacity' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('racks.index')->with('success', 'Rak berhasil ditambahkan!');
    }
}
