const csrfToken = () => document.querySelector('meta[name="csrf-token"]')?.content ?? '';

const toggleSidebar = (open) => {
    const sidebar = document.getElementById('app-sidebar');
    const backdrop = document.getElementById('app-sidebar-backdrop');

    if (!sidebar) {
        return;
    }

    const next = open ?? sidebar.dataset.open !== 'true';
    sidebar.dataset.open = String(next);
    if (backdrop) {
        backdrop.dataset.open = String(next);
    }
    document.querySelectorAll('[data-sidebar-toggle]').forEach((button) => {
        button.setAttribute('aria-expanded', String(next));
    });
};

const openOverlay = (id) => {
    const overlay = document.getElementById(id);
    if (overlay instanceof HTMLDialogElement && !overlay.open) {
        overlay.showModal();
    }
};

const closeOverlay = (overlay) => {
    if (overlay instanceof HTMLDialogElement && overlay.open) {
        overlay.close();
    }
};

window.UjianKu = {
    csrfToken,
    toggleSidebar,
    openOverlay,
    closeOverlay,
    request: async (url, options = {}) => fetch(url, {
        ...options,
        headers: {
            Accept: 'application/json',
            'X-CSRF-TOKEN': csrfToken(),
            ...(options.headers ?? {}),
        },
    }),
};

document.querySelectorAll('[data-sidebar-toggle]').forEach((button) => {
    button.addEventListener('click', () => toggleSidebar());
});

document.querySelectorAll('[data-sidebar-close]').forEach((button) => {
    button.addEventListener('click', () => toggleSidebar(false));
});

document.getElementById('app-sidebar-backdrop')?.addEventListener('click', () => toggleSidebar(false));

document.addEventListener('click', (event) => {
    const trigger = event.target.closest('[data-modal-open], [data-bottom-sheet-open]');
    if (trigger) {
        event.preventDefault();
        openOverlay(trigger.dataset.modalOpen ?? trigger.dataset.bottomSheetOpen);
        return;
    }

    const closer = event.target.closest('[data-modal-close]');
    if (closer) {
        event.preventDefault();
        closeOverlay(closer.closest('dialog'));
        return;
    }

    if (event.target.matches('dialog')) {
        closeOverlay(event.target);
    }

    document.querySelectorAll('details[data-dropdown][open]').forEach((dropdown) => {
        if (!dropdown.contains(event.target)) {
            dropdown.open = false;
        }
    });
});

document.querySelectorAll('[data-alert-dismiss]').forEach((button) => {
    button.addEventListener('click', () => button.closest('[data-alert]')?.remove());
});

document.querySelectorAll('[data-auto-dismiss]').forEach((element) => {
    window.setTimeout(() => element.remove(), Number(element.dataset.autoDismiss) || 2000);
});

document.querySelectorAll('[data-modal-autopen]').forEach((element) => openOverlay(element.id));

document.addEventListener('click', (event) => {
    const addOption = event.target.closest('[data-option-add]');
    if (addOption) {
        event.preventDefault();
        const section = addOption.closest('[data-question-options]');
        const list = section?.querySelector('[data-option-list]');
        const template = section?.querySelector('template');
        if (section && list && template) {
            const index = Number(section.dataset.nextIndex ?? 0);
            list.insertAdjacentHTML('beforeend', template.innerHTML.replaceAll('__INDEX__', String(index)));
            section.dataset.nextIndex = String(index + 1);
        }
        return;
    }

    const removeOption = event.target.closest('[data-option-remove]');
    if (removeOption) {
        event.preventDefault();
        removeOption.closest('[data-option-row]')?.remove();
    }
});

document.addEventListener('change', (event) => {
    const typeSelect = event.target.closest('[data-question-type]');
    if (typeSelect) {
        const section = document.querySelector('[data-question-options]');
        if (section) {
            section.hidden = typeSelect.value !== 'pg';
        }
    }

    const questionToggle = event.target.closest('[data-exam-question-toggle]');
    if (questionToggle) {
        const points = questionToggle.closest('[data-exam-question-row]')?.querySelector('[data-exam-question-points]');
        if (points) {
            points.disabled = !questionToggle.checked;
        }
    }
});

