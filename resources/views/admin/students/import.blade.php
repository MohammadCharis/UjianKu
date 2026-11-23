<x-app-layout>
    <x-slot name="header">Import Siswa</x-slot>

    <div class="grid gap-4 lg:grid-cols-3">
        <x-card title="Unggah Excel" subtitle="Format: xlsx, xls, atau csv (maks. 2 MB)" class="lg:col-span-2">
            @if ($errors->any())
                <x-alert type="danger" title="Data belum valid" class="mb-4">
                    <ul class="list-disc space-y-1 ps-5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </x-alert>
            @endif

            <form method="POST" action="{{ route('admin.students.import.store') }}" enctype="multipart/form-data" class="space-y-4">
                @csrf

                <div>
                    <x-input-label for="file" value="Berkas Siswa" />
                    <input id="file" name="file" type="file" accept=".xlsx,.xls,.csv" class="mt-1 block w-full min-h-11 rounded-xl border border-slate-300 bg-white p-2 text-sm" required>
                    <x-input-error :messages="$errors->get('file')" class="mt-2" />
                </div>

                <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                    <x-button variant="secondary" :href="route('admin.students.index')">Batal</x-button>
                    <x-button type="submit">Import</x-button>
                </div>
            </form>
        </x-card>

        <x-card title="Template" subtitle="Kolom yang dibutuhkan">
            <ul class="list-disc space-y-1 ps-5 text-sm text-slate-600">
                <li><code>nama</code></li>
                <li><code>email</code></li>
                <li><code>nis</code></li>
                <li><code>kelas</code> (nama kelas yang sudah ada)</li>
            </ul>
            <x-button class="mt-4" variant="secondary" :href="route('admin.students.import.template')">Unduh Template</x-button>
        </x-card>
    </div>
</x-app-layout>
