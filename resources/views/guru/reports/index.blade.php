<x-app-layout>
    <x-slot name="header">Laporan Nilai & Pelanggaran</x-slot>

    @php
        $routePrefix = request()->routeIs('admin.*') ? 'admin' : 'guru';
        $queryString = request()->getQueryString();
    @endphp

    @if (session('status'))
        <x-alert type="success" dismissible class="mb-4">{{ session('status') }}</x-alert>
    @endif

    <x-card>
        <form method="GET" action="{{ route($routePrefix.'.reports.index') }}" class="grid gap-3 md:grid-cols-4">
            <div>
                <x-input-label for="exam_id" value="Ujian" />
                <select id="exam_id" name="exam_id" class="mt-1 min-h-11 w-full rounded-xl border-slate-300 text-sm">
                    <option value="">Semua ujian</option>
                    @foreach ($exams as $exam)
                        <option value="{{ $exam->id }}" @selected(($filters['exam_id'] ?? '') == $exam->id)>{{ $exam->title }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <x-input-label for="class_id" value="Kelas" />
                <select id="class_id" name="class_id" class="mt-1 min-h-11 w-full rounded-xl border-slate-300 text-sm">
                    <option value="">Semua kelas</option>
                    @foreach ($classes as $class)
                        <option value="{{ $class->id }}" @selected(($filters['class_id'] ?? '') == $class->id)>{{ $class->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <x-input-label for="status" value="Status" />
                <select id="status" name="status" class="mt-1 min-h-11 w-full rounded-xl border-slate-300 text-sm">
                    <option value="">Semua status</option>
                    @foreach (['berlangsung', 'terkunci', 'selesai', 'diskualifikasi'] as $status)
                        <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ $status }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-end gap-2">
                <x-button type="submit">Filter</x-button>
                <x-button variant="secondary" :href="route($routePrefix.'.reports.index')">Reset</x-button>
            </div>
        </form>

        <div class="mt-4 flex flex-wrap gap-2">
            <x-button size="sm" :href="route($routePrefix.'.reports.export.excel').($queryString ? '?'.$queryString : '')">Export Excel Nilai</x-button>
            <x-button size="sm" variant="secondary" :href="route($routePrefix.'.reports.export.pdf').($queryString ? '?'.$queryString : '')">Export PDF Nilai</x-button>
            <x-button size="sm" variant="secondary" :href="route($routePrefix.'.reports.export.violations').($queryString ? '?'.$queryString : '')">Export Excel Pelanggaran</x-button>
        </div>
    </x-card>

    <x-card>
        <x-responsive-table>
            <thead class="bg-slate-50 text-xs uppercase text-slate-500">
                <tr>
                    <th class="px-3 py-3">Siswa</th>
                    <th class="px-3 py-3">Kelas</th>
                    <th class="px-3 py-3">Ujian</th>
                    <th class="px-3 py-3">Nilai</th>
                    <th class="px-3 py-3">Status</th>
                    <th class="px-3 py-3">Pelanggaran</th>
                    <th class="px-3 py-3">Selesai</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($sessions as $session)
                    <tr>
                        <td class="px-3 py-3 font-medium">{{ $session->student?->user?->name }}</td>
                        <td class="px-3 py-3">{{ $session->student?->schoolClass?->name }}</td>
                        <td class="px-3 py-3">{{ $session->exam?->title }}</td>
                        <td class="px-3 py-3">{{ $session->score ?? 0 }}</td>
                        <td class="px-3 py-3 capitalize">{{ $session->status }}</td>
                        <td class="px-3 py-3">{{ $session->violation_count }}</td>
                        <td class="px-3 py-3 text-xs text-slate-500">{{ $session->finished_at?->format('d M H:i') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-3 py-6 text-center text-slate-500">Belum ada data laporan.</td></tr>
                @endforelse
            </tbody>
        </x-responsive-table>

        <div class="mt-4">{{ $sessions->links() }}</div>
    </x-card>
</x-app-layout>
