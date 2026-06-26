<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use App\Services\VigenereCipherService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Role;

class AuthController extends Controller
{
    public function login(LoginRequest $request, VigenereCipherService $vigenereService)
    {
        $credentials = $request->validated();

        // Cari user berdasarkan email
        $user = User::where('email', $credentials['email'])->first();

        if (! $user) {
            return back()->withErrors(['email' => 'Invalid credentials.'])->withInput();
        }

        // Verifikasi password: enkripsi input lalu bandingkan dengan ciphertext di database
        if (! $vigenereService->verify($credentials['password'], $user->password)) {
            return back()->withErrors(['email' => 'Invalid credentials.'])->withInput();
        }

        // Login manual setelah verifikasi Vigenère berhasil
        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        $role = $user->roles()->first();

        if ($role && $role->name === 'librarian') {
            return redirect()->intended(route('librarian.dashboard'));
        }

        return redirect()->intended(route('member.dashboard'));
    }

    public function register(RegisterRequest $request, VigenereCipherService $vigenereService)
    {
        $validated = $request->validated();

        // Enkripsi password menggunakan Vigenère Cipher sebelum disimpan
        $encryptedPassword = $vigenereService->encrypt($validated['password']);

        $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => $encryptedPassword,
            ]);

        $role = Role::findByName($validated['role']);
        if ($role) {
            \Illuminate\Support\Facades\DB::table('role_user')->insert([
                'user_id' => $user->id,
                'role_id' => $role->id,
            ]);
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
