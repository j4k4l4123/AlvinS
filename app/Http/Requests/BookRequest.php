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
            'barcode' => ['nullable', 'string', 'max:100'],
            'copy_code_prefix' => ['nullable', 'string', 'max:50'],
            'isbn' => ['nullable', 'string', 'max:100'],
            'judul' => ['required', 'string', 'max:255'],
            'pengarang' => ['required', 'string', 'max:255'],
            'penerbit' => ['required', 'string', 'max:255'],
            'thn_terbit' => ['required', 'integer', 'min:1900', 'max:' . (int) date('Y')],
            'rack_id' => ['nullable', 'exists:racks,id'],
            'kategori' => ['required', 'string', 'max:100'],
            'language' => ['nullable', 'string', 'max:100'],
            'subject' => ['nullable', 'string', 'max:255'],
            'number_of_pages' => ['nullable', 'integer', 'min:1'],
            'format' => ['nullable', 'string', 'max:100'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'daily_late_fee' => ['nullable', 'numeric', 'min:0'],
            'max_loan_days' => ['nullable', 'integer', 'min:1', 'max:60'],
            'max_renewals' => ['nullable', 'integer', 'min:0', 'max:10'],
            'copy_status' => ['nullable', 'in:available,borrowed,reserved,lost,damaged,maintenance'],
            'copy_condition' => ['nullable', 'in:good,fair,damaged,lost'],
            'keterangan' => ['nullable', 'string', 'max:1000'],
            'stock' => ['required', 'integer', 'min:1'],
            'reference_only' => ['nullable', 'boolean'],
        ];
    }
}
