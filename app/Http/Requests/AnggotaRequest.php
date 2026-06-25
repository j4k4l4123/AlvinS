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
        $id = $this->route('id');

        if ($this->isMethod('put') || $this->isMethod('patch')) {
            return [
                'id_anggota'  => ['required', 'string', 'max:20', 'unique:anggota,id_anggota,' . $id],
                'nama'         => ['required', 'string', 'max:255'],
                'alamat'       => ['required', 'string'],
                'no_tlp'       => ['required', 'string', 'max:20'],
                'tanggal_daftar' => ['required', 'date'],
            ];
        }

        return [
            'id_anggota'  => ['required', 'string', 'max:20', 'unique:anggota,id_anggota'],
            'nama'         => ['required', 'string', 'max:255'],
            'alamat'       => ['required', 'string'],
            'no_tlp'       => ['required', 'string', 'max:20'],
            'tanggal_daftar' => ['required', 'date'],

            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }
}
