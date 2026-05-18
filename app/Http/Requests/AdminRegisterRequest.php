<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

/**
 * Form Request untuk validasi Registrasi Admin
 *
 * Security Features:
 * - name: string dengan format nama valid
 * - username: alphanumeric dan unique
 * - email: email format dan unique
 * - password: strong password dengan konfirmasi
 * - security_question_id: numeric dan exists
 * - security_answer: string untuk di-encrypt
 */
class AdminRegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        // Hanya allow jika belum ada admin
        return \App\Models\User::role('Admin')->count() === 0;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:100|regex:/^[\p{L}\s\.\-,]+$/u',
            'username' => 'required|string|max:50|regex:/^[a-zA-Z0-9_]+$/|unique:users,username',
            'email' => 'required|email|max:100|unique:users,email',
            'password' => 'required|string|min:8|confirmed|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/',
            'password_confirmation' => 'required|string',
            'security_question_id' => 'required|numeric|exists:security_questions,id',
            'security_answer' => 'required|string|max:255',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Nama lengkap harus diisi',
            'name.regex' => 'Nama hanya boleh mengandung huruf, spasi, titik, koma, dan tanda hubung',
            'username.required' => 'Username harus diisi',
            'username.regex' => 'Username hanya boleh mengandung huruf, angka, dan underscore',
            'username.unique' => 'Username sudah digunakan',
            'email.required' => 'Email harus diisi',
            'email.email' => 'Format email tidak valid',
            'email.unique' => 'Email sudah digunakan',
            'password.required' => 'Password harus diisi',
            'password.min' => 'Password minimal 8 karakter',
            'password.confirmed' => 'Konfirmasi password tidak cocok',
            'password.regex' => 'Password harus mengandung huruf kecil, huruf besar, dan angka',
            'security_question_id.required' => 'Pertanyaan keamanan harus dipilih',
            'security_question_id.exists' => 'Pertanyaan keamanan tidak valid',
            'security_answer.required' => 'Jawaban keamanan harus diisi',
        ];
    }

    /**
     * Handle a failed validation attempt.
     *
     * @param  \Illuminate\Contracts\Validation\Validator  $validator
     * @return void
     *
     * @throws \Illuminate\Http\Exceptions\HttpResponseException
     */
    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors(),
            ], 422)
        );
    }

    /**
     * Prepare inputs for validation.
     *
     * @return void
     */
    protected function prepareForValidation(): void
    {
        // Trim dan sanitize input
        $this->merge([
            'name' => is_string($this->name) ? trim(strip_tags($this->name)) : $this->name,
            'username' => is_string($this->username) ? trim($this->username) : $this->username,
            'email' => is_string($this->email) ? trim(strtolower($this->email)) : $this->email,
            'security_answer' => is_string($this->security_answer) ? trim($this->security_answer) : $this->security_answer,
        ]);
    }
}
