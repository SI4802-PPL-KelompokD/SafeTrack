<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/** @extends Factory<User> */
class UserFactory extends Factory
{
    public function definition(): array
    {
        $department = DB::table('departments')->insertGetId([
            'code' => Str::random(20), 'name' => 'Test Department', 'is_hse' => false,
        ]);
        $status = DB::table('status_karyawan')->value('id')
            ?: DB::table('status_karyawan')->insertGetId(['code' => 'test', 'name' => 'Test']);
        $registry = DB::table('nik')->insertGetId([
            'nik' => Str::uuid(), 'full_name' => 'Test User',
            'department_id' => $department, 'employment_status_id' => $status,
        ]);

        return [
            'employee_registry_id' => $registry, 'department_id' => $department,
            'name' => fake()->name(), 'username' => fake()->unique()->userName(),
            'email' => fake()->unique()->safeEmail(), 'password' => 'password',
            'role' => 'employee', 'is_active' => true,
            'email_verified_at' => now(), 'remember_token' => Str::random(10),
        ];
    }

    public function admin(): static
    {
        return $this->state(fn (): array => ['role' => 'admin', 'is_active' => true]);
    }

    public function hseEmployee(): static
    {
        return $this->employee()->afterCreating(function (User $user): void {
            DB::table('departments')->where('id', $user->department_id)->update(['is_hse' => true]);
        });
    }

    public function employee(): static
    {
        return $this->state(fn (): array => ['role' => 'employee', 'is_active' => true]);
    }

    public function inactive(): static
    {
        return $this->employee()->state(fn (): array => ['is_active' => false]);
    }

    public function softDeleted(): static
    {
        return $this->employee()->state(fn (): array => ['deleted_at' => now()]);
    }

    public function unverified(): static
    {
        return $this->state(fn (): array => ['email_verified_at' => null]);
    }
}
