<x-app-layout>
    <x-slot name="header">Dashboard</x-slot>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <x-card title="Halo, {{ auth()->user()->name }}" subtitle="Akun {{ auth()->user()->role }} sudah aktif.">
            <p class="text-sm leading-6 text-slate-600">
                Gunakan menu di samping (desktop) atau tombol di kanan atas (HP) untuk berpindah halaman.
            </p>
        </x-card>

        @if (auth()->user()->isAdmin())
            <x-card title="Data Master" subtitle="Tahap berikutnya">
                <p class="text-sm text-slate-600">Kelola siswa, guru, kelas, dan mata pelajaran.</p>
                <x-button class="mt-4" :href="route('admin.index')">Buka Ringkasan Admin</x-button>
            </x-card>
        @elseif (auth()->user()->isGuru())
            <x-card title="Mengawasi Ujian" subtitle="Tahap berikutnya">
                <p class="text-sm text-slate-600">Pantau alert pelanggaran dan buka kunci siswa.</p>
                <x-button class="mt-4" :href="route('guru.index')">Buka Ringkasan Guru</x-button>
            </x-card>
        @else
            <x-card title="Ujian Saya" subtitle="Tahap berikutnya">
                <p class="text-sm text-slate-600">Masukkan token untuk memulai ujian pada halaman siswa.</p>
                <x-button class="mt-4" :href="route('siswa.index')">Buka Halaman Siswa</x-button>
            </x-card>
        @endif
    </div>
</x-app-layout>
