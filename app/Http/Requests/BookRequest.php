<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BookRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id_buku' => ['required', 'string', 'max:10'],
            'judul' => ['required', 'string', 'max:255'],
            'pengarang' => ['required', 'string', 'max:255'],
            'penerbit' => ['required', 'string', 'max:255'],
            'thn_terbit' => ['required', 'integer', 'min:1900', 'max:' . (int) date('Y')],
            'kategori' => ['required', 'string', 'max:100'],
            'keterangan' => ['nullable', 'string', 'max:1000'],
            'stock' => ['required', 'integer', 'min:1'],
            'reference_only' => ['nullable', 'boolean'],
        ];
    }
}
