<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment('local', 'testing')) {
            throw new \RuntimeException('Demo hanya local/testing.');
        }

        DB::transaction(function () {
            $this->call(MasterDataSeeder::class);

            $insert = function ($table, $key, $values) {
                $id = DB::table($table)->where($key)->value('id');

                return $id ?: DB::table($table)->insertGetId($key + $values + ['created_at' => now(), 'updated_at' => now()]);
            };

            $hse = $insert('departments', ['code' => 'HSE'], ['name' => 'HSE', 'is_hse' => true]);
            $ops = $insert('departments', ['code' => 'OPS'], ['name' => 'Operasional', 'is_hse' => false]);

            $permanent = DB::table('status_karyawan')->where('code', 'permanent')->value('id');
            $contract = DB::table('status_karyawan')->where('code', 'contract')->value('id');

            foreach ([
                ['DEMO001', 'Admin Utama', 'super_admin', $hse, 'admin'],
                ['DEMO002', 'Petugas HSE', 'employee', $hse, 'hse'],
                ['DEMO003', 'Karyawan Demo', 'employee', $ops, 'karyawan'],
            ] as [$nik, $name, $role, $dept, $username]) {
                $reg = $insert('nik', ['nik' => $nik], [
                    'full_name' => $name,
                    'department_id' => $dept,
                    'employment_status_id' => $permanent,
                    'is_active' => true,
                ]);

                $user = DB::table('users')->where('employee_registry_id', $reg)->first();
                if (! $user) {
                    DB::table('users')->insert([
                        'employee_registry_id' => $reg,
                        'department_id' => $dept,
                        'name' => $name,
                        'username' => $username,
                        'email' => $username.'@safetrack.test',
                        'password' => Hash::make('DemoOnly!2026'),
                        'role' => $role,
                        'is_active' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            DB::table('users')->where('username', 'hse')->where('email', 'hse@safetrack.test')->update([
                'role' => 'employee',
                'department_id' => $hse,
                'updated_at' => now(),
            ]);

            // NIK contoh yang BELUM punya akun untuk pengujian registrasi
            $insert('nik', ['nik' => 'DEMO004'], [
                'full_name' => 'Calon Karyawan Tetap',
                'department_id' => $ops,
                'employment_status_id' => $permanent,
                'is_active' => true,
            ]);

            $insert('nik', ['nik' => 'DEMO005'], [
                'full_name' => 'Calon Karyawan Kontrak',
                'department_id' => $ops,
                'employment_status_id' => $contract,
                'is_active' => true,
            ]);

            $this->call(WorkAreaSeeder::class);
            $area = DB::table('area_kerja')->where('name', 'PIT/Front Loading')->value('id');
            $insert('shift', ['code' => 'PAGI'], ['name' => 'Pagi', 'starts_at' => '07:00', 'ends_at' => '15:00']);
            foreach (['Apakah jalur evakuasi bebas hambatan?', 'Apakah APD digunakan dengan benar?'] as $i => $q) {
                $insert('inspection_questions', ['work_area_id' => $area, 'question' => $q], ['sort_order' => $i + 1]);
            }
            $insert('sap_point_rules', ['activity' => 'hazard_verified', 'effective_from' => '2026-10-01'], ['points' => 1]);
        });
    }
}
