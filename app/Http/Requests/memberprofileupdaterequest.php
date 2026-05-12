<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MemberProfileUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->isMember();
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'alamat' => ['required', 'string', 'max:1000'],
            'no_tlp' => ['required', 'string', 'max:30'],
        ];
    }
}
