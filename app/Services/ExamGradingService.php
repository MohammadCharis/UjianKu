<?php

namespace App\Services;

use App\Models\Answer;
use App\Models\ExamQuestion;
use App\Models\ExamSession;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ExamGradingService
{
    public function grade(ExamSession $session): void
    {
        $points = ExamQuestion::query()
            ->where('exam_id', $session->exam_id)
            ->pluck('points', 'question_id');

        DB::transaction(function () use ($session, $points): void {
            foreach ($session->answers()->with('question')->get() as $answer) {
                $maximumPoints = (float) ($points[$answer->question_id] ?? 0);

                $score = match ($answer->question->type) {
                    'pg' => $answer->option_id !== null && $answer->option?->is_correct ? $maximumPoints : 0,
                    'isian' => $this->gradeShortAnswer($answer, $maximumPoints),
                    default => null,
                };

                $answer->update(['score' => $score]);
            }

            $session->update(['score' => $session->answers()->sum('score')]);
        });
    }

    private function gradeShortAnswer(Answer $answer, float $maximumPoints): ?float
    {
        $correctText = $answer->question->options()->where('is_correct', true)->value('text');

        if ($correctText === null || ! filled($answer->answer_text)) {
            return null;
        }

        return $this->normalize($answer->answer_text) === $this->normalize($correctText)
            ? $maximumPoints
            : 0;
    }

    private function normalize(string $value): string
    {
        return Str::lower(Str::squish(trim($value)));
    }
}
