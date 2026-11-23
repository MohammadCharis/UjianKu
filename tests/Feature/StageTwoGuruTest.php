<?php

namespace Tests\Feature;

use App\Models\Exam;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StageTwoGuruTest extends TestCase
{
    use RefreshDatabase;

    public function test_question_policy_allows_only_owner(): void
    {
        $owner = User::factory()->guru()->create();
        $other = User::factory()->guru()->create();
        $question = Question::factory()->create(['created_by' => $owner->id]);

        $this->assertTrue(Gate::forUser($owner)->allows('update', $question));
        $this->assertFalse(Gate::forUser($other)->allows('update', $question));

        $this->actingAs($other)
            ->get(route('guru.questions.edit', $question))
            ->assertForbidden();
    }

    public function test_guru_can_store_question_with_image_and_one_correct_option(): void
    {
        Storage::fake('public');

        $guru = User::factory()->guru()->create();
        $subject = Subject::factory()->create();

        $response = $this->actingAs($guru)->post(route('guru.questions.store'), [
            'subject_id' => $subject->id,
            'type' => 'pg',
            'body' => 'Soal dengan gambar',
            'image' => UploadedFile::fake()->image('soal.jpg', 120, 120),
            'options' => [
                ['label' => 'A', 'text' => 'Satu', 'is_correct' => 1],
                ['label' => 'B', 'text' => 'Dua', 'is_correct' => 0],
                ['label' => 'C', 'text' => 'Tiga', 'is_correct' => 0],
                ['label' => 'D', 'text' => 'Empat', 'is_correct' => 0],
            ],
        ]);

        $response->assertRedirect(route('guru.questions.index'));

        $question = Question::where('body', 'Soal dengan gambar')->firstOrFail();

        $this->assertSame($guru->id, $question->created_by);
        $this->assertSame(4, QuestionOption::where('question_id', $question->id)->count());
        $this->assertSame(1, QuestionOption::where('question_id', $question->id)->where('is_correct', true)->count());
        Storage::disk('public')->assertExists($question->image);
    }

    public function test_question_requires_exactly_one_correct_option(): void
    {
        $guru = User::factory()->guru()->create();
        $subject = Subject::factory()->create();

        $this->actingAs($guru)
            ->post(route('guru.questions.store'), [
                'subject_id' => $subject->id,
                'type' => 'pg',
                'body' => 'Soal dua kunci',
                'options' => [
                    ['label' => 'A', 'text' => 'Satu', 'is_correct' => 1],
                    ['label' => 'B', 'text' => 'Dua', 'is_correct' => 1],
                ],
            ])
            ->assertSessionHasErrors('options');

        $this->assertDatabaseMissing('questions', ['body' => 'Soal dua kunci']);
    }

    public function test_guru_can_create_exam_with_classes_and_questions(): void
    {
        $guru = User::factory()->guru()->create();
        $subject = Subject::factory()->create();
        $class = SchoolClass::factory()->create();
        $questions = Question::factory()->count(2)->create([
            'subject_id' => $subject->id,
            'created_by' => $guru->id,
        ]);

        $this->actingAs($guru)
            ->post(route('guru.exams.store'), [
                'subject_id' => $subject->id,
                'title' => 'Ujian Tengah Semester',
                'token' => 'UTS123',
                'start_at' => now()->subHour()->format('Y-m-d\TH:i'),
                'end_at' => now()->addHour()->format('Y-m-d\TH:i'),
                'duration_min' => 60,
                'max_violations' => 3,
                'shuffle' => 1,
                'lock_mode' => Exam::LOCK,
                'class_ids' => [$class->id],
                'questions' => [
                    ['id' => $questions[0]->id, 'points' => 10],
                    ['id' => $questions[1]->id, 'points' => 20],
                ],
            ])
            ->assertRedirect(route('guru.exams.index'));

        $exam = Exam::where('token', 'UTS123')->firstOrFail();

        $this->assertTrue((bool) $exam->shuffle);
        $this->assertDatabaseHas('exam_classes', ['exam_id' => $exam->id, 'class_id' => $class->id]);
        $this->assertSame(2, $exam->examQuestions()->count());
        $this->assertDatabaseHas('exam_questions', ['exam_id' => $exam->id, 'question_id' => $questions[1]->id, 'points' => 20]);
    }

    public function test_exam_rejects_question_from_other_subject(): void
    {
        $guru = User::factory()->guru()->create();
        $subject = Subject::factory()->create();
        $class = SchoolClass::factory()->create();
        $otherQuestion = Question::factory()->create();

        $this->actingAs($guru)
            ->post(route('guru.exams.store'), [
                'subject_id' => $subject->id,
                'title' => 'Ujian Salah Mapel',
                'token' => 'SALAH1',
                'start_at' => now()->subHour()->format('Y-m-d\TH:i'),
                'end_at' => now()->addHour()->format('Y-m-d\TH:i'),
                'duration_min' => 60,
                'max_violations' => 3,
                'lock_mode' => Exam::LOCK,
                'class_ids' => [$class->id],
                'questions' => [['id' => $otherQuestion->id, 'points' => 10]],
            ])
            ->assertSessionHasErrors('questions');
    }

    public function test_exam_policy_allows_only_owner(): void
    {
        $owner = User::factory()->guru()->create();
        $other = User::factory()->guru()->create();
        $exam = Exam::factory()->create(['created_by' => $owner->id]);

        $this->assertTrue(Gate::forUser($owner)->allows('update', $exam));
        $this->assertFalse(Gate::forUser($other)->allows('update', $exam));

        $this->actingAs($other)
            ->get(route('guru.exams.edit', $exam))
            ->assertForbidden();
    }
}
