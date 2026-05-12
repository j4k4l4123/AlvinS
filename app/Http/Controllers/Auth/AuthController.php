<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Role;

class AuthController extends Controller
{
    public function login(LoginRequest $request)
    {
        $credentials = $request->validated();

        if (!Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors(['email' => 'Invalid credentials.'])->withInput();
        }

        $request->session()->regenerate();
        $user = Auth::user();
        $role = $user->roles()->first();

        if ($role && $role->name === 'librarian') {
            return redirect()->intended(route('librarian.dashboard'));
        }

        return redirect()->intended(route('member.dashboard'));
    }

    public function register(RegisterRequest $request)
    {
        $validated = $request->validated();

        $user = \App\Models\User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => bcrypt($validated['password']),
            ]);

        $role = Role::where('name', $validated['role'])->first();
        if ($role) {
            $user->roles()->attach($role->id);
        }

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    public function logout(Request $request)
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}

