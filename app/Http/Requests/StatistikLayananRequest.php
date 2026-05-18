<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StatistikLayananRequest extends FormRequest
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
            'antrian_menunggu' => [
                'nullable',
                'integer',
                'min:0',
                'max:9999999',
            ],
            'antrian_diproses' => [
                'nullable',
                'integer',
                'min:0',
                'max:9999999',
            ],
            'antrian_selesai' => [
                'nullable',
                'integer',
                'min:0',
                'max:9999999',
            ],
            'antrian_ditolak' => [
                'nullable',
                'integer',
                'min:0',
                'max:9999999',
            ],
            'waktu_avg_penanganan_menit' => [
                'nullable',
                'integer',
                'min:0',
                'max:99999',
            ],
            'persentase_kepuasan' => [
                'nullable',
                'numeric',
                'min:0',
                'max:100',
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
            'antrian_menunggu.integer' => 'Jumlah antrian menunggu harus berupa angka.',
            'antrian_diproses.integer' => 'Jumlah antrian diproses harus berupa angka.',
            'antrian_selesai.integer' => 'Jumlah antrian selesai harus berupa angka.',
            'antrian_ditolak.integer' => 'Jumlah antrian ditolak harus berupa angka.',
            'waktu_avg_penanganan_menit.integer' => 'Waktu rata-rata harus berupa angka.',
            'persentase_kepuasan.numeric' => 'Persentase kepuasan harus berupa angka.',
            'persentase_kepuasan.min' => 'Persentase kepuasan minimal adalah 0.',
            'persentase_kepuasan.max' => 'Persentase kepuasan maksimal adalah 100.',
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
        $numberFields = [
            'antrian_menunggu',
            'antrian_diproses',
            'antrian_selesai',
            'antrian_ditolak',
            'waktu_avg_penanganan_menit',
        ];
        foreach ($numberFields as $field) {
            if ($this->has($field)) {
                $inputs[$field] = (int) str_replace(['.', ','], '', $this->$field);
            }
        }

        // Parse persentase_kepuasan
        if ($this->has('persentase_kepuasan')) {
            $inputs['persentase_kepuasan'] = (float) str_replace([',', '%'], ['.', ''], $this->persentase_kepuasan);
        }

        if (!empty($inputs)) {
            $this->merge($inputs);
        }
    }
}
