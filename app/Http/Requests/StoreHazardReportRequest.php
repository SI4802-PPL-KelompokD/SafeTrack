<?php

namespace App\Http\Requests;

use App\Models\Department;
use App\Models\HazardCategory;
use Illuminate\Foundation\Http\FormRequest;

class StoreHazardReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => [
                'required',
                'string',
                'max:200',
            ],

            'location' => [
                'required',
                'string',
                'max:255',
            ],

            'description' => [
                'required',
                'string',
            ],

            'category_id' => [
                'required',
                'integer',
                'exists:'.HazardCategory::class.',id',
            ],

            'reporter_department_id' => [
                'required',
                'integer',
                'exists:'.Department::class.',id',
            ],

            'repair_department_id' => [
                'required',
                'integer',
                'exists:'.Department::class.',id',
            ],

            'immediate_action' => [
                'required',
                'string',
            ],

            'client_request_id' => [
                'nullable',
                'uuid',
            ],

            'photos' => [
                'required',
                'array',
                'min:1',
                'max:1',
            ],

            'photos.*' => [
                'required',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Judul laporan wajib diisi.',
            'title.string' => 'Judul laporan harus berupa teks.',
            'title.max' => 'Judul laporan maksimal 200 karakter.',

            'location.required' => 'Lokasi kejadian wajib diisi.',
            'location.string' => 'Lokasi kejadian harus berupa teks.',
            'location.max' => 'Lokasi maksimal 255 karakter.',

            'description.required' => 'Deskripsi kejadian wajib diisi.',
            'description.string' => 'Deskripsi harus berupa teks.',

            'category_id.required' => 'Kategori temuan wajib dipilih.',
            'category_id.integer' => 'Kategori temuan tidak valid.',
            'category_id.exists' => 'Kategori temuan tidak ditemukan.',

            'reporter_department_id.required' => 'Departemen pelapor wajib dipilih.',
            'reporter_department_id.integer' => 'Departemen pelapor tidak valid.',
            'reporter_department_id.exists' => 'Departemen pelapor tidak ditemukan.',

            'repair_department_id.required' => 'Departemen perbaikan wajib dipilih.',
            'repair_department_id.integer' => 'Departemen perbaikan tidak valid.',
            'repair_department_id.exists' => 'Departemen perbaikan tidak ditemukan.',

            'immediate_action.required' => 'Tindakan sementara wajib diisi.',
            'immediate_action.string' => 'Tindakan sementara harus berupa teks.',

            'client_request_id.uuid' => 'ID permintaan tidak valid.',

            'photos.required' => 'Foto bukti wajib diunggah.',
            'photos.array' => 'Format data foto tidak valid.',
            'photos.min' => 'Minimal harus mengunggah 1 foto bukti.',
            'photos.max' => 'Hanya boleh mengunggah 1 foto bukti.',

            'photos.*.required' => 'Foto bukti wajib diisi.',
            'photos.*.image' => 'File bukti harus berupa gambar.',
            'photos.*.mimes' => 'Format foto harus JPG, JPEG, PNG, atau WEBP.',
            'photos.*.max' => 'Ukuran foto maksimal 5 MB.',
        ];
    }
}