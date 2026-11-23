<div class="grid grid-cols-5 gap-2 sm:grid-cols-6">
    @foreach ($questions as $index => $question)
        @php
            $answer = $answers->get($question->id);
        @endphp
        <button
            type="button"
            data-question-goto
            data-question-index="{{ $index }}"
            data-question-id="{{ $question->id }}"
            data-answered="{{ $answer && ($answer->option_id !== null || filled($answer->answer_text)) ? 'true' : 'false' }}"
            data-doubtful="{{ $answer?->is_doubtful ? 'true' : 'false' }}"
            data-current="{{ $index === 0 ? 'true' : 'false' }}"
            class="flex min-h-11 items-center justify-center rounded-xl border border-slate-200 bg-white text-sm font-semibold text-slate-600 transition"
            aria-label="Ke soal {{ $index + 1 }}"
        >
            {{ $index + 1 }}
        </button>
    @endforeach
</div>
