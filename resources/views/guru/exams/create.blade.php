<x-app-layout>
    <x-slot name="header">Buat Ujian</x-slot>

    <x-card>
        @if ($errors->any())
            <x-alert type="danger" title="Periksa kembali form" class="mb-4">
                <ul class="list-disc space-y-1 ps-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </x-alert>
        @endif

        @include('guru.exams._form', ['exam' => null])
    </x-card>
</x-app-layout>
