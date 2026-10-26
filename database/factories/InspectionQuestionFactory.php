<?php

namespace Database\Factories;

use App\Models\InspectionQuestion;
use App\Models\WorkArea;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<InspectionQuestion> */
class InspectionQuestionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'work_area_id' => WorkArea::factory(),
            'question' => fake()->unique()->sentence().'?',
            'sort_order' => fake()->numberBetween(1, 20),
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (): array => ['is_active' => false]);
    }
}
