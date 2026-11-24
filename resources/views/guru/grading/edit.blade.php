<x-app-layout>
    <x-slot name="header">Koreksi Esai: {{ $examSession->student?->user?->name }}</x-slot>

    @if (session('status'))
        <x-alert type="success" dismissible class="mb-4">{{ session('status') }}</x-alert>
    @endif

    <x-card title="{{ $examSession->exam?->title }}" subtitle="Nilai saat ini: {{ $examSession->score ?? 0 }}">
        @php
            $answers = $examSession->answers->keyBy('question_id');
        @endphp

        <form method="POST" action="{{ route('guru.grading.update', $examSession) }}" class="space-y-5">
            @csrf
            @method('PUT')

            @foreach ($examSession->exam->questions as $question)
                @php
                    $answer = $answers->get($question->id);
                    $maxPoints = $examSession->exam->examQuestions->firstWhere('question_id', $question->id)?->points ?? 0;
                @endphp
                <div class="rounded-2xl border border-slate-200 p-4">
                    <div class="flex items-start justify-between gap-3">
                        <p class="text-sm font-semibold text-slate-900">{{ $question->body }}</p>
                        <span class="shrink-0 text-xs text-slate-500">Maks. {{ $maxPoints }} poin</span>
                    </div>

                    <div class="mt-3 rounded-xl bg-slate-50 p-3 text-sm text-slate-700">
                        {{ $answer?->answer_text ?: 'Tidak dijawab' }}
                    </div>

                    @if (in_array($question->type, ['esai', 'isian'], true))
                        <div class="mt-3">
                            <x-input-label for="score_{{ $answer?->id }}" value="Nilai" />
                            <x-text-input id="score_{{ $answer?->id }}" name="scores[{{ $answer?->id }}]" type="number" min="0" max="{{ $maxPoints }}" step="0.5" class="mt-1 w-32" :value="old('scores.'.$answer?->id, $answer?->score)" />
                            <x-input-error :messages="$errors->get('scores.'.$answer?->id)" class="mt-2" />
                        </div>
                    @else
                        <p class="mt-3 text-xs text-slate-500">Nilai otomatis: {{ $answer?->score ?? 0 }}</p>
                    @endif
                </div>
            @endforeach

            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                <x-button variant="secondary" :href="route('guru.grading.index')">Batal</x-button>
                <x-button type="submit">Simpan Nilai</x-button>
            </div>
        </form>
    </x-card>
</x-app-layout>
