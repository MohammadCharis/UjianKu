@props([
    'title' => null,
    'subtitle' => null,
])

<section {{ $attributes->merge(['class' => 'rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-6']) }}>
    @if ($title)
        <div class="mb-4 flex flex-col gap-1">
            <h2 class="text-base font-semibold text-slate-900 sm:text-lg">{{ $title }}</h2>
            @if ($subtitle)
                <p class="text-sm text-slate-500">{{ $subtitle }}</p>
            @endif
        </div>
    @endif

    {{ $slot }}
</section>
