<div data-option-row class="grid grid-cols-[3rem_1fr_auto] items-start gap-2 rounded-xl border border-slate-200 p-3 sm:grid-cols-[4rem_1fr_auto]">
    <input
        type="text"
        name="options[{{ $index }}][label]"
        maxlength="5"
        value="{{ old('options.'.$index.'.label', $option?->label ?? '') }}"
        class="min-h-11 w-full rounded-lg border-slate-300 text-center text-sm"
        aria-label="Label opsi"
    >

    <div>
        <input
            type="text"
            name="options[{{ $index }}][text]"
            value="{{ old('options.'.$index.'.text', $option?->text ?? '') }}"
            class="min-h-11 w-full rounded-lg border-slate-300 text-sm"
            placeholder="Teks pilihan jawaban"
            aria-label="Teks opsi"
        >
        <label class="mt-2 flex items-center gap-2 text-xs text-slate-600">
            <input type="hidden" name="options[{{ $index }}][is_correct]" value="0">
            <input
                type="checkbox"
                name="options[{{ $index }}][is_correct]"
                value="1"
                @checked(old('options.'.$index.'.is_correct', $option?->is_correct ?? false))
                class="h-5 w-5 rounded border-slate-300 text-brand-600"
            >
            Kunci jawaban
        </label>
    </div>

    <button type="button" data-option-remove aria-label="Hapus opsi" class="min-h-11 rounded-lg px-3 text-sm text-red-600 hover:bg-red-50">
        Hapus
    </button>
</div>
