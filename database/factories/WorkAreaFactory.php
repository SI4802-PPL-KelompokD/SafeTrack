<?php

namespace Database\Factories;

use App\Models\Department;
use App\Models\WorkArea;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<WorkArea> */
class WorkAreaFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->words(3, true);

        return [
            'department_id' => Department::query()->value('id'),
            'code' => Str::upper(Str::limit(Str::slug($name, ''), 30, '')),
            'name' => Str::title($name),
            'description' => fake()->sentence(),
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (): array => ['is_active' => false]);
    }
}
