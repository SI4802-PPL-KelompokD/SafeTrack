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
            'nik' => ['required', 'string', 'max:40'],
            'username' => ['required', 'string', 'min:3', 'max:60', 'regex:/^[a-zA-Z0-9._-]+$/'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
        ];
    }
    

    public function messages(): array
    {
        return [
            'nik.required' => 'NIK wajib diisi.',
            'nik.string' => 'NIK harus berupa teks.',
            'nik.max' => 'NIK tidak boleh lebih dari 40 karakter.',
            'username.required' => 'Username wajib diisi.',
            'username.string' => 'Username harus berupa teks.',
            'username.min' => 'Username minimal 3 karakter.',
            'username.max' => 'Username tidak boleh lebih dari 60 karakter.',
            'username.regex' => 'Format username tidak valid.',
            'email.required' => 'Email wajib diisi.',
            'email.string' => 'Email harus berupa teks.',
            'email.email' => 'Format email tidak valid.',
            'email.max' => 'Email tidak boleh lebih dari 255 karakter.',
            'email.unique' => 'Email sudah digunakan.',
            'password.required' => 'Password wajib diisi.',
            'password.string' => 'Password harus berupa teks.',
            'password.min' => 'Password minimal 8 karakter.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'nik' => is_string($this->nik) ? trim($this->nik) : $this->nik,
            'email' => is_string($this->email) ? strtolower(trim($this->email)) : $this->email,
        ]);
    }
}

