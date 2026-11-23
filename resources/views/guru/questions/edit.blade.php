<x-app-layout>
    <x-slot name="header">Edit Soal</x-slot>

    <x-card>
        @include('guru.questions._form', ['question' => $question])
    </x-card>
</x-app-layout>
