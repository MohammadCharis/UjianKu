<?php

namespace App\Http\Controllers\Reports;

use App\Exports\ExamResultsExport;
use App\Exports\ViolationsExport;
use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\ExamSession;
use App\Models\SchoolClass;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        $sessions = $this->sessionsQuery($request)->paginate(15)->withQueryString();
        $exams = $this->availableExams();
        $classes = SchoolClass::query()->orderBy('grade')->orderBy('name')->get();
        $filters = $request->only(['exam_id', 'class_id', 'status']);

        return view('guru.reports.index', compact('sessions', 'exams', 'classes', 'filters'));
    }

    public function exportExcel(Request $request): BinaryFileResponse
    {
        $sessions = $this->sessionsQuery($request)->get();

        return Excel::download(new ExamResultsExport($sessions), 'laporan-nilai.xlsx');
    }

    public function exportPdf(Request $request)
    {
        $sessions = $this->sessionsQuery($request)->get();
        $filters = $request->only(['exam_id', 'class_id', 'status']);

        $pdf = Pdf::loadView('reports.pdf.exam-results', compact('sessions', 'filters'));

        return $pdf->download('laporan-nilai.pdf');
    }

    public function exportViolations(Request $request): BinaryFileResponse
    {
        $user = auth()->user();

        $violations = \App\Models\Violation::query()
            ->with(['session.student.user', 'session.student.schoolClass', 'session.exam'])
            ->when(! $user?->isAdmin(), fn ($query) => $query->whereHas('session.exam', fn ($examQuery) => $examQuery->where('created_by', $user?->id)))
            ->latest('occurred_at')
            ->get();

        return Excel::download(new ViolationsExport($violations), 'laporan-pelanggaran.xlsx');
    }

    private function sessionsQuery(Request $request)
    {
        $user = auth()->user();

        return ExamSession::query()
            ->with(['exam.subject', 'student.user', 'student.schoolClass'])
            ->when($request->filled('exam_id'), fn ($query) => $query->where('exam_id', $request->integer('exam_id')))
            ->when($request->filled('class_id'), fn ($query) => $query->whereHas('student', fn ($studentQuery) => $studentQuery->where('class_id', $request->integer('class_id'))))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->when(! $user?->isAdmin(), fn ($query) => $query->whereHas('exam', fn ($examQuery) => $examQuery->where('created_by', $user?->id)))
            ->latest('finished_at');
    }

    private function availableExams()
    {
        $user = auth()->user();

        return Exam::query()
            ->when(! $user?->isAdmin(), fn ($query) => $query->where('created_by', $user?->id))
            ->orderBy('title')
            ->get();
    }
}
