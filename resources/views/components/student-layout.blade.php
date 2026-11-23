<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#0f172a">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? config('app.name', 'UjianKu') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 font-sans text-slate-900 antialiased">
    <div class="mx-auto flex min-h-screen w-full max-w-3xl flex-col">
        <header class="sticky top-0 z-30 border-b border-slate-200 bg-white/95 backdrop-blur">
            <div class="flex h-14 items-center justify-between gap-3 px-4">
                <div class="flex min-w-0 items-center gap-2">
                    <x-application-logo class="h-7 w-auto shrink-0 fill-current text-brand-600" />
                    <span class="truncate text-sm font-bold text-slate-800">{{ $title ?? config('app.name', 'UjianKu') }}</span>
                </div>

                <div class="flex shrink-0 items-center gap-2">
                    @isset($timer)
                        {{ $timer }}
                    @endisset
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="min-h-10 rounded-xl px-3 text-xs font-semibold text-slate-500 hover:bg-slate-100">
                            Keluar
                        </button>
                    </form>
                </div>
            </div>
        </header>

        <main class="flex-1 px-3 pb-[max(1rem,env(safe-area-inset-bottom))] pt-4 sm:px-5">
            {{ $slot }}
        </main>
    </div>
</body>
</html>
