<?php

namespace Database\Factories;

use App\Models\Answer;
use App\Models\ExamSession;
use App\Models\Question;
use App\Models\QuestionOption;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Answer>
 */
class AnswerFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'session_id' => ExamSession::factory(),
            'question_id' => Question::factory(),
            'option_id' => QuestionOption::factory(),
            'answer_text' => null,
            'score' => null,
            'is_doubtful' => false,
        ];
    }

    public function doubtful(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_doubtful' => true,
        ]);
    }
}
