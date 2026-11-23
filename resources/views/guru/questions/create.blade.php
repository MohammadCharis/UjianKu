<x-app-layout>
    <x-slot name="header">Tambah Soal</x-slot>

    <x-card>
        @include('guru.questions._form', ['question' => null])
    </x-card>
</x-app-layout>
