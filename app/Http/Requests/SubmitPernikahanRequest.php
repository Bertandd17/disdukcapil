<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Carbon;

class SubmitPernikahanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nama_pemohon' => 'required|string|max:100',
            'nik_pemohon' => 'nullable|string|digits:16',
            'alamat_pemohon' => 'nullable|string|max:500',

            'nama_mempelai_pria' => 'required|string|max:100',
            'nik_mempelai_pria' => 'required|string|digits:16',
            'tempat_lahir_mempelai_pria' => 'nullable|string|max:100',
            'tanggal_lahir_mempelai_pria' => 'nullable|date|before:today',
            'agama_mempelai_pria' => 'nullable|string|max:50',
            'alamat_mempelai_pria' => 'nullable|string|max:500',
            'pekerjaan_mempelai_pria' => 'nullable|string|max:100',

            'nama_mempelai_wanita' => 'required|string|max:100',
            'nik_mempelai_wanita' => 'required|string|digits:16',
            'tempat_lahir_mempelai_wanita' => 'nullable|string|max:100',
            'tanggal_lahir_mempelai_wanita' => 'nullable|date|before:today',
            'agama_mempelai_wanita' => 'nullable|string|max:50',
            'alamat_mempelai_wanita' => 'nullable|string|max:500',
            'pekerjaan_mempelai_wanita' => 'nullable|string|max:100',

            'nama_ayah_pria' => 'nullable|string|max:100',
            'nik_ayah_pria' => 'nullable|string|digits:16',
            'tempat_lahir_ayah_pria' => 'nullable|string|max:100',
            'tanggal_lahir_ayah_pria' => 'nullable|date|before:today',
            'alamat_ayah_pria' => 'nullable|string|max:500',

            'nama_ibu_pria' => 'nullable|string|max:100',
            'nik_ibu_pria' => 'nullable|string|digits:16',
            'tempat_lahir_ibu_pria' => 'nullable|string|max:100',
            'tanggal_lahir_ibu_pria' => 'nullable|date|before:today',
            'alamat_ibu_pria' => 'nullable|string|max:500',

            'nama_saksi_1' => 'required|string|max:100',
            'nik_saksi_1' => 'required|string|digits:16',
            'tempat_lahir_saksi_1' => 'nullable|string|max:100',
            'tanggal_lahir_saksi_1' => 'nullable|date|before:today',
            'alamat_saksi_1' => 'nullable|string|max:500',

            'nama_saksi_2' => 'required|string|max:100',
            'nik_saksi_2' => 'required|string|digits:16',
            'tempat_lahir_saksi_2' => 'nullable|string|max:100',
            'tanggal_lahir_saksi_2' => 'nullable|date|before:today',
            'alamat_saksi_2' => 'nullable|string|max:500',

            'keagamaan_id' => 'nullable|string|exists:organisasi,organisasi_id',
            'nama_gereja' => 'required|string|max:100',

            'tanggal_perkawinan' => [
                'required',
                'date',
                'after:today',
                function (string $attribute, mixed $value, Closure $fail) {
                    if (!self::validateMin7Days($value)) {
                        $fail('Tanggal perkawinan harus minimal 7 hari dari hari ini.');
                    }
                },
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'required' => ':attribute wajib diisi.',
            'string' => ':attribute harus berupa teks.',
            'max' => ':attribute tidak boleh lebih dari :max karakter.',
            'digits' => ':attribute harus 16 digit.',
            'date' => ':attribute harus berupa tanggal yang valid.',
            'before:today' => ':attribute harus sebelum hari ini.',
            'after:today' => ':attribute harus setelah hari ini.',
            'date_format:H:i' => ':attribute harus berupa format jam yang valid (HH:MM).',
            'exists' => ':attribute tidak ditemukan.',
        ];
    }

    public function attributes(): array
    {
        return [
            'nama_pemohon' => 'Nama pemohon',
            'nik_pemohon' => 'NIK pemohon',
            'alamat_pemohon' => 'Alamat pemohon',
            'nama_mempelai_pria' => 'Nama mempelai pria',
            'nik_mempelai_pria' => 'NIK mempelai pria',
            'tempat_lahir_mempelai_pria' => 'Tempat lahir mempelai pria',
            'tanggal_lahir_mempelai_pria' => 'Tanggal lahir mempelai pria',
            'agama_mempelai_pria' => 'Agama mempelai pria',
            'alamat_mempelai_pria' => 'Alamat mempelai pria',
            'pekerjaan_mempelai_pria' => 'Pekerjaan mempelai pria',
            'nama_mempelai_wanita' => 'Nama mempelai wanita',
            'nik_mempelai_wanita' => 'NIK mempelai wanita',
            'tempat_lahir_mempelai_wanita' => 'Tempat lahir mempelai wanita',
            'tanggal_lahir_mempelai_wanita' => 'Tanggal lahir mempelai wanita',
            'agama_mempelai_wanita' => 'Agama mempelai wanita',
            'alamat_mempelai_wanita' => 'Alamat mempelai wanita',
            'pekerjaan_mempelai_wanita' => 'Pekerjaan mempelai wanita',
            'nama_ayah_pria' => 'Nama ayah mempelai pria',
            'nik_ayah_pria' => 'NIK ayah mempelai pria',
            'tempat_lahir_ayah_pria' => 'Tempat lahir ayah mempelai pria',
            'tanggal_lahir_ayah_pria' => 'Tanggal lahir ayah mempelai pria',
            'alamat_ayah_pria' => 'Alamat ayah mempelai pria',
            'nama_ibu_pria' => 'Nama ibu mempelai pria',
            'nik_ibu_pria' => 'NIK ibu mempelai pria',
            'tempat_lahir_ibu_pria' => 'Tempat lahir ibu mempelai pria',
            'tanggal_lahir_ibu_pria' => 'Tanggal lahir ibu mempelai pria',
            'alamat_ibu_pria' => 'Alamat ibu mempelai pria',
            'nama_saksi_1' => 'Nama saksi 1',
            'nik_saksi_1' => 'NIK saksi 1',
            'tempat_lahir_saksi_1' => 'Tempat lahir saksi 1',
            'tanggal_lahir_saksi_1' => 'Tanggal lahir saksi 1',
            'alamat_saksi_1' => 'Alamat saksi 1',
            'nama_saksi_2' => 'Nama saksi 2',
            'nik_saksi_2' => 'NIK saksi 2',
            'tempat_lahir_saksi_2' => 'Tempat lahir saksi 2',
            'tanggal_lahir_saksi_2' => 'Tanggal lahir saksi 2',
            'alamat_saksi_2' => 'Alamat saksi 2',
            'keagamaan_id' => 'Lembaga keagamaan',
            'nama_gereja' => 'Nama gereja/lembaga',
            'tanggal_perkawinan' => 'Tanggal perkawinan',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors(),
            ], 422)
        );
    }

    public static function validateMin7Days($tanggal): bool
    {
        $tanggalObj = Carbon::parse($tanggal);
        $hariIni = Carbon::today();
        $selisihHari = $hariIni->diffInDays($tanggalObj);
        return $selisihHari >= 7;
    }
}
