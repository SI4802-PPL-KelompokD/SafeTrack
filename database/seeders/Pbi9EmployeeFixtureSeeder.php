<?php

namespace Database\Seeders;

use App\Models\HazardReport;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class Pbi9EmployeeFixtureSeeder extends Seeder
{
    public const PASSWORD = 'Pbi9Only!2026';

    public function run(): void
    {
        if (! app()->environment('testing') || ! in_array(DB::connection()->getDatabaseName(), ['safetrack_test', 'safetrack_dusk'], true)) {
            throw new RuntimeException('Fixture PBI-9 hanya boleh digunakan pada database pengujian.');
        }

        $departments = [];
        foreach (['OPS' => 'Operations', 'HCGS' => 'HCGS', 'ENG' => 'Engineering', 'HSE' => 'HSE'] as $code => $name) {
            $departments[$code] = DB::table('departments')->insertGetId([
                'code' => $code, 'name' => $name, 'is_active' => true,
                'is_hse' => $code === 'HSE', 'created_at' => now(), 'updated_at' => now(),
            ]);
        }
        $status = DB::table('status_karyawan')->insertGetId([
            'code' => 'permanent', 'name' => 'Karyawan Tetap', 'created_at' => now(), 'updated_at' => now(),
        ]);

        $users = [];
        foreach ([
            ['H1', 'Petugas HSE Penguji', 'ADMIN-001', 'h1@example.test', 'HSE', 'admin', true],
            ['H2', 'Admin Utama Penguji', 'ADMIN-002', 'h2@example.test', 'HSE', 'super_admin', true],
            ['K1', 'Aditya Pratama', 'EMP-001', 'aditya@example.test', 'OPS', 'employee', true],
            ['K2', 'Nabila Anindya', 'EMP-003', 'nabila@example.test', 'HCGS', 'employee', false],
            ['K3', 'Karyawan Pemilik Laporan', 'EMP-004', 'k3@example.test', 'OPS', 'employee', true],
            ['KD', 'Karyawan Terhapus', 'EMP-999', 'kd@example.test', 'OPS', 'employee', false],
        ] as [$alias, $name, $nik, $email, $department, $role, $active]) {
            $registry = DB::table('nik')->insertGetId([
                'nik' => $nik, 'full_name' => $name, 'department_id' => $departments[$department],
                'employment_status_id' => $status, 'is_active' => $active,
                'created_at' => now(), 'updated_at' => now(),
            ]);
            $users[$alias] = User::factory()->create([
                'name' => $name, 'username' => strtolower($alias), 'email' => $email,
                'password' => self::PASSWORD, 'department_id' => $departments[$department],
                'employee_registry_id' => $registry, 'role' => $role, 'is_active' => $active,
            ]);
            $users[$alias]->forceFill(['status_karyawan_id' => $status])->save();
        }

        $users['KD']->forceFill(['deleted_by' => $users['H1']->id, 'deletion_reason' => 'Penghapusan sebelum pengujian.'])->save();
        $users['KD']->delete();
        HazardReport::factory()->pending()->create([
            'reporter_id' => $users['K3']->id, 'reporter_department_id' => $departments['OPS'],
            'title' => 'Laporan K3 untuk pengujian histori',
        ]);
    }
}
