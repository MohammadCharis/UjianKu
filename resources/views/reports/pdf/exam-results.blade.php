<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Nilai Ujian</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1f2937; }
        h1 { font-size: 16px; margin: 0 0 12px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #d1d5db; padding: 6px; text-align: left; }
        th { background: #f3f4f6; font-weight: 700; }
        .meta { margin-bottom: 12px; color: #6b7280; }
    </style>
</head>
<body>
    <h1>Laporan Nilai Ujian</h1>
    <p class="meta">Dicetak: {{ now()->format('d M Y H:i') }}</p>

    <table>
        <thead>
            <tr>
                <th>Siswa</th>
                <th>Kelas</th>
                <th>Ujian</th>
                <th>Nilai</th>
                <th>Status</th>
                <th>Pelanggaran</th>
                <th>Selesai</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($sessions as $session)
                <tr>
                    <td>{{ $session->student?->user?->name }}</td>
                    <td>{{ $session->student?->schoolClass?->name }}</td>
                    <td>{{ $session->exam?->title }}</td>
                    <td>{{ $session->score ?? 0 }}</td>
                    <td>{{ $session->status }}</td>
                    <td>{{ $session->violation_count }}</td>
                    <td>{{ $session->finished_at?->format('d M Y H:i') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
