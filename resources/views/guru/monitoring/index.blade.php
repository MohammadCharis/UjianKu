<x-app-layout>
    <x-slot name="header">Pengawasan Ujian</x-slot>

    <div
        data-monitoring
        data-data-url="{{ route('guru.monitoring.data') }}"
        data-unlock-template="{{ route('guru.monitoring.unlock', ['examSession' => '__ID__']) }}"
        data-add-time-template="{{ route('guru.monitoring.add-time', ['examSession' => '__ID__']) }}"
        data-warn-template="{{ route('guru.monitoring.warn', ['examSession' => '__ID__']) }}"
        data-disqualify-template="{{ route('guru.monitoring.disqualify', ['examSession' => '__ID__']) }}"
        class="space-y-4"
    >
        <x-card>
            <div class="grid gap-3 md:grid-cols-4">
                <div>
                    <label for="monitor-class" class="text-xs font-semibold text-slate-500">Kelas</label>
                    <select id="monitor-class" data-monitor-class class="mt-1 min-h-11 w-full rounded-xl border-slate-300 text-sm">
                        <option value="">Semua kelas</option>
                        @foreach ($classes as $class)
                            <option value="{{ $class->id }}">{{ $class->name }}</option>
                        @endforeach
                    </select>
                </div>

                <label class="flex min-h-11 items-center gap-2 rounded-xl bg-slate-50 px-3 text-sm text-slate-700">
                    <input type="checkbox" data-monitor-problematic class="h-5 w-5 rounded border-slate-300 text-brand-600">
                    Hanya bermasalah
                </label>

                <label class="flex min-h-11 items-center gap-2 rounded-xl bg-slate-50 px-3 text-sm text-slate-700">
                    <input type="checkbox" data-monitor-locked class="h-5 w-5 rounded border-slate-300 text-brand-600">
                    Hanya terkunci
                </label>

                <div class="flex items-center justify-end">
                    <span data-monitor-badge class="hidden rounded-full bg-red-600 px-3 py-1 text-xs font-bold text-white">0</span>
                </div>
            </div>

            <div class="mt-4 grid gap-3 sm:grid-cols-4">
                <div class="rounded-xl bg-slate-50 p-3">
                    <p class="text-xs text-slate-500">Sesi Aktif</p>
                    <p data-summary-active class="text-2xl font-bold text-slate-900">0</p>
                </div>
                <div class="rounded-xl bg-slate-50 p-3">
                    <p class="text-xs text-slate-500">Online</p>
                    <p data-summary-online class="text-2xl font-bold text-emerald-700">0</p>
                </div>
                <div class="rounded-xl bg-slate-50 p-3">
                    <p class="text-xs text-slate-500">Terkunci</p>
                    <p data-summary-locked class="text-2xl font-bold text-red-700">0</p>
                </div>
                <div class="rounded-xl bg-slate-50 p-3">
                    <p class="text-xs text-slate-500">Bermasalah</p>
                    <p data-summary-problematic class="text-2xl font-bold text-amber-700">0</p>
                </div>
            </div>
        </x-card>

        <div class="space-y-3 md:hidden" data-monitoring-cards></div>

        <x-card class="hidden md:block">
            <x-responsive-table>
                <thead class="bg-slate-50 text-xs uppercase text-slate-500">
                    <tr>
                        <th class="px-3 py-3">Siswa</th>
                        <th class="px-3 py-3">Kelas</th>
                        <th class="px-3 py-3">Ujian</th>
                        <th class="px-3 py-3">Pelanggaran</th>
                        <th class="px-3 py-3">Durasi</th>
                        <th class="px-3 py-3">Waktu</th>
                        <th class="px-3 py-3">Status</th>
                        <th class="px-3 py-3 text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody data-monitoring-rows class="divide-y divide-slate-100"></tbody>
            </x-responsive-table>
        </x-card>
    </div>

    <script>
        (function () {
            const root = document.querySelector('[data-monitoring]');
            if (!root) return;

            const dataUrl = root.dataset.dataUrl;
            let lastAlertId = 0;
            let hasLoaded = false;

            const levelLabel = {
                ringan: 'Ringan',
                sedang: 'Sedang',
                berat: 'Berat',
            };

            const typeLabel = (type) => type.replaceAll('_', ' ');

            const beep = () => {
                try {
                    const context = new (window.AudioContext || window.webkitAudioContext)();
                    const oscillator = context.createOscillator();
                    const gain = context.createGain();
                    oscillator.connect(gain);
                    gain.connect(context.destination);
                    oscillator.frequency.value = 880;
                    gain.gain.value = 0.12;
                    oscillator.start();
                    oscillator.stop(context.currentTime + 0.18);
                } catch (error) {
                    // Audio is optional.
                }
            };

            const actionUrl = (template, sessionId) => template.replace('__ID__', sessionId);

            const renderActions = (alert) => {
                const sessionId = alert.session_id;
                return `
                    <div class="flex flex-wrap justify-end gap-2">
                        <button type="button" data-action="unlock" data-session-id="${sessionId}" class="min-h-9 rounded-lg bg-emerald-600 px-3 text-xs font-semibold text-white">Buka Kunci</button>
                        <button type="button" data-action="add-time" data-session-id="${sessionId}" class="min-h-9 rounded-lg bg-sky-600 px-3 text-xs font-semibold text-white">+5 Menit</button>
                        <button type="button" data-action="warn" data-session-id="${sessionId}" class="min-h-9 rounded-lg bg-amber-500 px-3 text-xs font-semibold text-white">Peringatan</button>
                        <button type="button" data-action="disqualify" data-session-id="${sessionId}" class="min-h-9 rounded-lg bg-red-600 px-3 text-xs font-semibold text-white">Diskualifikasi</button>
                    </div>
                `;
            };

            const cardHtml = (alert) => `
                <article class="rounded-2xl border ${alert.session_status === 'terkunci' ? 'border-red-300 bg-red-50' : 'border-slate-200 bg-white'} p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="font-semibold text-slate-900">${alert.student_name}</p>
                            <p class="text-xs text-slate-500">${alert.nis} · ${alert.class_name} · ${alert.exam_title}</p>
                        </div>
                        <span class="rounded-full px-2 py-1 text-xs font-bold ${alert.level === 'berat' ? 'bg-red-100 text-red-700' : alert.level === 'sedang' ? 'bg-amber-100 text-amber-700' : 'bg-slate-100 text-slate-700'}">
                            ${levelLabel[alert.level] || alert.level}
                        </span>
                    </div>
                    <p class="mt-2 text-sm text-slate-700">${typeLabel(alert.type)} · Pelanggaran ke-${alert.violation_number} dari ${alert.max_violations}</p>
                    <p class="text-xs text-slate-500">Durasi ${alert.duration_seconds} detik · ${alert.occurred_at} · ${alert.session_status}</p>
                    <div class="mt-3">${renderActions(alert)}</div>
                </article>
            `;

            const rowHtml = (alert) => `
                <tr>
                    <td class="px-3 py-3">
                        <p class="font-medium">${alert.student_name}</p>
                        <p class="text-xs text-slate-500">${alert.nis}</p>
                    </td>
                    <td class="px-3 py-3">${alert.class_name}</td>
                    <td class="px-3 py-3">${alert.exam_title}</td>
                    <td class="px-3 py-3">
                        <p class="font-medium">${typeLabel(alert.type)}</p>
                        <p class="text-xs text-slate-500">Ke-${alert.violation_number} dari ${alert.max_violations}</p>
                    </td>
                    <td class="px-3 py-3">${alert.duration_seconds} detik</td>
                    <td class="px-3 py-3 text-xs">${alert.occurred_at}</td>
                    <td class="px-3 py-3">
                        <span class="rounded-full px-2 py-1 text-xs font-semibold ${alert.session_status === 'terkunci' ? 'bg-red-100 text-red-700' : 'bg-emerald-100 text-emerald-700'}">
                            ${alert.session_status}
                        </span>
                    </td>
                    <td class="px-3 py-3">${renderActions(alert)}</td>
                </tr>
            `;

            const runAction = async (action, sessionId) => {
                const templateMap = {
                    unlock: root.dataset.unlockTemplate,
                    'add-time': root.dataset.addTimeTemplate,
                    warn: root.dataset.warnTemplate,
                    disqualify: root.dataset.disqualifyTemplate,
                };

                const url = actionUrl(templateMap[action], sessionId);
                const body = action === 'add-time' ? JSON.stringify({ minutes: 5 }) : '{}';

                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                    },
                    body,
                });

                if (response.ok) {
                    await load();
                }
            };

            const load = async () => {
                const params = new URLSearchParams();
                const classId = root.querySelector('[data-monitor-class]')?.value;
                const problematic = root.querySelector('[data-monitor-problematic]')?.checked;
                const locked = root.querySelector('[data-monitor-locked]')?.checked;

                if (classId) params.set('class_id', classId);
                if (problematic) params.set('only_problematic', '1');
                if (locked) params.set('only_locked', '1');

                const response = await fetch(`${dataUrl}?${params.toString()}`, {
                    headers: { 'Accept': 'application/json' },
                });

                if (!response.ok) return;

                const data = await response.json();
                const alerts = data.alerts || [];

                root.querySelector('[data-summary-active]').textContent = data.summary.active_sessions;
                root.querySelector('[data-summary-online]').textContent = data.summary.online;
                root.querySelector('[data-summary-locked]').textContent = data.summary.locked;
                root.querySelector('[data-summary-problematic]').textContent = data.summary.problematic;

                const badge = root.querySelector('[data-monitor-badge]');
                if (data.summary.locked > 0) {
                    badge.textContent = data.summary.locked;
                    badge.classList.remove('hidden');
                } else {
                    badge.classList.add('hidden');
                }

                const cards = root.querySelector('[data-monitoring-cards]');
                const rows = root.querySelector('[data-monitoring-rows]');
                if (cards) cards.innerHTML = alerts.map(cardHtml).join('') || '<p class="text-sm text-slate-500">Belum ada alert pelanggaran.</p>';
                if (rows) rows.innerHTML = alerts.map(rowHtml).join('') || '<tr><td colspan="8" class="px-3 py-6 text-center text-slate-500">Belum ada alert pelanggaran.</td></tr>';

                if (alerts.length > 0) {
                    const newestId = alerts[0].id;
                    if (hasLoaded && newestId > lastAlertId) {
                        beep();
                        if (navigator.vibrate) navigator.vibrate([200, 100, 200]);
                    }
                    lastAlertId = Math.max(lastAlertId, newestId);
                }

                hasLoaded = true;
            };

            root.addEventListener('click', (event) => {
                const button = event.target.closest('[data-action]');
                if (!button) return;
                runAction(button.dataset.action, button.dataset.sessionId);
            });

            root.querySelector('[data-monitor-class]')?.addEventListener('change', load);
            root.querySelector('[data-monitor-problematic]')?.addEventListener('change', load);
            root.querySelector('[data-monitor-locked]')?.addEventListener('change', load);

            load();
            window.setInterval(load, 5000);
        })();
    </script>
</x-app-layout>
