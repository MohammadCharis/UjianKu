<?php

namespace Tests\Feature;

use App\Models\Exam;
use App\Models\ExamSession;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\User;
use App\Models\Violation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StageFourViolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_recording_violation_increments_count_and_sets_level(): void
    {
        [$studentUser, $student] = $this->makeStudent();
        $exam = $this->makeExam($student->class_id, ['max_violations' => 3]);
        $session = ExamSession::factory()->create([
            'exam_id' => $exam->id,
            'student_id' => $student->id,
        ]);

        $this->actingAs($studentUser)
            ->postJson(route('siswa.exams.violations', $session), [
                'type' => 'pindah_tab',
                'duration_seconds' => 10,
            ])
            ->assertOk()
            ->assertJsonPath('violation_count', 1)
            ->assertJsonPath('level', null);

        $this->assertDatabaseHas('violations', [
            'session_id' => $session->id,
            'type' => 'pindah_tab',
            'level' => Violation::SEDANG,
            'duration_seconds' => 10,
        ]);
    }

    public function test_session_is_locked_when_violation_limit_is_reached(): void
    {
        [$studentUser, $student] = $this->makeStudent();
        $exam = $this->makeExam($student->class_id, ['max_violations' => 1, 'lock_mode' => Exam::LOCK]);
        $session = ExamSession::factory()->create([
            'exam_id' => $exam->id,
            'student_id' => $student->id,
        ]);

        $this->actingAs($studentUser)
            ->postJson(route('siswa.exams.violations', $session), [
                'type' => 'pindah_tab',
                'duration_seconds' => 0,
            ])
            ->assertOk()
            ->assertJsonPath('locked', true)
            ->assertJsonPath('session_status', ExamSession::TERKUNCI);

        $this->assertDatabaseHas('exam_sessions', [
            'id' => $session->id,
            'status' => ExamSession::TERKUNCI,
            'violation_count' => 1,
        ]);
    }

    public function test_session_is_auto_submitted_for_auto_submit_mode(): void
    {
        [$studentUser, $student] = $this->makeStudent();
        $exam = $this->makeExam($student->class_id, ['max_violations' => 1, 'lock_mode' => Exam::AUTO_SUBMIT]);
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

        $this->actingAs($studentUser)
            ->postJson(route('siswa.exams.violations', $session), [
                'type' => 'pindah_tab',
                'duration_seconds' => 0,
            ])
            ->assertOk()
            ->assertJsonPath('auto_submitted', true)
            ->assertJsonPath('session_status', ExamSession::SELESAI);

        $this->assertDatabaseHas('exam_sessions', [
            'id' => $session->id,
            'status' => ExamSession::SELESAI,
            'score' => 10,
        ]);
    }

    public function test_other_student_cannot_record_violation(): void
    {
        [$studentUser, $student] = $this->makeStudent();
        [, $otherStudent] = $this->makeStudent();
        $exam = $this->makeExam($student->class_id);
        $session = ExamSession::factory()->create([
            'exam_id' => $exam->id,
            'student_id' => $student->id,
        ]);

        $this->actingAs(User::find($otherStudent->user_id))
            ->postJson(route('siswa.exams.violations', $session), [
                'type' => 'pindah_tab',
                'duration_seconds' => 0,
            ])
            ->assertForbidden();
    }

    public function test_teacher_can_unlock_add_time_warn_and_disqualify(): void
    {
        $guru = User::factory()->guru()->create();
        [$studentUser, $student] = $this->makeStudent();
        $exam = $this->makeExam($student->class_id, ['max_violations' => 1]);
        $session = ExamSession::factory()->create([
            'exam_id' => $exam->id,
            'student_id' => $student->id,
            'status' => ExamSession::TERKUNCI,
            'violation_count' => 1,
        ]);
        Violation::factory()->create(['session_id' => $session->id]);

        $this->actingAs($guru)
            ->post(route('guru.monitoring.unlock', $session))
            ->assertOk();

        $this->assertDatabaseHas('exam_sessions', [
            'id' => $session->id,
            'status' => ExamSession::BERLANGSUNG,
        ]);

        $startedAt = $session->fresh()->started_at;

        $this->actingAs($guru)
            ->post(route('guru.monitoring.add-time', $session), ['minutes' => 5])
            ->assertOk();

        $this->assertTrue($session->fresh()->started_at->lt($startedAt));

        $this->actingAs($guru)
            ->post(route('guru.monitoring.warn', $session), ['note' => 'Fokus kembali'])
            ->assertOk();

        $this->assertDatabaseHas('violations', [
            'session_id' => $session->id,
            'type' => 'peringatan_guru',
            'action' => 'peringatan',
        ]);

        $this->actingAs($guru)
            ->post(route('guru.monitoring.disqualify', $session))
            ->assertOk();

        $this->assertDatabaseHas('exam_sessions', [
            'id' => $session->id,
            'status' => ExamSession::DISKUALIFIKASI,
        ]);
    }

    public function test_monitoring_data_returns_alerts_and_summary(): void
    {
        $guru = User::factory()->guru()->create();
        [$studentUser, $student] = $this->makeStudent();
        $exam = $this->makeExam($student->class_id);
        $session = ExamSession::factory()->create([
            'exam_id' => $exam->id,
            'student_id' => $student->id,
            'status' => ExamSession::TERKUNCI,
            'violation_count' => 1,
            'last_seen_at' => now(),
        ]);
        Violation::factory()->create(['session_id' => $session->id]);

        $response = $this->actingAs($guru)
            ->getJson(route('guru.monitoring.data'))
            ->assertOk();

        $response->assertJsonPath('summary.locked', 1);
        $response->assertJsonPath('summary.problematic', 1);
        $this->assertNotEmpty($response->json('alerts'));
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
