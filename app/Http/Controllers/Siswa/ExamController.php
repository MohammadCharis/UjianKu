<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Http\Requests\Siswa\AnswerExamQuestionRequest;
use App\Http\Requests\Siswa\JoinExamRequest;
use App\Http\Requests\Siswa\SubmitExamRequest;
use App\Models\Exam;
use App\Models\ExamSession;
use App\Services\ExamGradingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ExamController extends Controller
{
    public function __construct(private ExamGradingService $gradingService) {}

    public function index(): View
    {
        $student = auth()->user()->student;

        abort_if($student === null, 403);

        $activeExams = Exam::query()
            ->whereHas('classes', fn ($query) => $query->where('classes.id', $student->class_id))
            ->where('start_at', '<=', now())
            ->where('end_at', '>=', now())
            ->with('subject')
            ->orderBy('end_at')
            ->get();

        $sessions = $student->examSessions()
            ->with('exam.subject')
            ->latest('started_at')
            ->get();

        return view('siswa.exams.index', compact('activeExams', 'sessions'));
    }

    public function join(JoinExamRequest $request): RedirectResponse
    {
        $student = auth()->user()->student;

        abort_if($student === null, 403);

        $token = $request->validated('token');
        $exam = Exam::query()->where('token', $token)->first();

        if ($exam === null) {
            return back()->withErrors(['token' => 'Token tidak ditemukan.']);
        }

        if (now()->lt($exam->start_at) || now()->gt($exam->end_at)) {
            return back()->withErrors(['token' => 'Ujian belum dimulai atau sudah berakhir.']);
        }

        if (! $exam->classes()->where('classes.id', $student->class_id)->exists()) {
            return back()->withErrors(['token' => 'Ujian tidak tersedia untuk kelas Anda.']);
        }

        $session = DB::transaction(fn (): ExamSession => ExamSession::firstOrCreate(
            ['exam_id' => $exam->id, 'student_id' => $student->id],
            [
                'started_at' => now(),
                'status' => ExamSession::BERLANGSUNG,
                'last_seen_at' => now(),
            ],
        ));

        if ($session->status !== ExamSession::BERLANGSUNG) {
            return redirect()->route('siswa.exams.result', $session)
                ->with('status', 'Sesi ujian Anda sudah ditutup.');
        }

        return redirect()->route('siswa.exams.work', $session);
    }

    public function work(ExamSession $examSession): View|RedirectResponse
    {
        Gate::authorize('view', $examSession);

        if ($examSession->status !== ExamSession::BERLANGSUNG) {
            return redirect()->route('siswa.exams.result', $examSession);
        }

        $examSession->load(['exam.subject', 'student.user']);
        $exam = $examSession->exam;

        $questions = $exam->questions()
            ->select('questions.*', 'exam_questions.order_no', 'exam_questions.points')
            ->with(['options' => fn ($query) => $query->select('id', 'question_id', 'label', 'text')->orderBy('id')])
            ->get();

        $answers = $examSession->answers()->get()->keyBy('question_id');

        return view('siswa.exams.work', [
            'session' => $examSession,
            'exam' => $exam,
            'questions' => $questions,
            'answers' => $answers,
            'remainingSeconds' => $examSession->remainingSeconds(),
            'serverNowMs' => (int) (now()->valueOf() * 1000),
        ]);
    }

    public function answer(AnswerExamQuestionRequest $request, ExamSession $examSession): JsonResponse
    {
        $data = $request->validated();

        $answer = $examSession->answers()->updateOrCreate(
            ['question_id' => $data['question_id']],
            [
                'option_id' => $data['option_id'] ?? null,
                'answer_text' => $data['answer_text'] ?? null,
                'is_doubtful' => $request->boolean('is_doubtful'),
            ],
        );

        return response()->json([
            'status' => 'saved',
            'question_id' => $answer->question_id,
            'saved_at' => $answer->updated_at?->toIso8601String(),
        ]);
    }

    public function heartbeat(ExamSession $examSession): JsonResponse
    {
        Gate::authorize('heartbeat', $examSession);

        $examSession->update(['last_seen_at' => now()]);

        return response()->json([
            'status' => 'ok',
            'server_now_ms' => (int) (now()->valueOf() * 1000),
            'remaining_seconds' => $examSession->remainingSeconds(),
        ]);
    }

    public function submit(SubmitExamRequest $request, ExamSession $examSession): JsonResponse|RedirectResponse
    {
        $this->gradingService->grade($examSession);

        $examSession->update([
            'status' => ExamSession::SELESAI,
            'finished_at' => now(),
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'submitted',
                'redirect' => route('siswa.exams.result', $examSession),
            ]);
        }

        return redirect()->route('siswa.exams.result', $examSession)
            ->with('status', 'Jawaban berhasil dikumpulkan.');
    }

    public function result(ExamSession $examSession): View
    {
        Gate::authorize('view', $examSession);

        $examSession->load(['exam.subject']);
        $exam = $examSession->exam;

        $questions = $exam->questions()
            ->select('questions.*', 'exam_questions.order_no', 'exam_questions.points')
            ->get();

        $answers = $examSession->answers()->with('option')->get()->keyBy('question_id');

        return view('siswa.exams.result', compact('examSession', 'exam', 'questions', 'answers'));
    }
}
