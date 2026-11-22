@props([
    'align' => 'right',
    'width' => '48',
    'contentClasses' => 'py-1 bg-white',
])

@php
    $alignmentClasses = match ($align) {
        'left' => 'start-0',
        'top' => 'start-1/2 -translate-x-1/2',
        default => 'end-0',
    };

    $width = match ($width) {
        '48' => 'w-48',
        default => $width,
    };
@endphp

<details data-dropdown class="relative">
    <summary class="cursor-pointer list-none [&::-webkit-details-marker]:hidden">{{ $trigger }}</summary>

    <div class="absolute z-50 mt-2 {{ $width }} rounded-xl border border-slate-200 bg-white shadow-lg {{ $alignmentClasses }}">
        <div class="rounded-xl {{ $contentClasses }}">
            {{ $content }}
        </div>
    </div>
</details>
