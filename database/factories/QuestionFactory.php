<?php

namespace Database\Factories;

use App\Models\Question;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Question>
 */
class QuestionFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'subject_id' => Subject::factory(),
            'created_by' => User::factory()->guru(),
            'type' => 'pg',
            'body' => fake()->sentence(),
            'image' => null,
        ];
    }

    public function pg(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'pg',
        ]);
    }

    public function isian(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'isian',
        ]);
    }

    public function esai(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'esai',
        ]);
    }
}
