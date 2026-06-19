<?php

namespace App\Http\Controllers;

use App\Models\racks;
use Illuminate\Http\Request;

class RackController extends Controller
{
    public function index()
    {
        $racks = racks::withCount('buku')->latest()->paginate(12);

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

        racks::create([
            'name' => 'Rak ' . $validated['code'],
            'code' => $validated['code'],
            'location_note' => $validated['location_note'] ?? null,
            'capacity' => 0,
        ]);

        return redirect()->route('racks.index')->with('success', 'Rak berhasil ditambahkan!');
    }
}
