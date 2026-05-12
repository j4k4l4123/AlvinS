<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PengembalianRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'pinjam_id'         => ['required', 'exists:pinjam,id'],
            'tanggal_dikembalikan' => ['required', 'date'],
        ];
    }
}
