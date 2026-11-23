<x-app-layout>
    <x-slot name="header">Ringkasan Admin</x-slot>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <x-card title="Data Master" subtitle="Tahap 2">
            <p class="text-sm leading-6 text-slate-600">CRUD siswa, guru, kelas, mapel, dan import Excel akan dibangun pada tahap berikutnya.</p>
        </x-card>
        <x-card title="Pengaturan Sistem" subtitle="Tahap 2">
            <p class="text-sm leading-6 text-slate-600">Pengaturan ujian dan batas pelanggaran tersedia setelah tahap pembuatan ujian.</p>
        </x-card>
        <x-card title="Laporan" subtitle="Tahap 5">
            <p class="text-sm leading-6 text-slate-600">Ekspor PDF dan Excel disiapkan bersama fitur koreksi esai.</p>
        </x-card>
    </div>
</x-app-layout>
