<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Http\Requests\Guru\StoreQuestionRequest;
use App\Http\Requests\Guru\UpdateQuestionRequest;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\Subject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class QuestionController extends Controller
{
    public function index(): View
    {
        Gate::authorize('viewAny', Question::class);

        $query = Question::query()->with(['subject', 'options']);

        if (! auth()->user()?->isAdmin()) {
            $query->where('created_by', auth()->id());
        }

        return view('guru.questions.index', [
            'questions' => $query->latest()->paginate(15),
        ]);
    }

    public function create(): View
    {
        Gate::authorize('create', Question::class);

        return view('guru.questions.create', [
            'subjects' => Subject::query()->orderBy('name')->get(),
        ]);
    }

    public function store(StoreQuestionRequest $request): RedirectResponse
    {
        $data = $request->safe()->only(['subject_id', 'type', 'body']);
        $data['created_by'] = auth()->id();

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('questions', 'public');
        }

        DB::transaction(function () use ($data, $request): void {
            $question = Question::create($data);
            $this->storeOptions($question, $request->input('options', []));
        });

        return redirect()->route('guru.questions.index')->with('status', 'Soal berhasil disimpan.');
    }

    public function edit(Question $question): View
    {
        Gate::authorize('update', $question);

        return view('guru.questions.edit', [
            'question' => $question->load('options'),
            'subjects' => Subject::query()->orderBy('name')->get(),
        ]);
    }

    public function update(UpdateQuestionRequest $request, Question $question): RedirectResponse
    {
        $data = $request->safe()->only(['subject_id', 'type', 'body']);

        if ($request->hasFile('image')) {
            if ($question->image) {
                Storage::disk('public')->delete($question->image);
            }

            $data['image'] = $request->file('image')->store('questions', 'public');
        }

        DB::transaction(function () use ($data, $request, $question): void {
            $question->update($data);
            $question->options()->delete();
            $this->storeOptions($question, $request->input('options', []));
        });

        return redirect()->route('guru.questions.index')->with('status', 'Soal berhasil diperbarui.');
    }

    public function destroy(Question $question): RedirectResponse
    {
        Gate::authorize('delete', $question);

        if ($question->image) {
            Storage::disk('public')->delete($question->image);
        }

        $question->delete();

        return redirect()->route('guru.questions.index')->with('status', 'Soal berhasil dihapus.');
    }

    private function storeOptions(Question $question, array $options): void
    {
        if ($question->type !== 'pg') {
            return;
        }

        foreach (array_values($options) as $index => $option) {
            QuestionOption::create([
                'question_id' => $question->id,
                'label' => $option['label'] ?? chr(65 + $index),
                'text' => $option['text'] ?? '',
                'is_correct' => filter_var($option['is_correct'] ?? false, FILTER_VALIDATE_BOOLEAN),
            ]);
        }
    }
}
