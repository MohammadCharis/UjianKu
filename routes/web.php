<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\SchoolClassController as AdminSchoolClassController;
use App\Http\Controllers\Admin\StudentController as AdminStudentController;
use App\Http\Controllers\Admin\StudentImportController as AdminStudentImportController;
use App\Http\Controllers\Admin\SubjectController as AdminSubjectController;
use App\Http\Controllers\Admin\TeacherController as AdminTeacherController;
use App\Http\Controllers\Guru\DashboardController as GuruDashboardController;
use App\Http\Controllers\Guru\ExamController as GuruExamController;
use App\Http\Controllers\Guru\MonitoringController as GuruMonitoringController;
use App\Http\Controllers\Guru\QuestionController as GuruQuestionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Siswa\DashboardController as SiswaDashboardController;
use App\Http\Controllers\Siswa\ExamController as SiswaExamController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'verified', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function (): void {
        Route::get('/', AdminDashboardController::class)->name('index');

        Route::resource('classes', AdminSchoolClassController::class)
            ->parameters(['classes' => 'school_class'])
            ->except(['show']);
        Route::resource('subjects', AdminSubjectController::class)->except(['show']);
        Route::resource('teachers', AdminTeacherController::class)->except(['show']);

        Route::get('students/import', [AdminStudentImportController::class, 'create'])->name('students.import.create');
        Route::post('students/import', [AdminStudentImportController::class, 'store'])->name('students.import.store');
        Route::get('students/import/template', [AdminStudentImportController::class, 'template'])->name('students.import.template');
        Route::resource('students', AdminStudentController::class)->except(['show']);
    });

Route::middleware(['auth', 'verified', 'role:guru'])
    ->prefix('guru')
    ->name('guru.')
    ->group(function (): void {
        Route::get('/', GuruDashboardController::class)->name('index');

        Route::resource('questions', GuruQuestionController::class)->except(['show']);
        Route::resource('exams', GuruExamController::class)->except(['show']);

        Route::get('monitoring', [GuruMonitoringController::class, 'index'])->name('monitoring.index');
        Route::get('monitoring/data', [GuruMonitoringController::class, 'data'])->name('monitoring.data');
        Route::post('monitoring/sessions/{examSession}/unlock', [GuruMonitoringController::class, 'unlock'])->name('monitoring.unlock');
        Route::post('monitoring/sessions/{examSession}/add-time', [GuruMonitoringController::class, 'addTime'])->name('monitoring.add-time');
        Route::post('monitoring/sessions/{examSession}/warn', [GuruMonitoringController::class, 'warn'])->name('monitoring.warn');
        Route::post('monitoring/sessions/{examSession}/disqualify', [GuruMonitoringController::class, 'disqualify'])->name('monitoring.disqualify');
    });

Route::middleware(['auth', 'verified', 'role:siswa'])
    ->prefix('siswa')
    ->name('siswa.')
    ->group(function (): void {
        Route::get('/', SiswaDashboardController::class)->name('index');

        Route::get('exams', [SiswaExamController::class, 'index'])->name('exams.index');
        Route::post('exams/join', [SiswaExamController::class, 'join'])->name('exams.join');
        Route::get('exams/{examSession}/work', [SiswaExamController::class, 'work'])->name('exams.work');
        Route::post('exams/{examSession}/answers', [SiswaExamController::class, 'answer'])
            ->middleware('throttle:answers')
            ->name('exams.answers');
        Route::post('exams/{examSession}/heartbeat', [SiswaExamController::class, 'heartbeat'])->name('exams.heartbeat');
        Route::post('exams/{examSession}/violations', [SiswaExamController::class, 'recordViolation'])
            ->middleware('throttle:violations')
            ->name('exams.violations');
        Route::post('exams/{examSession}/submit', [SiswaExamController::class, 'submit'])->name('exams.submit');
        Route::get('exams/{examSession}/result', [SiswaExamController::class, 'result'])->name('exams.result');
    });

Route::middleware('auth')->group(function (): void {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