document.addEventListener('click', (event) => {
    const joinButton = event.target.closest('[data-join-exam]');
    if (joinButton) {
        event.preventDefault();
        const examId = document.getElementById('join_exam_id');
        if (examId) {
            examId.value = joinButton.dataset.examId ?? '';
        }
        openOverlay('join-exam');
        return;
    }

    const zoomButton = event.target.closest('[data-image-zoom]');
    if (zoomButton) {
        event.preventDefault();
        const image = document.querySelector('[data-zoom-image]');
        if (image) {
            image.src = zoomButton.dataset.imageZoom ?? '';
            openOverlay('image-zoom');
        }
    }
});

const examSessions = [...document.querySelectorAll('[data-exam-session]')];
const textTimers = new WeakMap();

const formatRemaining = (seconds) => {
    const minutes = String(Math.floor(seconds / 60)).padStart(2, '0');
    const remainder = String(seconds % 60).padStart(2, '0');
    return `${minutes}:${remainder}`;
};

const queueKey = (sessionId) => `ujianku:answers:${sessionId}`;

const readQueue = (sessionId) => {
    try {
        const parsed = JSON.parse(localStorage.getItem(queueKey(sessionId)) ?? '[]');
        return Array.isArray(parsed) ? parsed : [];
    } catch {
        return [];
    }
};

const writeQueue = (sessionId, queue) => {
    localStorage.setItem(queueKey(sessionId), JSON.stringify(queue));
};

const setSaveStatus = (text) => {
    document.querySelectorAll('[data-save-status]').forEach((element) => {
        element.textContent = text;
    });
};

const markQuestionState = (root, payload) => {
    root.querySelectorAll(`[data-question-goto][data-question-id="${payload.question_id}"]`).forEach((button) => {
        button.dataset.answered = payload.has_answer ? 'true' : 'false';
        button.dataset.doubtful = payload.is_doubtful ? 'true' : 'false';
    });
};

const sendAnswer = async (root, payload) => {
    try {
        const response = await window.UjianKu.request(root.dataset.answerUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload),
        });

        if (!response.ok) {
            throw new Error('Network response was not ok');
        }

        const data = await response.json();
        const time = data.saved_at ? new Date(data.saved_at).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }) : '';
        setSaveStatus(time ? `Tersimpan ${time}` : 'Tersimpan');
        markQuestionState(root, payload);

        return true;
    } catch {
        const queue = readQueue(root.dataset.sessionId).filter((item) => item.question_id !== payload.question_id);
        queue.push(payload);
        writeQueue(root.dataset.sessionId, queue);
        setSaveStatus(navigator.onLine ? 'Gagal, akan dicoba lagi' : 'Offline, akan dikirim ulang');

        return false;
    }
};

const flushQueue = async (root) => {
    const queue = readQueue(root.dataset.sessionId);

    if (queue.length === 0) {
        return;
    }

    setSaveStatus('Mengirim ulang...');
    const remaining = [];

    for (const payload of queue) {
        const saved = await sendAnswer(root, payload);
        if (!saved) {
            remaining.push(payload);
        }
    }

    writeQueue(root.dataset.sessionId, remaining);

    if (remaining.length === 0) {
        setSaveStatus('Tersimpan');
    }
};

const submitExam = async (root) => {
    if (root.dataset.submitting === 'true') {
        return;
    }

    root.dataset.submitting = 'true';
    setSaveStatus('Menyimpan...');
    await flushQueue(root);

    try {
        const response = await window.UjianKu.request(root.dataset.submitUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: '{}',
        });

        if (!response.ok) {
            throw new Error('Network response was not ok');
        }

        const data = await response.json();
        window.location.assign(data.redirect);
    } catch {
        root.dataset.submitting = 'false';
        setSaveStatus('Gagal mengirim, mencoba lagi');
        window.setTimeout(() => submitExam(root), 5000);
    }
};

document.querySelectorAll('[data-submit-exam]').forEach((button) => {
    button.addEventListener('click', () => {
        const root = button.closest('[data-exam-session]');
        if (root) {
            submitExam(root);
        }
    });
});

window.addEventListener('online', () => {
    examSessions.forEach((root) => flushQueue(root));
});

