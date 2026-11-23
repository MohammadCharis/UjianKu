<x-app-layout>
    <x-slot name="header">Data Siswa</x-slot>

    @if (session('status'))
        <x-alert type="success" dismissible class="mb-4">{{ session('status') }}</x-alert>
    @endif

    <x-card>
        <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:justify-end">
            <x-button variant="secondary" :href="route('admin.students.import.create')">Import Excel</x-button>
            <x-button :href="route('admin.students.create')">Tambah Siswa</x-button>
        </div>

        <x-responsive-table>
            <thead class="bg-slate-50 text-xs uppercase text-slate-500">
                <tr>
                    <th class="px-3 py-3">NIS</th>
                    <th class="px-3 py-3">Nama</th>
                    <th class="px-3 py-3">Kelas</th>
                    <th class="px-3 py-3">Email</th>
                    <th class="px-3 py-3 text-end">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($students as $student)
                    <tr>
                        <td class="px-3 py-3 font-medium">{{ $student->nis }}</td>
                        <td class="px-3 py-3">{{ $student->user?->name }}</td>
                        <td class="px-3 py-3">{{ $student->schoolClass?->name }}</td>
                        <td class="px-3 py-3 text-slate-600">{{ $student->user?->email }}</td>
                        <td class="px-3 py-3">
                            <div class="flex justify-end gap-2">
                                <x-button size="sm" variant="secondary" :href="route('admin.students.edit', $student)">Edit</x-button>
                                <form method="POST" action="{{ route('admin.students.destroy', $student) }}" onsubmit="return confirm('Hapus siswa ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <x-button size="sm" variant="danger" type="submit">Hapus</x-button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-3 py-6 text-center text-slate-500">Belum ada siswa.</td></tr>
                @endforelse
            </tbody>
        </x-responsive-table>

        <div class="mt-4">{{ $students->links() }}</div>
    </x-card>
</x-app-layout>
