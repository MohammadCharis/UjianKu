<x-app-layout>
    <x-slot name="header">Ringkasan Guru</x-slot>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <x-card title="Bank Soal & Ujian" subtitle="Tersedia">
            <p class="text-sm leading-6 text-slate-600">Buat soal PG, isian, dan esai, lalu susun ujian beserta kelas pesertanya.</p>
            <div class="mt-4 flex flex-wrap gap-2">
                <x-button size="sm" :href="route('guru.questions.index')">Bank Soal</x-button>
                <x-button size="sm" variant="secondary" :href="route('guru.exams.index')">Ujian</x-button>
                <x-button size="sm" variant="secondary" :href="route('guru.grading.index')">Koreksi Esai</x-button>
                <x-button size="sm" variant="secondary" :href="route('guru.monitoring.index')">Pengawasan</x-button>
                <x-button size="sm" variant="secondary" :href="route('guru.reports.index')">Laporan</x-button>
            </div>
        </x-card>
        <x-card title="Pengawasan" subtitle="Tahap 4">
            <p class="text-sm leading-6 text-slate-600">Alert pelanggaran siswa akan tampil di sini secara real-time dengan polling.</p>
        </x-card>
        <x-card title="Koreksi & Laporan" subtitle="Tahap 5">
            <p class="text-sm leading-6 text-slate-600">Nilai esai dan unduh rekap nilai serta pelanggaran.</p>
        </x-card>
    </div>
</x-app-layout>
