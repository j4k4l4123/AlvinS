<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AnggotaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id_anggota'  => ['required', 'string', 'max:20', 'unique:anggota,id_anggota'],
            'nama'         => ['required', 'string', 'max:255'],
            'alamat'       => ['required', 'string'],
            'no_tlp'       => ['required', 'string', 'max:20'],
            'tanggal_daftar' => ['required', 'date'],

            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            // Catatan: validasi unique ini bergantung pada record di tabel users.
            // Pastikan saat delete anggota, record user terkait juga ikut terhapus.

            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }

}
