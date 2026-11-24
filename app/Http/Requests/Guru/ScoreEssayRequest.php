<?php

namespace App\Http\Requests\Guru;

use App\Models\Answer;
use App\Models\ExamSession;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class ScoreEssayRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('grade', $this->route('examSession')) ?? false;
    }

    public function rules(): array
    {
        return [
            'scores' => ['required', 'array'],
            'scores.*' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $session = $this->route('examSession');

                if (! $session instanceof ExamSession) {
                    return;
                }

                foreach (array_keys($this->input('scores', [])) as $answerId) {
                    $answer = Answer::query()
                        ->where('id', $answerId)
                        ->where('session_id', $session->id)
                        ->with('question')
                        ->first();

                    if (! $answer) {
                        $validator->errors()->add("scores.{$answerId}", 'Jawaban tidak ditemukan.');
                        continue;
                    }

                    $maximum = (float) ($session->exam->examQuestions()->where('question_id', $answer->question_id)->value('points') ?? 0);
                    $score = $this->input("scores.{$answerId}");

                    if ($score !== null && (float) $score > $maximum) {
                        $validator->errors()->add("scores.{$answerId}", "Nilai maksimal adalah {$maximum}.");
                    }
                }
            },
        ];
    }
}
