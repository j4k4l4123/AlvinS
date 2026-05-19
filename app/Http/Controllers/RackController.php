<?php

namespace App\Http\Controllers;

use App\Models\Rack;
use Illuminate\Http\Request;

class RackController extends Controller
{
    public function index()
    {
        $racks = Rack::withCount('books')->latest()->paginate(12);

        return view('racks.index', compact('racks'));
    }

    public function create()
    {
        return view('racks.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:racks,name'],
            'code' => ['required', 'string', 'max:100', 'unique:racks,code'],
            'location_note' => ['nullable', 'string', 'max:255'],
            'capacity' => ['required', 'integer', 'min:0'],
        ]);

        Rack::create($validated);

        return redirect()->route('racks.index')->with('success', 'Rak berhasil ditambahkan!');
    }
}
