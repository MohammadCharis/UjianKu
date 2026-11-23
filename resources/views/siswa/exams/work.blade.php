<x-student-layout title="{{ $exam->title }}">
    <x-slot name="timer">
        <span data-timer-display class="rounded-lg bg-slate-900 px-3 py-1.5 text-sm font-bold tabular-nums text-white">--:--</span>
        <span data-save-status class="hidden text-xs text-slate-500 sm:inline">Tersimpan</span>
    </x-slot>

    <div
        data-exam-session
        data-session-id="{{ $session->id }}"
        data-remaining-seconds="{{ $remainingSeconds }}"
        data-server-now-ms="{{ $serverNowMs }}"
        data-answer-url="{{ route('siswa.exams.answers', $session) }}"
        data-heartbeat-url="{{ route('siswa.exams.heartbeat', $session) }}"
        data-submit-url="{{ route('siswa.exams.submit', $session) }}"
        class="pb-24"
    >
        <div class="mb-3 flex items-center justify-between gap-3">
            <div class="min-w-0">
                <p class="truncate text-sm font-semibold text-slate-900">{{ $exam->subject->name }}</p>
                <p class="text-xs text-slate-500">{{ $questions->count() }} soal · {{ $exam->duration_min }} menit</p>
            </div>
            <button type="button" data-bottom-sheet-open="question-sheet" class="min-h-11 rounded-xl border border-slate-200 bg-white px-3 text-sm font-semibold text-slate-700 md:hidden">
                Nomor Soal
            </button>
        </div>

        <div class="grid gap-4 md:grid-cols-[minmax(0,1fr)_14rem] md:items-start">
            <div data-exam-pages>
                @foreach ($questions as $index => $question)
                    @php
                        $answer = $answers->get($question->id);
                    @endphp
                    <section data-question-page data-question-index="{{ $index }}" class="@if ($index !== 0) hidden @endif">
                        <div class="mb-2 flex items-center justify-between text-xs font-semibold text-slate-500">
                            <span>Soal {{ $index + 1 }} dari {{ $questions->count() }}</span>
                            <span>{{ $question->points }} poin</span>
                        </div>

                        <x-card>
                            <p class="whitespace-pre-line text-base leading-7 text-slate-900">{{ $question->body }}</p>

                            @if ($question->image)
                                <button type="button" data-image-zoom="{{ asset('storage/'.$question->image) }}" class="mt-4 block w-full overflow-hidden rounded-xl border border-slate-200">
                                    <img src="{{ asset('storage/'.$question->image) }}" alt="Gambar soal" class="max-h-72 w-full object-contain">
                                    <span class="block bg-slate-50 py-2 text-xs font-semibold text-brand-700">Ketuk untuk perbesar</span>
                                </button>
                            @endif

                            @if ($question->type === 'pg')
                                <div class="mt-5 space-y-3">
                                    @foreach ($question->options as $option)
                                        <label class="block cursor-pointer">
                                            <input
                                                type="radio"
                                                name="answer_{{ $question->id }}"
                                                value="{{ $option->id }}"
                                                data-answer-option
                                                data-question-id="{{ $question->id }}"
                                                data-question-index="{{ $index }}"
                                                @checked($answer?->option_id === $option->id)
                                                class="peer sr-only"
                                            >
                                            <span class="flex min-h-14 items-center gap-3 rounded-2xl border-2 border-slate-200 px-4 text-sm font-medium text-slate-700 transition peer-checked:border-brand-500 peer-checked:bg-brand-50 peer-checked:text-brand-800 peer-focus-visible:ring-2 peer-focus-visible:ring-brand-400">
                                                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-slate-100 text-sm font-bold peer-checked:bg-brand-100">{{ $option->label }}</span>
                                                <span>{{ $option->text }}</span>
                                            </span>
                                        </label>
                                    @endforeach
                                </div>
                            @else
                                <div class="mt-5">
                                    <x-input-label for="answer_text_{{ $question->id }}" value="{{ $question->type === 'esai' ? 'Jawaban Esai' : 'Jawaban Singkat' }}" />
                                    <textarea
                                        id="answer_text_{{ $question->id }}"
                                        rows="{{ $question->type === 'esai' ? 8 : 3 }}"
                                        data-answer-text
                                        data-question-id="{{ $question->id }}"
                                        data-question-index="{{ $index }}"
                                        class="mt-1 w-full rounded-xl border-slate-300 text-sm"
                                        placeholder="Tulis jawaban Anda di sini..."
                                    >{{ $answer?->answer_text }}</textarea>
                                </div>
                            @endif

                            <label class="mt-5 flex min-h-11 items-center gap-3 rounded-xl bg-amber-50 px-3 text-sm text-amber-900">
                                <input
                                    type="checkbox"
                                    data-doubtful
                                    data-question-id="{{ $question->id }}"
                                    data-question-index="{{ $index }}"
                                    @checked($answer?->is_doubtful)
                                    class="h-5 w-5 rounded border-slate-300 text-amber-600"
                                >
                                Tandai ragu-ragu
                            </label>
                        </x-card>
                    </section>
                @endforeach
            </div>

            <aside class="hidden md:block">
                <x-card title="Navigasi Soal" class="sticky top-20">
                    @include('siswa.exams._question-nav')
                    <p class="mt-3 text-xs text-slate-500">Nomor berwarna menandakan soal sudah dijawab.</p>
                </x-card>
            </aside>
        </div>

        <div class="fixed inset-x-0 bottom-0 z-20 border-t border-slate-200 bg-white/95 backdrop-blur">
            <div class="mx-auto flex w-full max-w-3xl items-center gap-2 px-3 py-3">
                <x-button data-question-prev variant="secondary" class="flex-1">Sebelumnya</x-button>
                <span data-question-position class="shrink-0 text-sm font-semibold text-slate-600">1 / {{ $questions->count() }}</span>
                <x-button data-question-next class="flex-1">Berikutnya</x-button>
                <button type="button" data-modal-open="submit-confirm" class="min-h-12 shrink-0 rounded-xl bg-red-600 px-3 text-sm font-semibold text-white">Selesai</button>
            </div>
        </div>

        <x-bottom-sheet name="question-sheet">
            <h2 class="mb-4 text-base font-semibold text-slate-900">Navigasi Soal</h2>
            @include('siswa.exams._question-nav')
        </x-bottom-sheet>

        <x-modal name="submit-confirm">
            <div class="p-5">
                <h2 class="text-lg font-semibold text-slate-900">Kumpulkan jawaban?</h2>
                <p class="mt-2 text-sm text-slate-600">Pastikan semua jawaban sudah terisi. Setelah dikumpulkan, jawaban tidak dapat diubah.</p>
                <div class="mt-5 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                    <x-button data-modal-close variant="secondary">Periksa Kembali</x-button>
                    <x-button data-submit-exam data-submit-url="{{ route('siswa.exams.submit', $session) }}" variant="danger">Ya, Kumpulkan</x-button>
                </div>
            </div>
        </x-modal>

        <x-modal name="image-zoom">
            <div class="p-3">
                <img data-zoom-image src="" alt="Gambar soal" class="mx-auto max-h-[80vh] w-full object-contain">
                <div class="mt-3 text-end">
                    <x-button data-modal-close variant="secondary">Tutup</x-button>
                </div>
            </div>
        </x-modal>
    </div>
</x-student-layout>
