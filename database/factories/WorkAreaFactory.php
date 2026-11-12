<?php

namespace Database\Factories;

use App\Models\WorkArea;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<WorkArea> */
class WorkAreaFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(3, true),
            'description' => fake()->sentence(),
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (): array => ['is_active' => false]);
    }
}
