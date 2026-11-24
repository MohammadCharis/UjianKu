<x-app-layout>
    <x-slot name="header">Koreksi Esai</x-slot>

    @if (session('status'))
        <x-alert type="success" dismissible class="mb-4">{{ session('status') }}</x-alert>
    @endif

    <x-card>
        <x-responsive-table>
            <thead class="bg-slate-50 text-xs uppercase text-slate-500">
                <tr>
                    <th class="px-3 py-3">Siswa</th>
                    <th class="px-3 py-3">Ujian</th>
                    <th class="px-3 py-3">Nilai Sementara</th>
                    <th class="px-3 py-3">Selesai</th>
                    <th class="px-3 py-3 text-end">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($sessions as $session)
                    <tr>
                        <td class="px-3 py-3 font-medium">{{ $session->student?->user?->name }}</td>
                        <td class="px-3 py-3">{{ $session->exam?->title }}</td>
                        <td class="px-3 py-3">{{ $session->score ?? 0 }}</td>
                        <td class="px-3 py-3 text-xs text-slate-500">{{ $session->finished_at?->format('d M H:i') }}</td>
                        <td class="px-3 py-3 text-end">
                            <x-button size="sm" :href="route('guru.grading.edit', $session)">Koreksi</x-button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-3 py-6 text-center text-slate-500">Belum ada ujian selesai yang perlu dikoreksi.</td></tr>
                @endforelse
            </tbody>
        </x-responsive-table>

        <div class="mt-4">{{ $sessions->links() }}</div>
    </x-card>
</x-app-layout>
