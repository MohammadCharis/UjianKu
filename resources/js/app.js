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
