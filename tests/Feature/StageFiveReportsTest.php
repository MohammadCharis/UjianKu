<?php

namespace Tests\Feature;

use App\Models\Answer;
use App\Models\Exam;
use App\Models\ExamSession;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;
use App\Models\Violation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StageFiveReportsTest extends TestCase
{
    use RefreshDatabase;

    public function test_guru_can_grade_essay_and_report_exports_work(): void
    {
        $guru = User::factory()->guru()->create();
        $studentUser = User::factory()->siswa()->create();
        $class = SchoolClass::factory()->create();
        $student = Student::factory()->create(['user_id' => $studentUser->id, 'class_id' => $class->id]);
        $subject = Subject::factory()->create();

        $exam = Exam::factory()->create([
            'subject_id' => $subject->id,
            'created_by' => $guru->id,
        ]);
        $exam->classes()->attach($class);

        $pgQuestion = Question::factory()->create(['subject_id' => $subject->id, 'created_by' => $guru->id, 'type' => 'pg']);
        $correctOption = QuestionOption::factory()->create(['question_id' => $pgQuestion->id, 'is_correct' => true]);
        $essayQuestion = Question::factory()->create(['subject_id' => $subject->id, 'created_by' => $guru->id, 'type' => 'esai']);
        $exam->questions()->attach($pgQuestion, ['order_no' => 1, 'points' => 10]);
        $exam->questions()->attach($essayQuestion, ['order_no' => 2, 'points' => 15]);

        $session = ExamSession::factory()->create([
            'exam_id' => $exam->id,
            'student_id' => $student->id,
            'status' => ExamSession::SELESAI,
            'finished_at' => now(),
        ]);

        $pgAnswer = Answer::factory()->create([
            'session_id' => $session->id,
            'question_id' => $pgQuestion->id,
            'option_id' => $correctOption->id,
            'score' => 10,
        ]);
        $essayAnswer = Answer::factory()->create([
            'session_id' => $session->id,
            'question_id' => $essayQuestion->id,
            'answer_text' => 'Jawaban esai siswa',
            'score' => null,
        ]);

        $session->update(['score' => 10]);

        $this->actingAs($guru)
            ->get(route('guru.grading.index'))
            ->assertOk()
            ->assertSee($studentUser->name);

        $this->actingAs($guru)
            ->put(route('guru.grading.update', $session), [
                'scores' => [$essayAnswer->id => 12],
            ])
            ->assertRedirect(route('guru.grading.index'));

        $this->assertDatabaseHas('answers', ['id' => $essayAnswer->id, 'score' => 12]);
        $this->assertDatabaseHas('exam_sessions', ['id' => $session->id, 'score' => 22]);

        $this->actingAs($guru)
            ->get(route('guru.reports.index'))
            ->assertOk()
            ->assertSee($studentUser->name);

        $this->actingAs($guru)
            ->get(route('guru.reports.export.excel'))
            ->assertOk();

        $this->actingAs($guru)
            ->get(route('guru.reports.export.pdf'))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');

        Violation::factory()->create(['session_id' => $session->id]);

        $this->actingAs($guru)
            ->get(route('guru.reports.export.violations'))
            ->assertOk();
    }
}
