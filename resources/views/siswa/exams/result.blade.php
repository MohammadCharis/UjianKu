<x-student-layout title="Hasil Ujian">
    @if (session('status'))
        <x-alert type="success" dismissible class="mb-4">{{ session('status') }}</x-alert>
    @endif

    <div class="space-y-4">
        <x-card title="{{ $exam->title }}" subtitle="{{ $exam->subject->name }}">
            <div class="grid gap-3 sm:grid-cols-3">
                <div class="rounded-xl bg-slate-50 p-4">
                    <p class="text-xs text-slate-500">Status</p>
                    <p class="text-lg font-bold capitalize text-slate-900">{{ $examSession->status }}</p>
                </div>
                <div class="rounded-xl bg-slate-50 p-4">
                    <p class="text-xs text-slate-500">Nilai</p>
                    <p class="text-lg font-bold text-slate-900">{{ $examSession->score ?? '-' }}</p>
                </div>
                <div class="rounded-xl bg-slate-50 p-4">
                    <p class="text-xs text-slate-500">Pelanggaran</p>
                    <p class="text-lg font-bold text-slate-900">{{ $examSession->violation_count }}</p>
                </div>
            </div>
        </x-card>

        <x-card title="Ringkasan Jawaban">
            <div class="space-y-3">
                @foreach ($questions as $index => $question)
                    @php
                        $answer = $answers->get($question->id);
                    @endphp
                    <div class="rounded-xl border border-slate-200 p-4">
                        <div class="flex items-start justify-between gap-3">
                            <p class="text-sm font-semibold text-slate-800">{{ $index + 1 }}. {{ $question->body }}</p>
                            <span class="shrink-0 text-xs text-slate-500">{{ $question->points }} poin</span>
                        </div>
                        <p class="mt-2 text-sm text-slate-600">
                            Jawaban:
                            @if ($question->type === 'pg')
                                {{ $answer?->option?->text ?? 'Tidak dijawab' }}
                            @else
                                {{ $answer?->answer_text ?: 'Tidak dijawab' }}
                            @endif
                        </p>
                        <p class="mt-1 text-xs text-slate-500">
                            @if ($answer?->score !== null)
                                Skor: {{ $answer->score }}
                            @else
                                Menunggu koreksi guru
                            @endif
                        </p>
                    </div>
                @endforeach
            </div>

            <x-button class="mt-5" variant="secondary" :href="route('siswa.exams.index')">Kembali ke Daftar Ujian</x-button>
        </x-card>
    </div>
</x-student-layout>
