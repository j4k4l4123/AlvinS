<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Models\User;
use App\Services\VigenereCipherService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class LoginPostController extends Controller
{
    public function __invoke(LoginRequest $request, VigenereCipherService $vigenereService): RedirectResponse
    {
        $credentials = $request->validated();
        $remember = $request->boolean('remember');

        $user = User::where('email', $credentials['email'])->first();

        if (! $user) {
            return back()->withErrors([
                'email' => 'Email atau password tidak valid.',
            ])->onlyInput('email');
        }

        if (! $vigenereService->verify($credentials['password'], $user->password)) {
            return back()->withErrors([
                'email' => 'Email atau password tidak valid.',
            ])->onlyInput('email');
        }

        Auth::login($user, $remember);
        $request->session()->regenerate();

        if ($user->isLibrarian()) {
            return redirect()->intended(route('librarian.dashboard'));
        }

        return redirect()->intended(route('member.dashboard'));
    }
}
