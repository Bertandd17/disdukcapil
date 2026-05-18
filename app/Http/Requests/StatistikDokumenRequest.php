<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StatistikDokumenRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'tahun' => [
                'required',
                'integer',
                'min:2000',
                'max:' . (date('Y') + 1),
            ],
            'bulan' => [
                'required',
                'integer',
                'min:1',
                'max:12',
            ],
            'jumlah_kk' => [
                'nullable',
                'integer',
                'min:0',
                'max:9999999',
            ],
            'jumlah_akte_lahir' => [
                'nullable',
                'integer',
                'min:0',
                'max:9999999',
            ],
            'jumlah_akte_kematian' => [
                'nullable',
                'integer',
                'min:0',
                'max:9999999',
            ],
            'jumlah_ktp' => [
                'nullable',
                'integer',
                'min:0',
                'max:9999999',
            ],
            'jumlah_kia' => [
                'nullable',
                'integer',
                'min:0',
                'max:9999999',
            ],
        ];

        // Untuk update, tahun dan bulan tidak berubah
        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            $rules['tahun'][0] = 'sometimes';
            $rules['bulan'][0] = 'sometimes';
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'tahun.required' => 'Tahun wajib diisi.',
            'tahun.integer' => 'Tahun harus berupa angka.',
            'tahun.min' => 'Tahun minimal adalah 2000.',
            'tahun.max' => 'Tahun maksimal adalah tahun depan.',
            'bulan.required' => 'Bulan wajib dipilih.',
            'bulan.integer' => 'Bulan harus berupa angka.',
            'bulan.min' => 'Bulan minimal adalah 1.',
            'bulan.max' => 'Bulan maksimal adalah 12.',
            'jumlah_kk.integer' => 'Jumlah KK harus berupa angka.',
            'jumlah_akte_lahir.integer' => 'Jumlah Akte Lahir harus berupa angka.',
            'jumlah_akte_kematian.integer' => 'Jumlah Akte Kematian harus berupa angka.',
            'jumlah_ktp.integer' => 'Jumlah KTP harus berupa angka.',
            'jumlah_kia.integer' => 'Jumlah KIA harus berupa angka.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $inputs = [];

        if ($this->has('tahun')) {
            $inputs['tahun'] = (int) $this->tahun;
        }

        if ($this->has('bulan')) {
            $inputs['bulan'] = (int) $this->bulan;
        }

        // Set default 0 untuk field yang nullable
        $numberFields = ['jumlah_kk', 'jumlah_akte_lahir', 'jumlah_akte_kematian', 'jumlah_ktp', 'jumlah_kia'];
        foreach ($numberFields as $field) {
            if ($this->has($field)) {
                $inputs[$field] = (int) str_replace(['.', ','], '', $this->$field);
            }
        }

        if (!empty($inputs)) {
            $this->merge($inputs);
        }
    }
}
