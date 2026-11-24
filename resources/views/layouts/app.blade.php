<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#0f172a">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <link rel="manifest" href="/manifest.json">
    <link rel="apple-touch-icon" href="/icons/icon-192.png">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? config('app.name', 'UjianKu') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-50 font-sans text-slate-900 antialiased">
    <a href="#main-content" class="sr-only focus:not-sr-only focus:fixed focus:start-4 focus:top-4 focus:z-50 focus:rounded-lg focus:bg-white focus:px-4 focus:py-2 focus:shadow">
        Lewati ke konten
    </a>

    <div
        id="app-sidebar-backdrop"
        data-open="false"
        class="fixed inset-0 z-30 hidden bg-slate-950/50 backdrop-blur-sm data-[open=true]:block lg:hidden"
    ></div>

    <aside
        id="app-sidebar"
        data-open="false"
        class="fixed inset-y-0 start-0 z-40 flex w-72 -translate-x-full flex-col border-e border-slate-200 bg-white transition-transform duration-200 data-[open=true]:translate-x-0 lg:translate-x-0"
    >
        <div class="flex h-14 items-center justify-between gap-2 border-b border-slate-200 px-4 lg:h-16">
            <a href="{{ route('dashboard') }}" class="flex min-w-0 items-center gap-2 font-bold text-brand-700">
                <x-application-logo class="h-8 w-auto shrink-0 fill-current" />
                <span class="truncate text-lg">{{ config('app.name', 'UjianKu') }}</span>
            </a>

            <button type="button" data-sidebar-close aria-label="Tutup menu" class="rounded-lg p-2 text-slate-500 hover:bg-slate-100 lg:hidden">
                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path d="M6.28 5.22a.75.75 0 0 0-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 1 0 1.06 1.06L10 11.06l3.72 3.72a.75.75 0 1 0 1.06-1.06L11.06 10l3.72-3.72a.75.75 0 0 0-1.06-1.06L10 8.94 6.28 5.22Z" />
                </svg>
            </button>
        </div>

        @php
            $links = match (auth()->user()->role) {
                'admin' => [
                    ['label' => 'Ringkasan', 'route' => route('admin.index'), 'pattern' => 'admin.index'],
                    ['label' => 'Kelas', 'route' => route('admin.classes.index'), 'pattern' => 'admin.classes.*'],
                    ['label' => 'Mata Pelajaran', 'route' => route('admin.subjects.index'), 'pattern' => 'admin.subjects.*'],
                    ['label' => 'Guru', 'route' => route('admin.teachers.index'), 'pattern' => 'admin.teachers.*'],
                    ['label' => 'Siswa', 'route' => route('admin.students.index'), 'pattern' => 'admin.students.*'],
                    ['label' => 'Laporan', 'route' => route('admin.reports.index'), 'pattern' => 'admin.reports.*'],
                ],
                'guru' => [
                    ['label' => 'Ringkasan', 'route' => route('guru.index'), 'pattern' => 'guru.index'],
                    ['label' => 'Bank Soal', 'route' => route('guru.questions.index'), 'pattern' => 'guru.questions.*'],
                    ['label' => 'Ujian', 'route' => route('guru.exams.index'), 'pattern' => 'guru.exams.*'],
                    ['label' => 'Koreksi Esai', 'route' => route('guru.grading.index'), 'pattern' => 'guru.grading.*'],
                    ['label' => 'Pengawasan', 'route' => route('guru.monitoring.index'), 'pattern' => 'guru.monitoring.*'],
                    ['label' => 'Laporan', 'route' => route('guru.reports.index'), 'pattern' => 'guru.reports.*'],
                ],
                default => [
                    ['label' => 'Ringkasan', 'route' => route('siswa.index'), 'pattern' => 'siswa.index'],
                ],
            };
        @endphp

        <nav class="flex-1 space-y-1 overflow-y-auto p-3" aria-label="Navigasi utama">
            <p class="px-3 pb-2 pt-3 text-xs font-semibold uppercase tracking-wide text-slate-400">Menu</p>
            @foreach ($links as $link)
                <a
                    href="{{ $link['route'] }}"
                    class="flex min-h-11 items-center rounded-xl px-3 text-sm font-medium transition {{ request()->routeIs($link['pattern']) ? 'bg-brand-50 text-brand-700' : 'text-slate-600 hover:bg-slate-100' }}"
                >
                    {{ $link['label'] }}
                </a>
            @endforeach
        </nav>

        <div class="border-t border-slate-200 p-3">
            <div class="mb-2 rounded-xl bg-slate-50 p-3">
                <p class="truncate text-sm font-semibold text-slate-800">{{ auth()->user()->name }}</p>
                <p class="text-xs capitalize text-slate-500">{{ auth()->user()->role }}</p>
            </div>
            <a href="{{ route('profile.edit') }}" class="flex min-h-11 items-center rounded-xl px-3 text-sm text-slate-600 hover:bg-slate-100">Profil</a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="flex min-h-11 w-full items-center rounded-xl px-3 text-sm text-red-600 hover:bg-red-50">Keluar</button>
            </form>
        </div>
    </aside>

    <div class="lg:ps-72">
        <header class="sticky top-0 z-20 flex h-14 items-center gap-3 border-b border-slate-200 bg-white/95 px-4 backdrop-blur sm:px-6 lg:h-16">
            <button
                type="button"
                data-sidebar-toggle
                aria-label="Buka menu"
                aria-controls="app-sidebar"
                aria-expanded="false"
                class="-ms-2 rounded-lg p-2 text-slate-600 hover:bg-slate-100 lg:hidden"
            >
                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">
                    <path d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>

            <div class="min-w-0 flex-1 truncate font-semibold">
                @isset($header)
                    {{ $header }}
                @else
                    {{ config('app.name', 'UjianKu') }}
                @endisset
            </div>

            <span class="hidden rounded-full bg-brand-50 px-3 py-1 text-xs font-semibold capitalize text-brand-700 sm:inline">
                {{ auth()->user()->role }}
            </span>

            <button type="button" data-install-app class="hidden min-h-10 rounded-xl bg-brand-600 px-3 text-xs font-semibold text-white">
                Pasang Aplikasi
            </button>
        </header>

        <main id="main-content" class="mx-auto w-full max-w-7xl px-4 py-5 sm:px-6 lg:px-8 lg:py-8">
            {{ $slot }}
        </main>
    </div>
</body>
</html>
