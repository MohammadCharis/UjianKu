<?php

namespace App\Http\Controllers\Admin;

use App\Exports\StudentsTemplateExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ImportStudentsRequest;
use App\Imports\StudentsImport;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class StudentImportController extends Controller
{
    public function create(): View
    {
        return view('admin.students.import');
    }

    public function store(ImportStudentsRequest $request): RedirectResponse
    {
        Excel::import(new StudentsImport, $request->file('file'));

        return redirect()->route('admin.students.index')->with('status', 'Data siswa berhasil diimpor.');
    }

    public function template(): BinaryFileResponse
    {
        return Excel::download(new StudentsTemplateExport, 'template-siswa.xlsx');
    }
}
