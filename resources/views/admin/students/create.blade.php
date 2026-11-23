<x-app-layout>
    <x-slot name="header">Tambah Siswa</x-slot>

    <x-card title="Form Siswa">
        <form method="POST" action="{{ route('admin.students.store') }}" class="space-y-4">
            @csrf

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <x-input-label for="name" value="Nama" />
                    <x-text-input id="name" name="name" type="text" class="mt-1 w-full" :value="old('name')" required />
                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="nis" value="NIS" />
                    <x-text-input id="nis" name="nis" type="text" class="mt-1 w-full" :value="old('nis')" required />
                    <x-input-error :messages="$errors->get('nis')" class="mt-2" />
                </div>
            </div>

            <div>
                <x-input-label for="email" value="Email" />
                <x-text-input id="email" name="email" type="email" class="mt-1 w-full" :value="old('email')" required />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="class_id" value="Kelas" />
                <select id="class_id" name="class_id" class="mt-1 min-h-11 w-full rounded-xl border-slate-300 text-sm" required>
                    @foreach ($classes as $class)
                        <option value="{{ $class->id }}" @selected(old('class_id') == $class->id)>{{ $class->name }}</option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('class_id')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="password" value="Password" />
                <x-text-input id="password" name="password" type="password" class="mt-1 w-full" />
                <p class="mt-1 text-xs text-slate-500">Kosongkan untuk menggunakan password default: password</p>
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                <x-button variant="secondary" :href="route('admin.students.index')">Batal</x-button>
                <x-button type="submit">Simpan</x-button>
            </div>
        </form>
    </x-card>
</x-app-layout>
