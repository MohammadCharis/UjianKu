<x-app-layout>
    <x-slot name="header">Data Mata Pelajaran</x-slot>

    @if (session('status'))
        <x-alert type="success" dismissible class="mb-4">{{ session('status') }}</x-alert>
    @endif

    <x-card>
        <div class="mb-4 flex justify-end">
            <x-button :href="route('admin.subjects.create')">Tambah Mapel</x-button>
        </div>

        <x-responsive-table>
            <thead class="bg-slate-50 text-xs uppercase text-slate-500">
                <tr>
                    <th class="px-3 py-3">Nama</th>
                    <th class="px-3 py-3 text-end">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($subjects as $subject)
                    <tr>
                        <td class="px-3 py-3 font-medium">{{ $subject->name }}</td>
                        <td class="px-3 py-3">
                            <div class="flex justify-end gap-2">
                                <x-button size="sm" variant="secondary" :href="route('admin.subjects.edit', $subject)">Edit</x-button>
                                <form method="POST" action="{{ route('admin.subjects.destroy', $subject) }}" onsubmit="return confirm('Hapus mapel ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <x-button size="sm" variant="danger" type="submit">Hapus</x-button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="2" class="px-3 py-6 text-center text-slate-500">Belum ada mata pelajaran.</td></tr>
                @endforelse
            </tbody>
        </x-responsive-table>

        <div class="mt-4">{{ $subjects->links() }}</div>
    </x-card>
</x-app-layout>
