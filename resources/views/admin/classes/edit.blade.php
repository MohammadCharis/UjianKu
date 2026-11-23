<x-app-layout>
    <x-slot name="header">Edit Kelas</x-slot>

    <x-card title="Form Kelas">
        <form method="POST" action="{{ route('admin.classes.update', $schoolClass) }}" class="space-y-4">
            @csrf
            @method('PUT')

            <div>
                <x-input-label for="name" value="Nama Kelas" />
                <x-text-input id="name" name="name" type="text" class="mt-1 w-full" :value="old('name', $schoolClass->name)" required />
                <x-input-error :messages="$errors->get('name')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="grade" value="Tingkat" />
                <x-text-input id="grade" name="grade" type="text" class="mt-1 w-full" :value="old('grade', $schoolClass->grade)" required />
                <x-input-error :messages="$errors->get('grade')" class="mt-2" />
            </div>

            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                <x-button variant="secondary" :href="route('admin.classes.index')">Batal</x-button>
                <x-button type="submit">Simpan</x-button>
            </div>
        </form>
    </x-card>
</x-app-layout>
