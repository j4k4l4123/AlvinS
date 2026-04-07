<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

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
        // Validate the login credentials
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6'
        ]);

        // In a real application, you would verify credentials against database
        // For demo purposes, using simple check
        if ($request->email === 'user@example.com' && $request->password === 'password') {
            // Store user information in session
            session([
                'user_id' => 1,
                'user_name' => 'Endfield',
                'user_email' => $request->email,
                'is_logged_in' => true
            ]);

            // Flash success message
            session()->flash('success', 'Login successful!');

            return redirect('/dashboard');
        } else {
            // Flash error message and redirect back
            return back()->withErrors([
                'login' => 'Invalid credentials provided.'
            ])->withInput();
        }
    }

    public function dashboard()
    {
        // Check if user is logged in
        if (!session('is_logged_in')) {
            return redirect('/login')->with('error', 'Please login first.');
        }

        // Get user name from session
        $userName = session('user_name', 'Guest');

        return view('dashboard', ['name' => $userName]);
    }

    public function logout()
    {
        // Clear all session data
        session()->flush();

        // Or clear specific session keys
        // session()->forget(['user_id', 'user_name', 'user_email', 'is_logged_in']);

        return redirect('/login')->with('success', 'You have been logged out successfully.');
    }
}
