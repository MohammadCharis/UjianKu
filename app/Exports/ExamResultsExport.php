<?php

namespace App\Exports;

use App\Models\ExamSession;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ExamResultsExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    public function __construct(private Collection $sessions)
    {
    }

    public function collection(): Collection
    {
        return $this->sessions;
    }

    public function headings(): array
    {
        return ['Nama Siswa', 'NIS', 'Kelas', 'Ujian', 'Mapel', 'Nilai', 'Status', 'Pelanggaran', 'Selesai'];
    }

    public function map($session): array
    {
        return [
            $session->student?->user?->name,
            $session->student?->nis,
            $session->student?->schoolClass?->name,
            $session->exam?->title,
            $session->exam?->subject?->name,
            $session->score,
            $session->status,
            $session->violation_count,
            $session->finished_at?->format('Y-m-d H:i:s'),
        ];
    }
}
