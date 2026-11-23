<x-student-layout title="Daftar Ujian">
    @if (session('status'))
        <x-alert type="success" dismissible class="mb-4">{{ session('status') }}</x-alert>
    @endif

    <div class="space-y-4">
        <x-card title="Ujian Aktif" subtitle="Masukkan token untuk memulai">
            @forelse ($activeExams as $exam)
                <div class="flex flex-col gap-3 rounded-xl border border-slate-200 p-4 sm:flex-row sm:items-center sm:justify-between">
                    <div class="min-w-0">
                        <p class="font-semibold text-slate-900">{{ $exam->title }}</p>
                        <p class="text-sm text-slate-500">{{ $exam->subject->name }} · {{ $exam->duration_min }} menit</p>
                        <p class="text-xs text-slate-400">Berakhir {{ $exam->end_at->format('d M Y, H:i') }}</p>
                    </div>
                    <x-button data-join-exam data-exam-id="{{ $exam->id }}">Masukkan Token</x-button>
                </div>
            @empty
                <p class="text-sm text-slate-500">Belum ada ujian aktif untuk kelas Anda.</p>
            @endforelse
        </x-card>

        @if ($sessions->isNotEmpty())
            <x-card title="Riwayat Ujian">
                <div class="space-y-2">
                    @foreach ($sessions as $session)
                        <div class="flex flex-col gap-2 rounded-xl border border-slate-200 p-3 sm:flex-row sm:items-center sm:justify-between">
                            <div class="min-w-0">
                                <p class="truncate text-sm font-semibold">{{ $session->exam->title }}</p>
                                <p class="text-xs capitalize text-slate-500">{{ $session->status }}</p>
                            </div>
                            @if ($session->status === App\Models\ExamSession::BERLANGSUNG)
                                <x-button size="sm" :href="route('siswa.exams.work', $session)">Lanjutkan</x-button>
                            @else
                                <x-button size="sm" variant="secondary" :href="route('siswa.exams.result', $session)">Lihat Hasil</x-button>
                            @endif
                        </div>
                    @endforeach
                </div>
            </x-card>
        @endif
    </div>

    <x-modal name="join-exam">
        <form method="POST" action="{{ route('siswa.exams.join') }}" class="p-5">
            @csrf
            <input type="hidden" id="join_exam_id" name="exam_id" value="">
            <h2 class="text-lg font-semibold text-slate-900">Masukkan Token Ujian</h2>
            <p class="mt-1 text-sm text-slate-500">Token diberikan oleh guru pengawas.</p>

            @if ($errors->any())
                <x-alert type="danger" class="mt-3">
                    {{ $errors->first('token') }}
                </x-alert>
            @endif

            <div class="mt-4">
                <x-input-label for="token" value="Token" />
                <x-text-input id="token" name="token" type="text" class="mt-1 w-full uppercase" :value="old('token')" required autocomplete="off" />
            </div>

            <div class="mt-5 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                <x-button data-modal-close variant="secondary">Batal</x-button>
                <x-button type="submit">Mulai Ujian</x-button>
            </div>
        </form>
    </x-modal>
</x-student-layout>
