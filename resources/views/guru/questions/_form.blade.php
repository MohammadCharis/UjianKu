@php
    $currentType = old('type', $question?->type ?? 'pg');
    $options = old('options');

    if (! is_array($options)) {
        $options = $question?->options?->all() ?? [
            ['label' => 'A', 'text' => '', 'is_correct' => false],
            ['label' => 'B', 'text' => '', 'is_correct' => false],
            ['label' => 'C', 'text' => '', 'is_correct' => false],
            ['label' => 'D', 'text' => '', 'is_correct' => false],
        ];
    }

    $options = collect($options)->map(fn ($option) => (object) $option)->all();
@endphp

<form
    method="POST"
    action="{{ $question ? route('guru.questions.update', $question) : route('guru.questions.store') }}"
    enctype="multipart/form-data"
    class="space-y-5"
>
    @csrf
    @if ($question)
        @method('PUT')
    @endif

    <div class="grid gap-4 sm:grid-cols-2">
        <div>
            <x-input-label for="subject_id" value="Mata Pelajaran" />
            <select id="subject_id" name="subject_id" class="mt-1 min-h-11 w-full rounded-xl border-slate-300 text-sm" required>
                @foreach ($subjects as $subject)
                    <option value="{{ $subject->id }}" @selected(old('subject_id', $question?->subject_id) == $subject->id)>{{ $subject->name }}</option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('subject_id')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="type" value="Tipe Soal" />
            <select id="type" name="type" data-question-type class="mt-1 min-h-11 w-full rounded-xl border-slate-300 text-sm" required>
                <option value="pg" @selected($currentType === 'pg')>Pilihan Ganda</option>
                <option value="isian" @selected($currentType === 'isian')>Isian Singkat</option>
                <option value="esai" @selected($currentType === 'esai')>Esai</option>
            </select>
            <x-input-error :messages="$errors->get('type')" class="mt-2" />
        </div>
    </div>

    <div>
        <x-input-label for="body" value="Pertanyaan" />
        <textarea id="body" name="body" rows="4" class="mt-1 w-full rounded-xl border-slate-300 text-sm" required>{{ old('body', $question?->body) }}</textarea>
        <x-input-error :messages="$errors->get('body')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="image" value="Gambar Soal" />
        <input id="image" name="image" type="file" accept="image/jpeg,image/png,image/webp" class="mt-1 block min-h-11 w-full rounded-xl border border-slate-300 bg-white p-2 text-sm">
        @if ($question?->image)
            <img src="{{ asset('storage/'.$question->image) }}" alt="Gambar soal saat ini" class="mt-3 h-32 w-32 rounded-xl object-cover">
        @endif
        <x-input-error :messages="$errors->get('image')" class="mt-2" />
    </div>

    <div data-question-options data-next-index="{{ count($options) }}" @if ($currentType !== 'pg') hidden @endif>
        <div class="mb-3 flex items-center justify-between gap-3">
            <p class="text-sm font-semibold text-slate-800">Pilihan Jawaban</p>
            <button type="button" data-option-add class="min-h-11 rounded-xl bg-slate-100 px-3 text-sm font-semibold text-slate-700 hover:bg-slate-200">+ Tambah Opsi</button>
        </div>

        <div data-option-list class="space-y-2">
            @foreach ($options as $index => $option)
                @include('guru.questions._option-row', ['index' => $index, 'option' => $option])
            @endforeach
        </div>

        <x-input-error :messages="$errors->get('options')" class="mt-2" />

        <template>
            @include('guru.questions._option-row', ['index' => '__INDEX__', 'option' => null])
        </template>
    </div>

    <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
        <x-button variant="secondary" :href="route('guru.questions.index')">Batal</x-button>
        <x-button type="submit">Simpan Soal</x-button>
    </div>
</form>
