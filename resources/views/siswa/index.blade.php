<x-student-layout title="Beranda Siswa">
    <x-card title="Selamat datang, {{ auth()->user()->name }}" subtitle="Halaman ujian">
        <p class="text-sm leading-6 text-slate-600">
            Daftar ujian aktif dan input token akan tersedia pada Tahap 3.
        </p>
        <div class="mt-4 flex flex-col gap-3 sm:flex-row">
            <x-button :href="route('siswa.index')">Halaman Siswa</x-button>
            <x-button variant="secondary" :href="route('profile.edit')">Profil</x-button>
        </div>
    </x-card>
</x-student-layout>
