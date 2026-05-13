<?php

namespace App\Http\Controllers;

use App\Models\Rack;

class RackController extends Controller
{
    public function index()
    {
        $racks = Rack::withCount('books')->latest()->paginate(12);

        return view('racks.index', compact('racks'));
    }
}
