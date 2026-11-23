<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreTeacherRequest;
use App\Http\Requests\Admin\UpdateTeacherRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TeacherController extends Controller
{
    public function index(): View
    {
        return view('admin.teachers.index', [
            'teachers' => User::query()->where('role', User::GURU)->orderBy('name')->paginate(15),
        ]);
    }

    public function create(): View
    {
        return view('admin.teachers.create');
    }

    public function store(StoreTeacherRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['role'] = User::GURU;

        User::create($data);

        return redirect()->route('admin.teachers.index')->with('status', 'Guru berhasil ditambahkan.');
    }

    public function edit(User $teacher): View
    {
        return view('admin.teachers.edit', compact('teacher'));
    }

    public function update(UpdateTeacherRequest $request, User $teacher): RedirectResponse
    {
        $data = $request->safe()->only(['name', 'email']);

        if ($request->filled('password')) {
            $data['password'] = $request->input('password');
        }

        $teacher->update($data);

        return redirect()->route('admin.teachers.index')->with('status', 'Guru berhasil diperbarui.');
    }

    public function destroy(User $teacher): RedirectResponse
    {
        if ($teacher->role !== User::GURU) {
            abort(404);
        }

        $teacher->delete();

        return redirect()->route('admin.teachers.index')->with('status', 'Guru berhasil dihapus.');
    }
}
