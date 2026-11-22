@props(['name'])

<dialog
    id="{{ $name }}"
    class="fixed inset-x-0 bottom-0 m-0 w-full max-w-none rounded-t-3xl border-t border-slate-200 bg-white p-0 shadow-2xl backdrop:bg-slate-950/50 sm:mx-auto sm:mb-0 sm:max-w-lg sm:rounded-3xl sm:border"
    aria-modal="true"
>
    <div class="mx-auto mt-3 h-1.5 w-10 rounded-full bg-slate-300 sm:hidden"></div>
    <div class="max-h-[85vh] overflow-y-auto p-4 pb-[max(1rem,env(safe-area-inset-bottom))] sm:p-6">
        {{ $slot }}
    </div>
</dialog>
