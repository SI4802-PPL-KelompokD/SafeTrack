<?php

namespace App\Http\Requests;

use App\Models\HazardReport;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class HazardReportFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // akses dijaga middleware auth + HazardReportPolicy
    }

    public function rules(): array
    {
        return [
            'q' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::in(array_keys(HazardReport::STATUS_LABELS))],
            'department_id' => ['nullable', 'integer', 'exists:departments,id'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
            'archive' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'to.after_or_equal' => 'Tanggal akhir harus setelah tanggal mulai.',
            'from.date' => 'Tanggal mulai tidak valid.',
            'to.date' => 'Tanggal akhir tidak valid.',
            'status.in' => 'Status tidak dikenal.',
            'department_id.exists' => 'Departemen tidak ditemukan.',
        ];
    }
}
