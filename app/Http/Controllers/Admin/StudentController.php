<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreStudentRequest;
use App\Http\Requests\Admin\UpdateStudentRequest;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class StudentController extends Controller
{
    public function index(): View
    {
        return view('admin.students.index', [
            'students' => Student::query()
                ->with(['user', 'schoolClass'])
                ->orderBy('nis')
                ->paginate(15),
        ]);
    }

    public function create(): View
    {
        return view('admin.students.create', [
            'classes' => SchoolClass::query()->orderBy('grade')->orderBy('name')->get(),
        ]);
    }

    public function store(StoreStudentRequest $request): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($data): void {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'] ?? 'password',
                'role' => User::SISWA,
            ]);

            Student::create([
                'user_id' => $user->id,
                'class_id' => $data['class_id'],
                'nis' => $data['nis'],
            ]);
        });

        return redirect()->route('admin.students.index')->with('status', 'Siswa berhasil ditambahkan.');
    }

    public function edit(Student $student): View
    {
        return view('admin.students.edit', [
            'student' => $student->load('user'),
            'classes' => SchoolClass::query()->orderBy('grade')->orderBy('name')->get(),
        ]);
    }

    public function update(UpdateStudentRequest $request, Student $student): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($data, $request, $student): void {
            $userData = [
                'name' => $data['name'],
                'email' => $data['email'],
            ];

            if ($request->filled('password')) {
                $userData['password'] = $data['password'];
            }

            $student->user->update($userData);
            $student->update([
                'class_id' => $data['class_id'],
                'nis' => $data['nis'],
            ]);
        });

        return redirect()->route('admin.students.index')->with('status', 'Siswa berhasil diperbarui.');
    }

    public function destroy(Student $student): RedirectResponse
    {
        DB::transaction(function () use ($student): void {
            $user = $student->user;
            $student->delete();

            if ($user?->isSiswa()) {
                $user->delete();
            }
        });

        return redirect()->route('admin.students.index')->with('status', 'Siswa berhasil dihapus.');
    }
}
