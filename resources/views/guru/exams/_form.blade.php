@php
    $selectedClassIds = old('class_ids', $exam?->classes->pluck('id')->all() ?? []);
    $selectedQuestions = $exam?->examQuestions->keyBy('question_id') ?? collect();
@endphp

<form method="POST" action="{{ $exam ? route('guru.exams.update', $exam) : route('guru.exams.store') }}" class="space-y-5">
    @csrf
    @if ($exam)
        @method('PUT')
    @endif

    <div class="grid gap-4 sm:grid-cols-2">
        <div>
            <x-input-label for="subject_id" value="Mata Pelajaran" />
            <select id="subject_id" name="subject_id" class="mt-1 min-h-11 w-full rounded-xl border-slate-300 text-sm">
                @foreach ($subjects as $subject)
                    <option value="{{ $subject->id }}" @selected(old('subject_id', $exam?->subject_id) == $subject->id)>{{ $subject->name }}</option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('subject_id')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="lock_mode" value="Mode Kunci" />
            <select id="lock_mode" name="lock_mode" class="mt-1 min-h-11 w-full rounded-xl border-slate-300 text-sm">
                <option value="kunci" @selected(old('lock_mode', $exam?->lock_mode ?? 'kunci') === 'kunci')>Kunci Sesi</option>
                <option value="auto_submit" @selected(old('lock_mode', $exam?->lock_mode) === 'auto_submit')>Auto Submit</option>
            </select>
            <x-input-error :messages="$errors->get('lock_mode')" class="mt-2" />
        </div>
    </div>

    <div class="grid gap-4 sm:grid-cols-2">
        <div>
            <x-input-label for="title" value="Judul Ujian" />
            <x-text-input id="title" name="title" type="text" class="mt-1 w-full" :value="old('title', $exam?->title)" />
            <x-input-error :messages="$errors->get('title')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="token" value="Token" />
            <x-text-input id="token" name="token" type="text" class="mt-1 w-full uppercase" :value="old('token', $exam?->token)" />
            <x-input-error :messages="$errors->get('token')" class="mt-2" />
        </div>
    </div>

    <div class="grid gap-4 sm:grid-cols-2">
        <div>
            <x-input-label for="start_at" value="Mulai" />
            <x-text-input id="start_at" name="start_at" type="datetime-local" class="mt-1 w-full" :value="old('start_at', $exam?->start_at?->format('Y-m-d\TH:i'))" />
            <x-input-error :messages="$errors->get('start_at')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="end_at" value="Selesai" />
            <x-text-input id="end_at" name="end_at" type="datetime-local" class="mt-1 w-full" :value="old('end_at', $exam?->end_at?->format('Y-m-d\TH:i'))" />
            <x-input-error :messages="$errors->get('end_at')" class="mt-2" />
        </div>
    </div>

    <div class="grid gap-4 sm:grid-cols-2">
        <div>
            <x-input-label for="duration_min" value="Durasi (menit)" />
            <x-text-input id="duration_min" name="duration_min" type="number" min="1" max="600" class="mt-1 w-full" :value="old('duration_min', $exam?->duration_min ?? 60)" />
            <x-input-error :messages="$errors->get('duration_min')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="max_violations" value="Batas Pelanggaran" />
            <x-text-input id="max_violations" name="max_violations" type="number" min="1" max="20" class="mt-1 w-full" :value="old('max_violations', $exam?->max_violations ?? 3)" />
            <x-input-error :messages="$errors->get('max_violations')" class="mt-2" />
        </div>
    </div>

    <label class="flex min-h-11 items-center gap-3 rounded-xl bg-slate-50 px-4 text-sm text-slate-700">
        <input type="hidden" name="shuffle" value="0">
        <input type="checkbox" name="shuffle" value="1" @checked(old('shuffle', $exam?->shuffle ?? false)) class="h-5 w-5 rounded border-slate-300 text-brand-600">
        Acak urutan soal
    </label>

    <div>
        <p class="text-sm font-semibold text-slate-800">Kelas Peserta</p>
        <div class="mt-2 grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($classes as $schoolClass)
                <label class="flex min-h-11 items-center gap-3 rounded-xl border border-slate-200 px-3 text-sm">
                    <input type="checkbox" name="class_ids[]" value="{{ $schoolClass->id }}" @checked(in_array($schoolClass->id, $selectedClassIds)) class="h-5 w-5 rounded border-slate-300 text-brand-600">
                    <span>{{ $schoolClass->name }}</span>
                </label>
            @endforeach
        </div>
        <x-input-error :messages="$errors->get('class_ids')" class="mt-2" />
    </div>

    <div>
        <p class="text-sm font-semibold text-slate-800">Soal & Poin</p>
        <div class="mt-2 space-y-2">
            @foreach ($questions as $question)
                @php
                    $checked = in_array($question->id, collect(old('questions', []))->pluck('id')->all()) || $selectedQuestions->has($question->id);
                    $points = old('questions.'.$loop->index.'.points', $selectedQuestions->get($question->id)?->points ?? 10);
                @endphp
                <div data-exam-question-row class="grid grid-cols-[auto_1fr_5rem] items-center gap-3 rounded-xl border border-slate-200 p-3">
                    <input
                        type="checkbox"
                        name="questions[{{ $loop->index }}][id]"
                        value="{{ $question->id }}"
                        data-exam-question-toggle
                        @checked($checked)
                        class="h-5 w-5 rounded border-slate-300 text-brand-600"
                    >
                    <div class="min-w-0">
                        <p class="truncate text-sm text-slate-800">{{ Str::limit($question->body, 80) }}</p>
                        <p class="text-xs text-slate-500">{{ $question->subject->name }} · {{ strtoupper($question->type) }}</p>
                    </div>
                    <input
                        type="number"
                        name="questions[{{ $loop->index }}][points]"
                        value="{{ $points }}"
                        min="0"
                        max="100"
                        step="0.5"
                        @disabled(! $checked)
                        data-exam-question-points
                        class="min-h-11 w-full rounded-lg border-slate-300 text-sm"
                        aria-label="Poin soal"
                    >
                </div>
            @endforeach
        </div>
        <x-input-error :messages="$errors->get('questions')" class="mt-2" />
    </div>

    <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
        <x-button variant="secondary" :href="route('guru.exams.index')">Batal</x-button>
        <x-button type="submit">Simpan Ujian</x-button>
    </div>
</form>
