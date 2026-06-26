<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\VigenereCipherService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ForceResetPasswordController extends Controller
{
    public function show(): View
    {
        // Tampilkan user yang ada untuk dipilih.
        // Dibuat sederhana: hanya id & name/email.
        $users = User::query()
            ->orderBy('name')
            ->select(['id', 'name', 'email'])
            ->get();

        return view('auth.force-reset-password', compact('users'));
    }

    public function update(Request $request, VigenereCipherService $vigenereService): RedirectResponse
    {
        $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        $user = User::query()->findOrFail($request->input('user_id'));

        $encryptedPassword = $vigenereService->encrypt($request->input('password'));

        $user->forceFill([
            'password' => $encryptedPassword,
        ])->save();

        return redirect()
            ->back()
            ->with('success', 'Password berhasil diubah.');
    }
}

