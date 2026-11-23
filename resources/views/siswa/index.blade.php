<x-student-layout title="Beranda Siswa">
    <x-card title="Selamat datang, {{ auth()->user()->name }}" subtitle="Halaman ujian">
        <p class="text-sm leading-6 text-slate-600">
            Lihat ujian yang tersedia untuk kelas Anda, lalu masukkan token untuk memulai.
        </p>
        <div class="mt-4 flex flex-col gap-3 sm:flex-row">
            <x-button :href="route('siswa.exams.index')">Daftar Ujian</x-button>
            <x-button variant="secondary" :href="route('profile.edit')">Profil</x-button>
        </div>
    </x-card>
</x-student-layout>
