<?php

namespace Database\Factories;

use App\Models\Exam;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Exam>
 */
class ExamFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'subject_id' => Subject::factory(),
            'created_by' => User::factory()->guru(),
            'title' => fake()->sentence(3),
            'token' => Str::upper(Str::random(8)),
            'start_at' => now()->subMinutes(5),
            'end_at' => now()->addHour(),
            'duration_min' => 60,
            'max_violations' => 3,
            'shuffle' => false,
            'lock_mode' => Exam::LOCK,
        ];
    }

    public function autoSubmit(): static
    {
        return $this->state(fn (array $attributes) => [
            'lock_mode' => Exam::AUTO_SUBMIT,
        ]);
    }
}
