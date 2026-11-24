<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\ExamSession;
use App\Models\SchoolClass;
use App\Models\Violation;
use App\Services\ExamGradingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MonitoringController extends Controller
{
    public function __construct(private ExamGradingService $gradingService)
    {
    }

    public function index(): View
    {
        return view('guru.monitoring.index', [
            'classes' => SchoolClass::query()->orderBy('grade')->orderBy('name')->get(),
        ]);
    }

    public function data(Request $request): JsonResponse
    {
        $classId = $request->integer('class_id') ?: null;
        $onlyProblematic = $request->boolean('only_problematic');
        $onlyLocked = $request->boolean('only_locked');

        $violations = Violation::query()
            ->with(['session.student.user', 'session.student.schoolClass', 'session.exam'])
            ->whereHas('session', fn ($query) => $query->whereIn('status', [ExamSession::BERLANGSUNG, ExamSession::TERKUNCI]))
            ->when($classId, fn ($query) => $query->whereHas('session.student', fn ($studentQuery) => $studentQuery->where('class_id', $classId)))
            ->when($onlyProblematic, fn ($query) => $query->whereHas('session', fn ($sessionQuery) => $sessionQuery->where('violation_count', '>', 0)))
            ->when($onlyLocked, fn ($query) => $query->whereHas('session', fn ($sessionQuery) => $sessionQuery->where('status', ExamSession::TERKUNCI)))
            ->latest('occurred_at')
            ->limit(50)
            ->get();

        foreach ($violations as $violation) {
            $violation->violation_number = Violation::query()
                ->where('session_id', $violation->session_id)
                ->where('occurred_at', '<=', $violation->occurred_at)
                ->count();
        }

        $activeSessions = ExamSession::query()
            ->whereIn('status', [ExamSession::BERLANGSUNG, ExamSession::TERKUNCI])
            ->when($classId, fn ($query) => $query->whereHas('student', fn ($studentQuery) => $studentQuery->where('class_id', $classId)))
            ->with('student')
            ->get();

        return response()->json([
            'summary' => [
                'active_sessions' => $activeSessions->count(),
                'online' => $activeSessions->filter(fn (ExamSession $session): bool => $session->last_seen_at?->gt(now()->subSeconds(30)) ?? false)->count(),
                'locked' => $activeSessions->where('status', ExamSession::TERKUNCI)->count(),
                'problematic' => $activeSessions->where('violation_count', '>', 0)->count(),
            ],
            'alerts' => $violations->map(fn (Violation $violation): array => [
                'id' => $violation->id,
                'session_id' => $violation->session_id,
                'student_name' => $violation->session?->student?->user?->name ?? '-',
                'nis' => $violation->session?->student?->nis ?? '-',
                'class_name' => $violation->session?->student?->schoolClass?->name ?? '-',
                'exam_title' => $violation->session?->exam?->title ?? '-',
                'type' => $violation->type,
                'level' => $violation->level,
                'duration_seconds' => $violation->duration_seconds,
                'occurred_at' => $violation->occurred_at?->format('d M H:i:s'),
                'violation_number' => $violation->violation_number,
                'session_status' => $violation->session?->status,
                'max_violations' => $violation->session?->exam?->max_violations,
                'handled' => $violation->handled_by !== null,
                'action' => $violation->action,
            ])->values(),
        ]);
    }

    public function unlock(ExamSession $examSession): JsonResponse
    {
        $this->ensureGuru();

        $examSession->update([
            'status' => ExamSession::BERLANGSUNG,
            'last_seen_at' => now(),
        ]);

        $this->logAction($examSession, 'buka_kunci');

        return response()->json(['status' => 'unlocked']);
    }

    public function addTime(Request $request, ExamSession $examSession): JsonResponse
    {
        $this->ensureGuru();

        $minutes = (int) $request->validate([
            'minutes' => ['required', 'integer', 'min:1', 'max:60'],
        ])['minutes'];

        $examSession->started_at = $examSession->started_at->subMinutes($minutes);
        $examSession->save();

        $this->logAction($examSession, 'tambah_waktu', $minutes.' menit');

        return response()->json(['status' => 'time_added', 'minutes' => $minutes]);
    }

    public function warn(Request $request, ExamSession $examSession): JsonResponse
    {
        $this->ensureGuru();

        $note = $request->validate([
            'note' => ['nullable', 'string', 'max:255'],
        ])['note'] ?? null;

        Violation::create([
            'session_id' => $examSession->id,
            'handled_by' => auth()->id(),
            'type' => 'peringatan_guru',
            'level' => Violation::RINGAN,
            'occurred_at' => now(),
            'duration_seconds' => 0,
            'action' => 'peringatan',
            'note' => $note,
        ]);

        return response()->json(['status' => 'warned']);
    }

    public function disqualify(ExamSession $examSession): JsonResponse
    {
        $this->ensureGuru();

        $examSession->update([
            'status' => ExamSession::DISKUALIFIKASI,
            'finished_at' => now(),
        ]);

        $this->gradingService->grade($examSession);
        $this->logAction($examSession, 'diskualifikasi');

        return response()->json(['status' => 'disqualified']);
    }

    private function ensureGuru(): void
    {
        $user = auth()->user();

        if (! ($user?->isGuru() || $user?->isAdmin())) {
            abort(403);
        }
    }

    private function logAction(ExamSession $session, string $action, ?string $note = null): void
    {
        $violation = $session->violations()->latest()->first();

        if ($violation) {
            $violation->update([
                'handled_by' => auth()->id(),
                'action' => $action,
                'note' => $note ?? $violation->note,
            ]);

            return;
        }

        Violation::create([
            'session_id' => $session->id,
            'handled_by' => auth()->id(),
            'type' => 'tindakan_guru',
            'level' => Violation::RINGAN,
            'occurred_at' => now(),
            'duration_seconds' => 0,
            'action' => $action,
            'note' => $note,
        ]);
    }
}
