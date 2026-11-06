<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class DeletePenggunaRequest extends FormRequest
{
    /**
     * Tentukan apakah pengguna memiliki hak akses untuk request ini.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Aturan validasi penghapusan/soft-delete akun pengguna (FR-07 / UC-33).
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'deletion_reason' => ['required', 'string', 'min:3', 'max:1000'],
        ];
    }

    /**
     * Pesan validasi dalam Bahasa Indonesia.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'deletion_reason.required' => 'Alasan penghapusan akun wajib diisi.',
            'deletion_reason.string' => 'Alasan penghapusan harus berupa teks.',
            'deletion_reason.min' => 'Alasan penghapusan minimal harus terdiri dari 3 karakter.',
            'deletion_reason.max' => 'Alasan penghapusan maksimal 1000 karakter.',
        ];
    }
}
