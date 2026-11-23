<?php

namespace App\Http\Requests\Siswa;

use App\Models\ExamQuestion;
use App\Models\ExamSession;
use App\Models\Question;
use App\Models\QuestionOption;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class AnswerExamQuestionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('answer', $this->route('examSession')) ?? false;
    }

    public function rules(): array
    {
        return [
            'question_id' => ['required', 'integer'],
            'option_id' => ['nullable', 'integer'],
            'answer_text' => ['nullable', 'string', 'max:5000'],
            'is_doubtful' => ['sometimes', 'boolean'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $session = $this->route('examSession');

                if (! $session instanceof ExamSession) {
                    return;
                }

                $questionId = (int) $this->input('question_id');
                $optionId = $this->input('option_id');

                $inExam = ExamQuestion::query()
                    ->where('exam_id', $session->exam_id)
                    ->where('question_id', $questionId)
                    ->exists();

                if (! $inExam) {
                    $validator->errors()->add('question_id', 'Soal tidak termasuk dalam ujian ini.');

                    return;
                }

                $question = Question::find($questionId);

                if ($question?->type === 'pg' && $optionId === null) {
                    $validator->errors()->add('option_id', 'Pilihan jawaban wajib diisi.');

                    return;
                }

                if ($optionId !== null && ! QuestionOption::query()
                    ->where('id', $optionId)
                    ->where('question_id', $questionId)
                    ->exists()) {
                    $validator->errors()->add('option_id', 'Pilihan jawaban tidak valid.');
                }
            },
        ];
    }
}
