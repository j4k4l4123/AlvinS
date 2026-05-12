<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'                  => ['required', 'string', 'max:255'],
            'email'                 => ['required', 'email', 'unique:users,email'],
            'password'              => ['required', 'string', 'min:8', 'confirmed'],
            'password_confirmation' => ['required', 'string'],
            'role'                  => ['required', 'in:librarian,member'],
        ];
    }

    public function messages(): array
    {
        return [
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'role.in'            => 'Pilih peran yang valid.',
        ];
    }
}
