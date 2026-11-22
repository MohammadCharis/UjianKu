<?php

namespace Database\Factories;

use App\Models\Exam;
use App\Models\ExamClass;
use App\Models\SchoolClass;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ExamClass>
 */
class ExamClassFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'exam_id' => Exam::factory(),
            'class_id' => SchoolClass::factory(),
        ];
    }
}
