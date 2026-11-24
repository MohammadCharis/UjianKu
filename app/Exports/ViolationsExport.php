<?php

namespace App\Exports;

use App\Models\Violation;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ViolationsExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    public function __construct(private Collection $violations)
    {
    }

    public function collection(): Collection
    {
        return $this->violations;
    }

    public function headings(): array
    {
        return ['Siswa', 'NIS', 'Kelas', 'Ujian', 'Jenis', 'Level', 'Durasi (detik)', 'Waktu', 'Status Sesi', 'Tindakan'];
    }

    public function map($violation): array
    {
        return [
            $violation->session?->student?->user?->name,
            $violation->session?->student?->nis,
            $violation->session?->student?->schoolClass?->name,
            $violation->session?->exam?->title,
            $violation->type,
            $violation->level,
            $violation->duration_seconds,
            $violation->occurred_at?->format('Y-m-d H:i:s'),
            $violation->session?->status,
            $violation->action,
        ];
    }
}
