<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class VigenereController extends Controller
{
    private function normalizeKey(string $key): string
    {
        return $key ?? '';
    }


    private function shiftChar(string $char, int $shift, bool $isEncrypt): string
    {
        // Mod-255 shift for raw bytes: encrypts ALL characters (including special chars).
        $ord = ord($char); // 0..255
        $delta = $isEncrypt ? $shift : -$shift;

        $out = ($ord + $delta) % 255;
        if ($out < 0) {
            $out += 255;
        }

        return chr($out);
    }


    public function index(Request $request)
    {
        return view('vigenere.index', [
            'result' => $request->session()->get('vigenere_result'),
            'mode' => $request->session()->get('vigenere_mode'),
        ]);
    }

    public function encrypt(Request $request)
    {
        $request->validate([
            'plaintext' => ['required', 'string'],
            'key' => ['required', 'string'],
        ]);

        $plaintext = $request->input('plaintext');
        $key = $this->normalizeKey($request->input('key'));

        if ($key === '') {
            return back()->with('vigenere_error', 'Key tidak valid. Minimal harus mengandung huruf A-Z.');
        }

        $result = '';
        $keyLen = strlen($key);
        $keyIndex = 0;

        $bytes = str_split($plaintext);
        foreach ($bytes as $ch) {
            $k = ord($key[$keyIndex % $keyLen]);
            $result .= $this->shiftChar($ch, $k % 255, true);
            $keyIndex++;
        }


        return redirect()->route('vigenere.index')
            ->with('vigenere_mode', 'encrypt')
            ->with('vigenere_result', $result);
    }

    public function decrypt(Request $request)
    {
        $request->validate([
            'ciphertext' => ['required', 'string'],
            'key' => ['required', 'string'],
        ]);

        $ciphertext = $request->input('ciphertext');
        $key = $this->normalizeKey($request->input('key'));

        if ($key === '') {
            return back()->with('vigenere_error', 'Key tidak valid. Minimal harus mengandung huruf A-Z.');
        }

        $result = '';
        $keyLen = strlen($key);
        $keyIndex = 0;

        $bytes = str_split($ciphertext);
        foreach ($bytes as $ch) {
            $k = ord($key[$keyIndex % $keyLen]);
            $result .= $this->shiftChar($ch, $k % 255, false);
            $keyIndex++;
        }


        return redirect()->route('vigenere.index')
            ->with('vigenere_mode', 'decrypt')
            ->with('vigenere_result', $result);
    }
}


