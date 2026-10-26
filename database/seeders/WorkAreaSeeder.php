<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class WorkAreaSeeder extends Seeder
{
    public function run(): void
    {
        $departmentId = DB::table('departments')->where('code', 'OPS')->value('id');

        if (! $departmentId) {
            throw new RuntimeException('Seeder area kerja membutuhkan departemen berkode OPS.');
        }

        $names = [
            'PIT/Front Loading', 'Jalan', 'Disposal', 'Disposal Kritis', 'Workshop', 'Timbangan',
            'Fuel Storage', 'TPS Limbah B3', 'Kolam Pengendapan Lumpur', 'Nursery', 'Warehouse',
            'Rumah Core Box', 'Rumah Genset', 'Ruang PPGD', 'ROM/Stockpile', 'Perkantoran',
        ];

        foreach ($names as $name) {
            DB::table('work_areas')->updateOrInsert(
                ['department_id' => $departmentId, 'name' => $name],
                [
                    'code' => Str::upper(Str::limit(Str::slug($name, ''), 30, '')),
                    'is_active' => true,
                    'updated_at' => now(),
                    'created_at' => now(),
                ],
            );

            $workAreaId = DB::table('work_areas')
                ->where('department_id', $departmentId)
                ->where('name', $name)
                ->value('id');

            $questions = [
                'Apakah area kerja bersih, rapi, dan bebas dari hambatan?',
                'Apakah APD yang sesuai telah digunakan dengan benar?',
                'Apakah peralatan dan fasilitas keselamatan berada dalam kondisi baik?',
            ];

            foreach ($questions as $index => $question) {
                $exists = DB::table('inspection_questions')
                    ->where('work_area_id', $workAreaId)
                    ->where('question', $question)
                    ->exists();

                if (! $exists) {
                    DB::table('inspection_questions')->insert([
                        'work_area_id' => $workAreaId,
                        'question' => $question,
                        'sort_order' => $index + 1,
                        'is_active' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }
}
