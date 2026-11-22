<?php

namespace Database\Factories;

use App\Models\Exam;
use App\Models\ExamSession;
use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ExamSession>
 */
class ExamSessionFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'exam_id' => Exam::factory(),
            'student_id' => Student::factory(),
            'started_at' => now(),
            'finished_at' => null,
            'status' => ExamSession::BERLANGSUNG,
            'score' => null,
            'violation_count' => 0,
            'last_seen_at' => now(),
        ];
    }

    public function locked(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ExamSession::TERKUNCI,
            'violation_count' => 3,
        ]);
    }

    public function finished(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ExamSession::SELESAI,
            'finished_at' => now(),
            'score' => 85,
        ]);
    }

    public function disqualified(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ExamSession::DISKUALIFIKASI,
            'finished_at' => now(),
        ]);
    }
}