examSessions.forEach((root) => {
    const pages = [...root.querySelectorAll('[data-question-page]')];
    root.dataset.currentIndex = '0';

    const showQuestion = (index) => {
        if (index < 0 || index >= pages.length) {
            return;
        }

        pages.forEach((page, pageIndex) => page.classList.toggle('hidden', pageIndex !== index));
        root.querySelectorAll('[data-question-goto]').forEach((button) => {
            button.dataset.current = Number(button.dataset.questionIndex) === index ? 'true' : 'false';
        });

        const position = root.querySelector('[data-question-position]');
        if (position) {
            position.textContent = `${index + 1} / ${pages.length}`;
        }

        const previous = root.querySelector('[data-question-prev]');
        const next = root.querySelector('[data-question-next]');
        if (previous) {
            previous.disabled = index === 0;
        }
        if (next) {
            next.disabled = index === pages.length - 1;
        }

        root.dataset.currentIndex = String(index);
        closeOverlay(document.getElementById('question-sheet'));
    };

    showQuestion(0);

    root.addEventListener('click', (event) => {
        const goto = event.target.closest('[data-question-goto]');
        if (goto) {
            showQuestion(Number(goto.dataset.questionIndex));
            return;
        }

        if (event.target.closest('[data-question-prev]')) {
            showQuestion(Number(root.dataset.currentIndex) - 1);
            return;
        }

        if (event.target.closest('[data-question-next]')) {
            showQuestion(Number(root.dataset.currentIndex) + 1);
        }
    });

    const saveFromInput = (input) => {
        const page = input.closest('[data-question-page]');
        const option = page?.querySelector('[data-answer-option]:checked');
        const text = page?.querySelector('[data-answer-text]')?.value ?? null;
        const doubtful = page?.querySelector('[data-doubtful]')?.checked ?? false;
        const payload = {
            question_id: Number(input.dataset.questionId),
            option_id: option ? Number(option.value) : null,
            answer_text: text,
            is_doubtful: doubtful,
            has_answer: option !== null || (text ?? '').trim() !== '',
        };

        setSaveStatus('Menyimpan...');
        sendAnswer(root, payload);
    };

    root.querySelectorAll('[data-answer-text]').forEach((input) => {
        input.addEventListener('input', () => {
            window.clearTimeout(textTimers.get(input));
            textTimers.set(input, window.setTimeout(() => saveFromInput(input), 600));
        });
    });

    root.querySelectorAll('[data-answer-option], [data-doubtful]').forEach((input) => {
        input.addEventListener('change', () => saveFromInput(input));
    });

    const timerDisplay = document.querySelector('[data-timer-display]');
    let deadline = Number(root.dataset.serverNowMs) + Number(root.dataset.remainingSeconds) * 1000;

    const syncTimer = async () => {
        try {
            const response = await window.UjianKu.request(root.dataset.heartbeatUrl, { method: 'POST' });
            if (!response.ok) {
                return;
            }
            const data = await response.json();
            deadline = Number(data.server_now_ms) + Number(data.remaining_seconds) * 1000;

            if (data.session_status === 'terkunci' && root.dataset.locked !== 'true') {
                const overlay = root.querySelector('[data-lock-overlay]');
                if (overlay) {
                    overlay.classList.remove('hidden');
                    overlay.classList.add('flex');
                }
                root.dataset.locked = 'true';
            }

            if (data.session_status === 'berlangsung' && root.dataset.locked === 'true') {
                window.location.reload();
            }
        } catch {
            setSaveStatus('Sinkronisasi tertunda');
        }
    };

    window.setInterval(() => {
        const remaining = Math.max(0, Math.ceil((deadline - Date.now()) / 1000));
        if (timerDisplay) {
            timerDisplay.textContent = formatRemaining(remaining);
        }
        if (remaining === 0) {
            submitExam(root);
        }
    }, 1000);

    window.setInterval(syncTimer, 30000);
    syncTimer();
    flushQueue(root);

    if (root.dataset.locked === 'true') {
        const initialOverlay = root.querySelector('[data-lock-overlay]');
        if (initialOverlay) {
            initialOverlay.classList.remove('hidden');
            initialOverlay.classList.add('flex');
        }
    }

    let pendingViolation = null;
    const isMobile = /Android|iPhone|iPad|iPod/i.test(navigator.userAgent) || window.matchMedia('(max-width: 768px)').matches;

    const showViolationPopup = (count, max, type) => {
        const countElement = document.querySelector('[data-violation-count]');
        const maxElement = document.querySelector('[data-violation-max]');
        const typeElement = document.querySelector('[data-violation-type]');
        if (countElement) countElement.textContent = count;
        if (maxElement) maxElement.textContent = max;
        if (typeElement) typeElement.textContent = type.replaceAll('_', ' ');
        openOverlay('violation-warning');
    };

    const lockScreen = () => {
        const overlay = root.querySelector('[data-lock-overlay]');
        if (overlay) {
            overlay.classList.remove('hidden');
            overlay.classList.add('flex');
        }
        root.dataset.locked = 'true';
    };

    const sendViolation = async (type, duration, violationId = null) => {
        const payload = { type, duration_seconds: duration };
        if (violationId) {
            payload.violation_id = violationId;
        }

        try {
            const response = await window.UjianKu.request(root.dataset.violationUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload),
            });

            if (!response.ok) {
                throw new Error('Network response was not ok');
            }

            const data = await response.json();

            if (data.status === 'recorded') {
                showViolationPopup(data.violation_count, data.max_violations, type);
            }

            if (data.locked) {
                lockScreen();
            }

            if (data.auto_submitted && data.redirect) {
                window.location.assign(data.redirect);
            }

            return data;
        } catch {
            setSaveStatus('Gagal mencatat pelanggaran');
            return null;
        }
    };

    const startPendingViolation = (type) => {
        if (root.dataset.locked === 'true' || pendingViolation) {
            return;
        }

        pendingViolation = {
            type,
            startedAt: Date.now(),
            recorded: false,
            id: null,
            timer: window.setTimeout(async () => {
                if (!pendingViolation || root.dataset.locked === 'true') {
                    return;
                }

                if (type === 'pindah_tab' && !document.hidden) {
                    return;
                }

                const data = await sendViolation(type, 0);
                if (data) {
                    pendingViolation.recorded = true;
                    pendingViolation.id = data.violation_id;
                }
            }, 3000),
        };
    };

    const finishPendingViolation = async () => {
        if (!pendingViolation) {
            return;
        }

        window.clearTimeout(pendingViolation.timer);

        if (pendingViolation.recorded && pendingViolation.id) {
            const duration = Math.max(0, Math.round((Date.now() - pendingViolation.startedAt) / 1000));
            await sendViolation(pendingViolation.type, duration, pendingViolation.id);
        }

        pendingViolation = null;
    };

    document.addEventListener('visibilitychange', () => {
        if (document.hidden) {
            startPendingViolation('pindah_tab');
        } else {
            finishPendingViolation();
        }
    });

    if (!isMobile) {
        window.addEventListener('blur', () => {
            if (document.querySelector('dialog[open]')) {
                return;
            }
            startPendingViolation('keluar_fokus');
        });

        window.addEventListener('focus', () => {
            finishPendingViolation();
        });

        document.addEventListener('fullscreenchange', () => {
            if (document.fullscreenElement) {
                root.dataset.wasFullscreen = 'true';
                return;
            }

            if (root.dataset.wasFullscreen === 'true') {
                startPendingViolation('keluar_fullscreen');
                window.setTimeout(finishPendingViolation, 3000);
                root.dataset.wasFullscreen = 'false';
            }
        });

        document.documentElement.requestFullscreen?.().catch(() => {});

        const recordImmediateViolation = (type) => {
            if (root.dataset.locked === 'true') {
                return;
            }
            sendViolation(type, 0);
        };

        document.addEventListener('copy', (event) => {
            event.preventDefault();
            recordImmediateViolation('copy');
        });

        document.addEventListener('paste', (event) => {
            event.preventDefault();
            recordImmediateViolation('paste');
        });

        document.addEventListener('contextmenu', (event) => {
            event.preventDefault();
            recordImmediateViolation('klik_kanan');
        });

        document.addEventListener('keydown', (event) => {
            const usesShortcut = (event.ctrlKey || event.metaKey) && ['c', 'v', 'u'].includes(event.key.toLowerCase());

            if (usesShortcut || event.key === 'F12' || event.key === 'PrintScreen') {
                event.preventDefault();
                recordImmediateViolation('shortcut');
            }
        });
    }
});
