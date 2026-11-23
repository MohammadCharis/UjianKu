<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class StudentsTemplateExport implements FromCollection, WithHeadings
{
    public function collection(): Collection
    {
        return collect([
            ['Contoh Siswa', 'siswa@contoh.test', '20240999', 'X IPA 1'],
        ]);
    }

    public function headings(): array
    {
        return ['nama', 'email', 'nis', 'kelas'];
    }
}
