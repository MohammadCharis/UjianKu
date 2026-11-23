<x-app-layout>
    <x-slot name="header">Daftar Ujian</x-slot>

    @if (session('status'))
        <x-alert type="success" dismissible class="mb-4">{{ session('status') }}</x-alert>
    @endif

    <x-card>
        <div class="mb-4 flex justify-end">
            <x-button :href="route('guru.exams.create')">Buat Ujian</x-button>
        </div>

        <x-responsive-table>
            <thead class="bg-slate-50 text-xs uppercase text-slate-500">
                <tr>
                    <th class="px-3 py-3">Judul</th>
                    <th class="px-3 py-3">Mapel</th>
                    <th class="px-3 py-3">Token</th>
                    <th class="px-3 py-3">Jadwal</th>
                    <th class="px-3 py-3 text-end">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($exams as $exam)
                    <tr>
                        <td class="px-3 py-3 font-medium">{{ $exam->title }}</td>
                        <td class="px-3 py-3">{{ $exam->subject?->name }}</td>
                        <td class="px-3 py-3"><code>{{ $exam->token }}</code></td>
                        <td class="px-3 py-3 text-xs text-slate-600">
                            {{ $exam->start_at?->format('d M H:i') }} – {{ $exam->end_at?->format('d M H:i') }}
                        </td>
                        <td class="px-3 py-3">
                            <div class="flex justify-end gap-2">
                                <x-button size="sm" variant="secondary" :href="route('guru.exams.edit', $exam)">Edit</x-button>
                                <form method="POST" action="{{ route('guru.exams.destroy', $exam) }}" onsubmit="return confirm('Hapus ujian ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <x-button size="sm" variant="danger" type="submit">Hapus</x-button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-3 py-6 text-center text-slate-500">Belum ada ujian.</td></tr>
                @endforelse
            </tbody>
        </x-responsive-table>

        <div class="mt-4">{{ $exams->links() }}</div>
    </x-card>
</x-app-layout>
