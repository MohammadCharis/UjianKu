<?php

namespace Tests\Feature;

use App\Models\Exam;
use App\Models\ExamSession;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StageThreeExamFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_can_join_exam_with_valid_token_only(): void
    {
        [$studentUser, $student] = $this->makeStudent();
        $exam = $this->makeExam($student->class_id, ['token' => 'TEST123']);

        $this->actingAs($studentUser)
            ->get(route('siswa.exams.index'))
            ->assertOk()
            ->assertSee($exam->title);

        $this->actingAs($studentUser)
            ->post(route('siswa.exams.join'), ['token' => 'SALAH'])
            ->assertSessionHasErrors('token');

        $this->actingAs($studentUser)
            ->post(route('siswa.exams.join'), ['token' => 'test123'])
            ->assertRedirect(route('siswa.exams.work', ExamSession::firstOrFail()));

        $this->assertDatabaseHas('exam_sessions', [
            'exam_id' => $exam->id,
            'student_id' => $student->id,
            'status' => ExamSession::BERLANGSUNG,
        ]);
    }

    public function test_exam_page_does_not_expose_correct_answer_flag(): void
    {
        [$studentUser, $student] = $this->makeStudent();
        $exam = $this->makeExam($student->class_id);
        $question = $this->makeQuestion($exam->subject_id);
        $correctOption = QuestionOption::factory()->create([
            'question_id' => $question->id,
            'label' => 'A',
            'text' => 'Jawaban yang benar',
            'is_correct' => true,
        ]);
        $exam->questions()->attach($question, ['order_no' => 1, 'points' => 10]);

        $session = ExamSession::factory()->create([
            'exam_id' => $exam->id,
            'student_id' => $student->id,
        ]);

        $this->actingAs($studentUser)
            ->get(route('siswa.exams.work', $session))
            ->assertOk()
            ->assertSee($correctOption->text)
            ->assertDontSee('is_correct');
    }

    public function test_only_session_owner_can_save_answer_and_duplicate_is_updated(): void
    {
        [$studentUser, $student] = $this->makeStudent();
        [, $otherStudent] = $this->makeStudent();
        $exam = $this->makeExam($student->class_id);
        $question = $this->makeQuestion($exam->subject_id);
        $option = QuestionOption::factory()->create(['question_id' => $question->id]);
        $exam->questions()->attach($question, ['order_no' => 1, 'points' => 10]);

        $session = ExamSession::factory()->create([
            'exam_id' => $exam->id,
            'student_id' => $student->id,
        ]);

        $payload = ['question_id' => $question->id, 'option_id' => $option->id];

        $this->actingAs(User::find($otherStudent->user_id))
            ->postJson(route('siswa.exams.answers', $session), $payload)
            ->assertForbidden();

        $this->actingAs($studentUser)
            ->postJson(route('siswa.exams.answers', $session), $payload)
            ->assertOk()
            ->assertJson(['status' => 'saved']);

        $this->actingAs($studentUser)
            ->postJson(route('siswa.exams.answers', $session), [
                'question_id' => $question->id,
                'option_id' => null,
                'answer_text' => 'Teks isian',
            ])->assertJsonValidationErrors('option_id');

        $this->assertSame(1, $session->answers()->count());
    }

    public function test_answer_is_rejected_after_server_deadline(): void
    {
        [$studentUser, $student] = $this->makeStudent();
        $exam = $this->makeExam($student->class_id);
        $question = $this->makeQuestion($exam->subject_id);
        $option = QuestionOption::factory()->create(['question_id' => $question->id]);
        $exam->questions()->attach($question, ['order_no' => 1, 'points' => 10]);

        $session = ExamSession::factory()->create([
            'exam_id' => $exam->id,
            'student_id' => $student->id,
        ]);
        $session->exam->update(['end_at' => now()->subMinute()]);

        $this->actingAs($studentUser)
            ->postJson(route('siswa.exams.answers', $session), [
                'question_id' => $question->id,
                'option_id' => $option->id,
            ])
            ->assertForbidden();
    }

    public function test_submit_grades_pg_and_locks_further_answers(): void
    {
        [$studentUser, $student] = $this->makeStudent();
        $exam = $this->makeExam($student->class_id);
        $question = $this->makeQuestion($exam->subject_id);
        $correctOption = QuestionOption::factory()->create([
            'question_id' => $question->id,
            'is_correct' => true,
        ]);
        $exam->questions()->attach($question, ['order_no' => 1, 'points' => 10]);

        $session = ExamSession::factory()->create([
            'exam_id' => $exam->id,
            'student_id' => $student->id,
        ]);

        $this->actingAs($studentUser)
            ->postJson(route('siswa.exams.answers', $session), [
                'question_id' => $question->id,
                'option_id' => $correctOption->id,
            ])
            ->assertOk();

        $response = $this->actingAs($studentUser)
            ->postJson(route('siswa.exams.submit', $session))
            ->assertOk();

        $response->assertJsonPath('status', 'submitted');

        $this->assertDatabaseHas('exam_sessions', [
            'id' => $session->id,
            'status' => ExamSession::SELESAI,
            'score' => 10,
        ]);
        $this->assertDatabaseHas('answers', [
            'session_id' => $session->id,
            'question_id' => $question->id,
            'score' => 10,
        ]);

        $this->actingAs($studentUser)
            ->postJson(route('siswa.exams.answers', $session), [
                'question_id' => $question->id,
                'option_id' => $correctOption->id,
            ])
            ->assertForbidden();
    }

    private function makeStudent(): array
    {
        $user = User::factory()->siswa()->create();
        $class = SchoolClass::factory()->create();
        $student = Student::factory()->create([
            'user_id' => $user->id,
            'class_id' => $class->id,
        ]);

        return [$user, $student];
    }

    private function makeExam(int $classId, array $overrides = []): Exam
    {
        $exam = Exam::factory()->create(array_merge([
            'start_at' => now()->subMinute(),
            'end_at' => now()->addHour(),
            'duration_min' => 60,
            'token' => 'UJIAN1',
        ], $overrides));
        $exam->classes()->attach($classId);

        return $exam;
    }

    private function makeQuestion(int $subjectId): Question
    {
        return Question::factory()->create([
            'subject_id' => $subjectId,
            'created_by' => User::factory()->guru()->create()->id,
            'type' => 'pg',
        ]);
    }
}
