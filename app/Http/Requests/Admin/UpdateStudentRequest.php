<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        $student = $this->route('student');

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($student?->user_id)],
            'nis' => ['required', 'string', 'max:50', Rule::unique('students', 'nis')->ignore($student)],
            'class_id' => ['required', 'exists:classes,id'],
            'password' => ['nullable', 'string', 'min:8'],
        ];
    }
}
