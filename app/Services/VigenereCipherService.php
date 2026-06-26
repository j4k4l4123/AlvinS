<?php

namespace App\Services;

use Illuminate\Support\Facades\Config;

/**
 * Service untuk enkripsi dan verifikasi password menggunakan algoritma Vigenère Cipher.
 *
 * Logika enkripsi dipisahkan ke service ini agar:
 * - Mudah digunakan ulang di controller manapun (register, login, reset password, dll.)
 * - Key enkripsi dikelola terpusat melalui config/vigenere.php
 * - Mudah di-test secara independen
 */
class VigenereCipherService
{
    /**
     * Ambil kunci Vigenère dari konfigurasi terpusat.
     */
    public function getKey(): string
    {
        return Config::get('vigenere.key', 'KEY');
    }

    /**
     * Enkripsi plaintext menggunakan algoritma Vigenère Cipher.
     *
     * Proses:
     * 1. Setiap byte plaintext di-shift berdasarkan nilai ASCII dari karakter key yang bersesuaian.
     * 2. Hasil enkripsi (binary) di-encode ke Base64 agar aman disimpan di database sebagai string.
     *
     * @param  string  $plaintext  Password dalam bentuk plaintext
     * @param  string|null  $key  Kunci enkripsi (default dari config)
     * @return string Ciphertext dalam format Base64
     *
     * @throws \InvalidArgumentException Jika plaintext atau key kosong
     */
    public function encrypt(string $plaintext, ?string $key = null): string
    {
        $key = $key ?? $this->getKey();

        if ($plaintext === '') {
            throw new \InvalidArgumentException('Plaintext tidak boleh kosong.');
        }

        if ($key === '') {
            throw new \InvalidArgumentException('Kunci Vigenère tidak boleh kosong.');
        }

        $result = '';
        $keyLen = strlen($key);
        $keyIndex = 0;

        $bytes = str_split($plaintext);
        foreach ($bytes as $ch) {
            $k = ord($key[$keyIndex % $keyLen]);
            $shifted = (ord($ch) + $k) % 256;
            $result .= chr($shifted);
            $keyIndex++;
        }

        return base64_encode($result);
    }

    /**
     * Verifikasi apakah plaintext cocok dengan ciphertext yang tersimpan.
     *
     * Proses:
     * 1. Enkripsi input plaintext menggunakan key yang sama.
     * 2. Bandingkan hasil enkripsi dengan ciphertext di database.
     * 3. Jika cocok, login berhasil.
     *
     * @param  string  $plaintext  Password input dari user (plaintext)
     * @param  string  $ciphertext  Password tersimpan di database (ciphertext Base64)
     * @param  string|null  $key  Kunci enkripsi (default dari config)
     * @return bool True jika cocok
     */
    public function verify(string $plaintext, string $ciphertext, ?string $key = null): bool
    {
        $encrypted = $this->encrypt($plaintext, $key);

        return hash_equals($encrypted, $ciphertext);
    }

    /**
     * Dekripsi ciphertext menggunakan algoritma Vigenère Cipher.
     *
     * @param  string  $ciphertext  Ciphertext dalam format Base64
     * @param  string|null  $key  Kunci enkripsi (default dari config)
     * @return string Plaintext hasil dekripsi
     *
     * @throws \InvalidArgumentException Jika ciphertext atau key kosong
     */
    public function decrypt(string $ciphertext, ?string $key = null): string
    {
        $key = $key ?? $this->getKey();

        if ($ciphertext === '') {
            throw new \InvalidArgumentException('Ciphertext tidak boleh kosong.');
        }

        if ($key === '') {
            throw new \InvalidArgumentException('Kunci Vigenère tidak boleh kosong.');
        }

        $decoded = base64_decode($ciphertext, true);
        if ($decoded === false) {
            throw new \InvalidArgumentException('Ciphertext bukan format Base64 yang valid.');
        }

        $result = '';
        $keyLen = strlen($key);
        $keyIndex = 0;

        $bytes = str_split($decoded);
        foreach ($bytes as $ch) {
            $k = ord($key[$keyIndex % $keyLen]);
            $shifted = (ord($ch) - $k) % 256;
            if ($shifted < 0) {
                $shifted += 256;
            }
            $result .= chr($shifted);
            $keyIndex++;
        }

        return $result;
    }
}
