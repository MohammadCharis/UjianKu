<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Http\Requests\Guru\StoreExamRequest;
use App\Http\Requests\Guru\UpdateExamRequest;
use App\Models\Exam;
use App\Models\ExamQuestion;
use App\Models\Question;
use App\Models\SchoolClass;
use App\Models\Subject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ExamController extends Controller
{
    public function index(): View
    {
        Gate::authorize('viewAny', Exam::class);

        $query = Exam::query()->with('subject');

        if (! auth()->user()?->isAdmin()) {
            $query->where('created_by', auth()->id());
        }

        return view('guru.exams.index', [
            'exams' => $query->latest('start_at')->paginate(15),
        ]);
    }

    public function create(): View
    {
        Gate::authorize('create', Exam::class);

        return view('guru.exams.create', $this->formData());
    }

    public function store(StoreExamRequest $request): RedirectResponse
    {
        $data = $request->safe()->only([
            'subject_id',
            'title',
            'token',
            'start_at',
            'end_at',
            'duration_min',
            'max_violations',
            'lock_mode',
        ]);
        $data['created_by'] = auth()->id();
        $data['shuffle'] = $request->boolean('shuffle');

        DB::transaction(function () use ($data, $request): void {
            $exam = Exam::create($data);
            $exam->classes()->sync($request->input('class_ids', []));
            $this->storeExamQuestions($exam, $request->input('questions', []));
        });

        return redirect()->route('guru.exams.index')->with('status', 'Ujian berhasil dibuat.');
    }

    public function edit(Exam $exam): View
    {
        Gate::authorize('update', $exam);

        $exam->load(['classes', 'examQuestions']);

        return view('guru.exams.edit', array_merge($this->formData(), compact('exam')));
    }

    public function update(UpdateExamRequest $request, Exam $exam): RedirectResponse
    {
        $data = $request->safe()->only([
            'subject_id',
            'title',
            'token',
            'start_at',
            'end_at',
            'duration_min',
            'max_violations',
            'lock_mode',
        ]);
        $data['shuffle'] = $request->boolean('shuffle');

        DB::transaction(function () use ($data, $request, $exam): void {
            $exam->update($data);
            $exam->classes()->sync($request->input('class_ids', []));
            $exam->examQuestions()->delete();
            $this->storeExamQuestions($exam, $request->input('questions', []));
        });

        return redirect()->route('guru.exams.index')->with('status', 'Ujian berhasil diperbarui.');
    }

    public function destroy(Exam $exam): RedirectResponse
    {
        Gate::authorize('delete', $exam);

        $exam->delete();

        return redirect()->route('guru.exams.index')->with('status', 'Ujian berhasil dihapus.');
    }

    private function formData(): array
    {
        return [
            'subjects' => Subject::query()->orderBy('name')->get(),
            'classes' => SchoolClass::query()->orderBy('grade')->orderBy('name')->get(),
            'questions' => Question::query()->with('subject')->orderBy('subject_id')->orderBy('id')->get(),
        ];
    }

    private function storeExamQuestions(Exam $exam, array $questions): void
    {
        $rows = collect(array_values($questions))
            ->map(fn (array $question, int $index): array => [
                'exam_id' => $exam->id,
                'question_id' => $question['id'],
                'order_no' => $index + 1,
                'points' => $question['points'],
            ])
            ->all();

        if ($rows !== []) {
            ExamQuestion::insert($rows);
        }
    }
}
