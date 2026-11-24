<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Http\Requests\Guru\ScoreEssayRequest;
use App\Models\Answer;
use App\Models\ExamSession;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class GradingController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();

        $sessions = ExamSession::query()
            ->with(['exam.subject', 'student.user'])
            ->where('status', ExamSession::SELESAI)
            ->when(! $user?->isAdmin(), fn ($query) => $query->whereHas('exam', fn ($examQuery) => $examQuery->where('created_by', $user?->id)))
            ->latest('finished_at')
            ->paginate(15);

        return view('guru.grading.index', compact('sessions'));
    }

    public function edit(ExamSession $examSession): View
    {
        Gate::authorize('grade', $examSession);

        $examSession->load(['exam.questions', 'answers.option']);

        return view('guru.grading.edit', compact('examSession'));
    }

    public function update(ScoreEssayRequest $request, ExamSession $examSession): RedirectResponse
    {
        Gate::authorize('grade', $examSession);

        $scores = $request->validated('scores', []);

        DB::transaction(function () use ($examSession, $scores): void {
            foreach ($scores as $answerId => $score) {
                $answer = Answer::query()
                    ->where('id', $answerId)
                    ->where('session_id', $examSession->id)
                    ->first();

                if ($answer) {
                    $answer->update(['score' => $score === null ? null : (float) $score]);
                }
            }

            $examSession->update(['score' => $examSession->answers()->sum('score')]);
        });

        return redirect()->route('guru.grading.index')->with('status', 'Nilai esai berhasil disimpan.');
    }
}
