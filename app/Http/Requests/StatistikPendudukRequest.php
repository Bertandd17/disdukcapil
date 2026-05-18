<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StatistikPendudukRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization di-handle oleh middleware/permission
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'kecamatan_id' => [
                'required',
                'uuid',
                Rule::exists('kecamatan', 'kecamatan_id'),
            ],
            'tahun' => [
                'required',
                'integer',
                'min:2000',
                'max:' . (date('Y') + 1),
            ],
            'total_penduduk' => [
                'required',
                'integer',
                'min:0',
                'max:9999999',
            ],
        ];

        // Untuk update, beberapa field menjadi nullable
        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            $rules['kecamatan_id'][0] = 'sometimes';
            $rules['tahun'][0] = 'sometimes';
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
            'kecamatan_id.required' => 'Kecamatan wajib dipilih.',
            'kecamatan_id.uuid' => 'Format kecamatan tidak valid.',
            'kecamatan_id.exists' => 'Kecamatan yang dipilih tidak ditemukan.',
            'tahun.required' => 'Tahun wajib diisi.',
            'tahun.integer' => 'Tahun harus berupa angka.',
            'tahun.min' => 'Tahun minimal adalah 2000.',
            'tahun.max' => 'Tahun maksimal adalah tahun depan.',
            'total_penduduk.required' => 'Total penduduk wajib diisi.',
            'total_penduduk.integer' => 'Total penduduk harus berupa angka.',
            'total_penduduk.min' => 'Total penduduk tidak boleh negatif.',
            'total_penduduk.max' => 'Total penduduk terlalu besar.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Ensure tahun is integer
        if ($this->has('tahun')) {
            $this->merge([
                'tahun' => (int) $this->tahun,
            ]);
        }

        // Ensure total_penduduk is integer
        if ($this->has('total_penduduk')) {
            $this->merge([
                'total_penduduk' => (int) str_replace(['.', ','], '', $this->total_penduduk),
            ]);
        }
    }
}
