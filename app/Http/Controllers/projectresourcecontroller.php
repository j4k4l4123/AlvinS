<?php

namespace App\Http\Controllers;

use App\Models\ProjectResource;
use Illuminate\Http\Request;

class ProjectResourceController extends Controller
{
    public function index(Request $request)
    {
        $query = ProjectResource::query();

        if ($request->filled('search')) {
            $keyword = strtolower($request->search);
            $query->where(function ($q) use ($keyword) {
                $q->whereRaw('LOWER(task_name) LIKE ?', ["%{$keyword}%"])
                    ->orWhereRaw('LOWER(task_code) LIKE ?', ["%{$keyword}%"])
                    ->orWhereRaw('LOWER(resource_name) LIKE ?', ["%{$keyword}%"])
                    ->orWhereRaw('LOWER(resource_category) LIKE ?', ["%{$keyword}%"]);
            });
        }

        $projectResources = $query->orderBy('task_code')->orderBy('resource_name')->get();
        $totalBudget = $projectResources->sum('total_price');

        return view('project-resources.index', compact('projectResources', 'totalBudget'));
    }

    public function create()
    {
        return view('project-resources.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'task_name' => 'required|string|max:255',
            'task_code' => 'nullable|string|max:50',
            'resource_name' => 'required|string|max:255',
            'resource_category' => 'required|string|max:100',
            'quantity' => 'required|numeric|min:0',
            'unit' => 'required|string|max:50',
            'unit_price' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $validated['total_price'] = $validated['quantity'] * $validated['unit_price'];

        ProjectResource::create($validated);

        return redirect()->route('project-resources.index')->with('success', 'Resource proyek berhasil ditambahkan!');
    }
}
