<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PinjamRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'anggota_id'      => ['required', 'exists:anggota,id'],
            'book_id'         => ['required', 'exists:books,id'],
            'tanggal_pinjam'  => ['required', 'date'],
            'tanggal_kembali' => ['required', 'date', 'after_or_equal:tanggal_pinjam'],
        ];
    }

    public function messages(): array
    {
        return [
            'tanggal_kembali.after_or_equal' => 'Tanggal kembali harus setelah tanggal pinjam.',
        ];
    }
}
