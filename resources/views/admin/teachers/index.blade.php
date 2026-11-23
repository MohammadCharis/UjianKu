<x-app-layout>
    <x-slot name="header">Data Guru</x-slot>

    @if (session('status'))
        <x-alert type="success" dismissible class="mb-4">{{ session('status') }}</x-alert>
    @endif

    <x-card>
        <div class="mb-4 flex justify-end">
            <x-button :href="route('admin.teachers.create')">Tambah Guru</x-button>
        </div>

        <x-responsive-table>
            <thead class="bg-slate-50 text-xs uppercase text-slate-500">
                <tr>
                    <th class="px-3 py-3">Nama</th>
                    <th class="px-3 py-3">Email</th>
                    <th class="px-3 py-3 text-end">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($teachers as $teacher)
                    <tr>
                        <td class="px-3 py-3 font-medium">{{ $teacher->name }}</td>
                        <td class="px-3 py-3 text-slate-600">{{ $teacher->email }}</td>
                        <td class="px-3 py-3">
                            <div class="flex justify-end gap-2">
                                <x-button size="sm" variant="secondary" :href="route('admin.teachers.edit', $teacher)">Edit</x-button>
                                <form method="POST" action="{{ route('admin.teachers.destroy', $teacher) }}" onsubmit="return confirm('Hapus guru ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <x-button size="sm" variant="danger" type="submit">Hapus</x-button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="px-3 py-6 text-center text-slate-500">Belum ada guru.</td></tr>
                @endforelse
            </tbody>
        </x-responsive-table>

        <div class="mt-4">{{ $teachers->links() }}</div>
    </x-card>
</x-app-layout>
