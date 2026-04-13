<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pengguna;
use Illuminate\Support\Facades\Hash;

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

    public function handleLogin(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6'
        ]);

        if ($request->email === 'user@example.com' && $request->password === 'password') {
            session([
                'user_id' => 1,
                'user_name' => 'Endfield',
                'user_email' => $request->email,
                'is_logged_in' => true
            ]);

            session()->flash('success', 'Login successful!');

            return redirect('/dashboard');
        } else {
            return back()->withErrors([
                'login' => 'Invalid credentials provided.'
            ])->withInput();
        }
    }

    public function dashboard()
    {
        if (!session('is_logged_in')) {
            return redirect('/login')->with('error', 'Please login first.');
        }

        $userName = session('user_name', 'Guest');

        return view('dashboard', ['name' => $userName]);
    }

    public function logout()
    {
        session()->flush();
        return redirect('/login')->with('success', 'You have been logged out successfully.');
    }

    // ========== CRUD METHODS FOR PENGGUNA ==========

    // READ - List all pengguna
    public function database()
    {
        $pengguna = Pengguna::all();
        return view('database-page', compact('pengguna'));
    }

    // CREATE - Show create form
    public function create()
    {
        return view('pengguna.create');
    }

    // STORE - Save new pengguna
    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'email' => 'required|email|unique:pengguna,email',
            'password' => 'required|min:6',
            'telepon' => 'nullable|string|max:20',
            'alamat' => 'nullable|string',
        ]);

        Pengguna::create([
            'nama' => $request->nama,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'telepon' => $request->telepon,
            'alamat' => $request->alamat,
        ]);

        return redirect('/database')->with('success', 'Pengguna created successfully!');
    }

    // EDIT - Show edit form
    public function edit($id)
    {
        $pengguna = Pengguna::findOrFail($id);
        return view('pengguna.edit', compact('pengguna'));
    }

    // UPDATE - Update pengguna
    public function update(Request $request, $id)
    {
        $pengguna = Pengguna::findOrFail($id);

        $request->validate([
            'nama' => 'required|string|max:255',
            'email' => 'required|email|unique:pengguna,email,' . $id,
            'telepon' => 'nullable|string|max:20',
            'alamat' => 'nullable|string',
        ]);

        $data = [
            'nama' => $request->nama,
            'email' => $request->email,
            'telepon' => $request->telepon,
            'alamat' => $request->alamat,
        ];

        // Only update password if provided
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $pengguna->update($data);

        return redirect('/database')->with('success', 'Pengguna updated successfully!');
    }

    // DELETE - Delete pengguna
    public function destroy($id)
    {
        $pengguna = Pengguna::findOrFail($id);
        $pengguna->delete();

        return redirect('/database')->with('success', 'Pengguna deleted successfully!');
    }
}