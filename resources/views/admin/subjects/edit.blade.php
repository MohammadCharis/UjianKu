<x-app-layout>
    <x-slot name="header">Edit Mata Pelajaran</x-slot>

    <x-card title="Form Mata Pelajaran">
        <form method="POST" action="{{ route('admin.subjects.update', $subject) }}" class="space-y-4">
            @csrf
            @method('PUT')

            <div>
                <x-input-label for="name" value="Nama Mata Pelajaran" />
                <x-text-input id="name" name="name" type="text" class="mt-1 w-full" :value="old('name', $subject->name)" required />
                <x-input-error :messages="$errors->get('name')" class="mt-2" />
            </div>

            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                <x-button variant="secondary" :href="route('admin.subjects.index')">Batal</x-button>
                <x-button type="submit">Simpan</x-button>
            </div>
        </form>
    </x-card>
</x-app-layout>
