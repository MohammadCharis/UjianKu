<?php

namespace Database\Factories;

use App\Models\ExamSession;
use App\Models\Violation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Violation>
 */
class ViolationFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'session_id' => ExamSession::factory(),
            'handled_by' => null,
            'type' => 'pindah_tab',
            'level' => Violation::RINGAN,
            'occurred_at' => now(),
            'duration_seconds' => fake()->numberBetween(3, 60),
            'action' => null,
            'note' => null,
        ];
    }

    public function sedang(): static
    {
        return $this->state(fn (array $attributes) => [
            'level' => Violation::SEDANG,
        ]);
    }

    public function berat(): static
    {
        return $this->state(fn (array $attributes) => [
            'level' => Violation::BERAT,
        ]);
    }
}
