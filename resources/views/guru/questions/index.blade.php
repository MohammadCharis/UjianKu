<x-app-layout>
    <x-slot name="header">Bank Soal</x-slot>

    @if (session('status'))
        <x-alert type="success" dismissible class="mb-4">{{ session('status') }}</x-alert>
    @endif

    <x-card>
        <div class="mb-4 flex justify-end">
            <x-button :href="route('guru.questions.create')">Tambah Soal</x-button>
        </div>

        <x-responsive-table>
            <thead class="bg-slate-50 text-xs uppercase text-slate-500">
                <tr>
                    <th class="px-3 py-3">Soal</th>
                    <th class="px-3 py-3">Mapel</th>
                    <th class="px-3 py-3">Tipe</th>
                    <th class="px-3 py-3 text-end">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($questions as $question)
                    <tr>
                        <td class="px-3 py-3">
                            <p class="max-w-md truncate font-medium">{{ $question->body }}</p>
                            @if ($question->image)
                                <img src="{{ asset('storage/'.$question->image) }}" alt="Gambar soal" class="mt-2 h-16 w-16 rounded-lg object-cover">
                            @endif
                        </td>
                        <td class="px-3 py-3">{{ $question->subject?->name }}</td>
                        <td class="px-3 py-3"><span class="rounded-full bg-slate-100 px-2 py-1 text-xs font-semibold uppercase">{{ $question->type }}</span></td>
                        <td class="px-3 py-3">
                            <div class="flex justify-end gap-2">
                                <x-button size="sm" variant="secondary" :href="route('guru.questions.edit', $question)">Edit</x-button>
                                <form method="POST" action="{{ route('guru.questions.destroy', $question) }}" onsubmit="return confirm('Hapus soal ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <x-button size="sm" variant="danger" type="submit">Hapus</x-button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-3 py-6 text-center text-slate-500">Belum ada soal.</td></tr>
                @endforelse
            </tbody>
        </x-responsive-table>

        <div class="mt-4">{{ $questions->links() }}</div>
    </x-card>
</x-app-layout>
