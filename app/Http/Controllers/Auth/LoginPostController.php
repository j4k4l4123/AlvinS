<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class LoginPostController extends Controller
{
    public function __invoke(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:6'],
        ]);

        $remember = $request->boolean('remember');

        // Laravel handles password hashing verification securely.
        $credentials = $request->only('email', 'password');

        if (!Auth::attempt($credentials, $remember)) {
            return back()->withErrors(['email' => 'Invalid credentials.'])->withInput();
        }

        $user = $request->user();
        $role = $user->roles()->first();

        // Role-based redirect after login.
        if ($role && $role->name === 'librarian') {
            return redirect()->route('librarian.dashboard');
        }

        return redirect()->route('member.dashboard');
    }
}

