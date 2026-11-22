@props([
    'name',
    'show' => false,
    'maxWidth' => 'lg',
])

@php
    $widths = [
        'sm' => 'sm:max-w-sm',
        'md' => 'sm:max-w-md',
        'lg' => 'sm:max-w-lg',
        'xl' => 'sm:max-w-xl',
        '2xl' => 'sm:max-w-2xl',
    ];
@endphp

<dialog
    id="{{ $name }}"
    @if ($show) data-modal-autopen @endif
    class="w-[calc(100%-2rem)] max-w-none rounded-2xl border border-slate-200 bg-white p-0 shadow-xl backdrop:bg-slate-950/50 {{ $widths[$maxWidth] ?? $widths['lg'] }}"
    aria-modal="true"
>
    <div class="max-h-[85vh] overflow-y-auto">
        {{ $slot }}
    </div>
</dialog>
