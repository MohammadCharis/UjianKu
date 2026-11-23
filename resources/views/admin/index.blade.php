<x-app-layout>
    <x-slot name="header">Ringkasan Admin</x-slot>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <x-card title="Data Master" subtitle="Kelas, mapel, guru, siswa">
            <p class="text-sm leading-6 text-slate-600">Kelola data master dan import siswa dari Excel.</p>
            <div class="mt-4 flex flex-wrap gap-2">
                <x-button size="sm" :href="route('admin.classes.index')">Kelas</x-button>
                <x-button size="sm" variant="secondary" :href="route('admin.subjects.index')">Mapel</x-button>
                <x-button size="sm" variant="secondary" :href="route('admin.teachers.index')">Guru</x-button>
                <x-button size="sm" variant="secondary" :href="route('admin.students.index')">Siswa</x-button>
            </div>
        </x-card>
        <x-card title="Import Siswa" subtitle="Excel">
            <p class="text-sm leading-6 text-slate-600">Gunakan template agar kolom sesuai: nama, email, nis, kelas.</p>
            <x-button class="mt-4" variant="secondary" :href="route('admin.students.import.create')">Buka Import</x-button>
        </x-card>
        <x-card title="Laporan" subtitle="Tahap 5">
            <p class="text-sm leading-6 text-slate-600">Ekspor PDF dan Excel disiapkan bersama fitur koreksi esai.</p>
        </x-card>
    </div>
</x-app-layout>
