<nav class="border-b border-slate-200 bg-white">
    <div class="mx-auto flex h-14 max-w-7xl items-center justify-between gap-4 px-4 sm:px-6 lg:px-8">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-2 font-semibold text-slate-900">
            <x-application-logo class="h-8 w-auto fill-current text-brand-600" />
            <span>{{ config('app.name', 'UjianKu') }}</span>
        </a>

        <div class="flex items-center gap-2">
            <x-button variant="ghost" size="sm" :href="route('dashboard')">Beranda</x-button>

            <x-dropdown align="right" width="48">
                <x-slot name="trigger">
                    <span class="inline-flex min-h-10 items-center rounded-xl px-3 text-sm font-medium text-slate-600 hover:bg-slate-100">
                        {{ Auth::user()->name }}
                    </span>
                </x-slot>

                <x-slot name="content">
                    <x-dropdown-link :href="route('profile.edit')">{{ __('Profile') }}</x-dropdown-link>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">
                            {{ __('Log Out') }}
                        </x-dropdown-link>
                    </form>
                </x-slot>
            </x-dropdown>
        </div>
    </div>
</nav>
