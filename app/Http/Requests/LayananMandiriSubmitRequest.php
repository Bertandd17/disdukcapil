<?php

namespace App\Http\Requests;

class LayananMandiriSubmitRequest extends SecureFormRequest
{
    public function rules(): array
    {
        return [
            'nama' => 'required|string|max:255',
            'nik' => 'required|string|size:16|regex:/^\d{16}$/',
            'no_hp' => 'required|string|max:15|regex:/^[0-9]{10,15}$/',
            'alamat' => 'required|string|max:500',
        ];
    }

    public function messages(): array
    {
        return array_merge(parent::messages(), [
            'nama.required' => 'Nama wajib diisi.',
            'nik.required' => 'NIK wajib diisi.',
            'nik.size' => 'NIK harus 16 digit.',
            'nik.regex' => 'NIK harus berupa 16 digit angka.',
            'no_hp.required' => 'Nomor HP wajib diisi.',
            'no_hp.regex' => 'Nomor HP harus 10–15 digit angka.',
            'alamat.required' => 'Alamat wajib diisi.',
        ]);
    }

    protected function prepareForValidation(): void
    {
        parent::prepareForValidation();

        if (is_string($this->nama)) {
            $this->merge(['nama' => strip_tags($this->nama)]);
        }

        if (is_string($this->alamat)) {
            $this->merge(['alamat' => strip_tags($this->alamat)]);
        }

        if (is_string($this->nik)) {
            $this->merge(['nik' => preg_replace('/\D/', '', $this->nik)]);
        }

        if (is_string($this->no_hp)) {
            $this->merge(['no_hp' => preg_replace('/\D/', '', $this->no_hp)]);
        }
    }
}
