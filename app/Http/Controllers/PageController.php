<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PageController extends Controller
{
    public function login()
    {
        return view('login');
    }

    public function handleLogin(Request $request)
    {
        return redirect('/dashboard');
    }

    public function dashboard()
    {
        return view('dashboard', ['name' => 'Endfield']);
    }
}
