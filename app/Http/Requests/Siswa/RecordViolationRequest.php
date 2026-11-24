<?php

namespace App\Http\Requests\Siswa;

use App\Models\ExamSession;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RecordViolationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('view', $this->route('examSession')) ?? false;
    }

    public function rules(): array
    {
        return [
            'type' => ['required', 'string', 'max:50'],
            'duration_seconds' => ['nullable', 'integer', 'min:0', 'max:86400'],
            'violation_id' => ['nullable', 'integer', Rule::exists('violations', 'id')],
        ];
    }

    public function after(): array
    {
        return [
            function ($validator): void {
                $session = $this->route('examSession');

                if (! $session instanceof ExamSession) {
                    return;
                }

                if ($this->filled('violation_id')) {
                    $ownsViolation = \App\Models\Violation::query()
                        ->where('id', $this->input('violation_id'))
                        ->where('session_id', $session->id)
                        ->exists();

                    if (! $ownsViolation) {
                        $validator->errors()->add('violation_id', 'Pelanggaran tidak ditemukan.');
                    }
                }
            },
        ];
    }
}
