<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreSchoolClassRequest;
use App\Http\Requests\Admin\UpdateSchoolClassRequest;
use App\Models\SchoolClass;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SchoolClassController extends Controller
{
    public function index(): View
    {
        return view('admin.classes.index', [
            'classes' => SchoolClass::query()->orderBy('grade')->orderBy('name')->paginate(15),
        ]);
    }

    public function create(): View
    {
        return view('admin.classes.create');
    }

    public function store(StoreSchoolClassRequest $request): RedirectResponse
    {
        SchoolClass::create($request->validated());

        return redirect()->route('admin.classes.index')->with('status', 'Kelas berhasil ditambahkan.');
    }

    public function edit(SchoolClass $schoolClass): View
    {
        return view('admin.classes.edit', compact('schoolClass'));
    }

    public function update(UpdateSchoolClassRequest $request, SchoolClass $schoolClass): RedirectResponse
    {
        $schoolClass->update($request->validated());

        return redirect()->route('admin.classes.index')->with('status', 'Kelas berhasil diperbarui.');
    }

    public function destroy(SchoolClass $schoolClass): RedirectResponse
    {
        $schoolClass->delete();

        return redirect()->route('admin.classes.index')->with('status', 'Kelas berhasil dihapus.');
    }
}
