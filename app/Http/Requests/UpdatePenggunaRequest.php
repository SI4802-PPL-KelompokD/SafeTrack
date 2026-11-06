<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Schema;

class UpdatePenggunaRequest extends FormRequest
{
    /**
     * Tentukan apakah pengguna memiliki hak akses untuk request ini.
     * Pengecekan role dilakukan di controller/service.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Aturan validasi untuk memperbarui akun pengguna (FR-06 / UC-33).
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $statusTable = Schema::hasTable('status_karyawan') ? 'status_karyawan' : 'employment_statuses';

        return [
            'department_id' => ['required', 'integer', 'exists:departments,id'],
            'employment_status_id' => ['required', 'integer', "exists:{$statusTable},id"],
            'is_active' => ['required', 'boolean'],
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
            'department_id.required' => 'Departemen wajib dipilih.',
            'department_id.exists' => 'Departemen yang dipilih tidak valid atau tidak ditemukan.',
            'employment_status_id.required' => 'Status kepegawaian wajib dipilih.',
            'employment_status_id.exists' => 'Status kepegawaian yang dipilih tidak valid.',
            'is_active.required' => 'Status akun wajib ditentukan.',
            'is_active.boolean' => 'Format status akun tidak valid.',
        ];
    }
}
