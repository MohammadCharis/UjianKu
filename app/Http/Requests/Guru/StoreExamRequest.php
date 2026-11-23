<?php

namespace App\Http\Requests\Guru;

use App\Models\Exam;
use App\Models\Question;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreExamRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Exam::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'subject_id' => ['required', 'exists:subjects,id'],
            'title' => ['required', 'string', 'max:150'],
            'token' => ['required', 'string', 'max:20', Rule::unique('exams', 'token')],
            'start_at' => ['required', 'date'],
            'end_at' => ['required', 'date', 'after:start_at'],
            'duration_min' => ['required', 'integer', 'min:1', 'max:600'],
            'max_violations' => ['required', 'integer', 'min:1', 'max:20'],
            'shuffle' => ['sometimes', 'boolean'],
            'lock_mode' => ['required', Rule::in([Exam::LOCK, Exam::AUTO_SUBMIT])],
            'class_ids' => ['required', 'array', 'min:1'],
            'class_ids.*' => ['required', 'integer', Rule::distinct(), 'exists:classes,id'],
            'questions' => ['required', 'array', 'min:1'],
            'questions.*.id' => ['required', 'integer', Rule::distinct(), 'exists:questions,id'],
            'questions.*.points' => ['required', 'numeric', 'min:0', 'max:100'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->hasAny(['subject_id', 'questions'])) {
                    return;
                }

                $questionIds = collect($this->input('questions', []))->pluck('id')->filter()->all();

                if (Question::whereIn('id', $questionIds)->where('subject_id', '!=', $this->input('subject_id'))->exists()) {
                    $validator->errors()->add('questions', 'Semua soal harus memiliki mapel yang sama dengan ujian.');
                }
            },
        ];
    }
}
