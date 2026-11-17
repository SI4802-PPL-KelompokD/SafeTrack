<?php

namespace Database\Seeders;

use App\Models\EmployeeRegistry;
use App\Models\EmploymentStatus;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class RegistrationTestNikSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment('local', 'testing')) {
            throw new RuntimeException('NIK uji registrasi hanya boleh ditambahkan pada lingkungan local/testing.');
        }

        $registryTable = (new EmployeeRegistry)->getTable();
        $statusTable = (new EmploymentStatus)->getTable();
        $departments = DB::table('departments')->where('is_active', true)
            ->whereIn('code', ['OPS', 'HSE'])->pluck('id', 'code');
        $employmentStatuses = DB::table($statusTable)
            ->whereIn('code', ['permanent', 'contract'])->pluck('id', 'code');

        if ($departments->count() !== 2 || $employmentStatuses->count() !== 2) {
            throw new RuntimeException('Departemen aktif OPS/HSE dan status permanent/contract harus tersedia sebelum menambahkan NIK uji.');
        }

        $employees = [
            ['EMP-TEST-001', 'Andi Pratama', 'OPS', 'permanent'],
            ['EMP-TEST-002', 'Budi Santoso', 'OPS', 'contract'],
            ['EMP-TEST-003', 'Citra Lestari', 'HSE', 'permanent'],
            ['EMP-TEST-004', 'Dinda Putri', 'OPS', 'permanent'],
            ['EMP-TEST-005', 'Eko Saputra', 'HSE', 'contract'],
        ];

        DB::transaction(function () use ($registryTable, $employees, $departments, $employmentStatuses): void {
            foreach ($employees as [$nik, $fullName, $departmentCode, $statusCode]) {
                if (DB::table($registryTable)->where('nik', $nik)->exists()) {
                    continue;
                }

                DB::table($registryTable)->insert([
                    'nik' => $nik,
                    'full_name' => $fullName,
                    'department_id' => $departments[$departmentCode],
                    'employment_status_id' => $employmentStatuses[$statusCode],
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });

        $registries = DB::table($registryTable.' as registry')
            ->join('departments', 'departments.id', '=', 'registry.department_id')
            ->join($statusTable.' as employment_status', 'employment_status.id', '=', 'registry.employment_status_id')
            ->leftJoin('users', 'users.employee_registry_id', '=', 'registry.id')
            ->whereIn('registry.nik', array_column($employees, 0))
            ->orderBy('registry.nik')
            ->get(['registry.nik', 'registry.full_name', 'departments.name as department', 'employment_status.name as employment_status', 'registry.is_active', 'users.id as user_id']);

        $this->command?->table(
            ['NIK', 'Nama', 'Departemen', 'Kepegawaian', 'Registrasi'],
            $registries->map(fn (object $registry): array => [
                $registry->nik,
                $registry->full_name,
                $registry->department,
                $registry->employment_status,
                $registry->user_id !== null ? 'Sudah digunakan' : ($registry->is_active ? 'Siap digunakan' : 'Nonaktif'),
            ])->all(),
        );
    }
}
