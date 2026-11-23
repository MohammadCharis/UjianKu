<?php

namespace App\Http\Requests\Guru;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateQuestionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('question')) ?? false;
    }

    public function rules(): array
    {
        return [
            'subject_id' => ['required', 'exists:subjects,id'],
            'type' => ['required', Rule::in(['pg', 'isian', 'esai'])],
            'body' => ['required', 'string'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'options' => ['required_if:type,pg', 'array', 'min:2', 'max:6'],
            'options.*.label' => ['required_if:type,pg', 'string', 'max:5'],
            'options.*.text' => ['required_if:type,pg', 'string', 'max:255'],
            'options.*.is_correct' => ['required_if:type,pg', 'boolean'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($this->input('type') !== 'pg' || $validator->errors()->has('options')) {
                    return;
                }

                $correct = collect($this->input('options', []))
                    ->filter(fn ($option): bool => filter_var($option['is_correct'] ?? false, FILTER_VALIDATE_BOOLEAN))
                    ->count();

                if ($correct !== 1) {
                    $validator->errors()->add('options', 'Pilihan ganda harus memiliki tepat satu kunci jawaban.');
                }
            },
        ];
    }
}
