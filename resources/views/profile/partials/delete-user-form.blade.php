<x-button data-modal-open="confirm-user-deletion" variant="danger">{{ __('Delete Account') }}</x-button>

<x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()">
    <form method="post" action="{{ route('profile.destroy') }}" class="p-6">
        @csrf
        @method('delete')

        <h2 class="text-lg font-semibold text-slate-900">{{ __('Are you sure you want to delete your account?') }}</h2>
        <p class="mt-2 text-sm text-slate-600">
            {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm you would like to permanently delete your account.') }}
        </p>

        <div class="mt-5">
            <x-input-label for="password" :value="__('Password')" class="sr-only" />
            <x-text-input id="password" name="password" type="password" class="mt-1 block w-full" placeholder="{{ __('Password') }}" />
            <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2" />
        </div>

        <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
            <x-button data-modal-close variant="secondary">{{ __('Cancel') }}</x-button>
            <x-button variant="danger">{{ __('Delete Account') }}</x-button>
        </div>
    </form>
</x-modal>
