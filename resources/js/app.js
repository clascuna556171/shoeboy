import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.store('dialog', {
    open: false,
    loading: false,
    variant: 'neutral',
    icon: null,
    title: '',
    message: '',
    confirmLabel: 'Confirm',
    cancelLabel: 'Cancel',
    _resolve: null,

    show(options = {}) {
        this.variant = options.variant || 'neutral';
        const defaults = { danger: 'trash', warning: 'warning', success: 'check', info: 'info', neutral: 'question' };
        this.icon = options.icon || defaults[this.variant] || 'question';
        this.title = options.title || 'Are you sure?';
        this.message = options.message || '';
        this.confirmLabel = options.confirmLabel || 'Confirm';
        this.cancelLabel = options.cancelLabel || 'Cancel';
        this.loading = false;
        this.open = true;

        return new Promise((resolve) => {
            this._resolve = resolve;
        });
    },

    close(result) {
        this.open = false;
        this.loading = false;
        const resolve = this._resolve;
        this._resolve = null;
        if (resolve) resolve(result);
    },

    // Confirm keeps the dialog open with a spinner on the primary button; the
    // caller then submits, and the reload replaces the page.
    confirm() {
        if (this.loading) return;
        this.loading = true;
        if (window.appLoading) window.appLoading.start();
        const resolve = this._resolve;
        this._resolve = null;
        if (resolve) resolve(true);
    },

    cancel() {
        if (this.loading) return;
        if (window.appLoading) window.appLoading.reset();
        this.close(false);
    },
});

Alpine.data('toastHub', (initial = []) => ({
    toasts: [],

    init() {
        (initial || []).forEach((toast) => this.push(toast));
        window.addEventListener('toast', (e) => this.push(e.detail || {}));
    },

    push(toast) {
        const id = Math.random().toString(36).slice(2);
        const duration = toast.duration ?? (toast.undo ? 8000 : (toast.link ? 6000 : (toast.type === 'error' ? 6000 : 4500)));
        const entry = {
            id,
            type: toast.type || 'info',
            message: toast.message || '',
            undo: toast.undo || null,
            link: toast.link || null,
            duration,
        };

        this.toasts.push(entry);

        setTimeout(() => this.dismiss(id), duration);
    },

    dismiss(id) {
        this.toasts = this.toasts.filter((toast) => toast.id !== id);
    },
}));


Alpine.data('reservationCountdown', (opts = {}) => ({
    now: Date.now(),
    expired: false,
    settled: false,
    busy: false,
    timer: null,

    get expiresAt() { return new Date(opts.expiresAt).getTime(); },
    get startedAt() { return new Date(opts.startedAt).getTime(); },
    get total() { return Math.max(1, this.expiresAt - this.startedAt); },
    get remainingMs() { return this.expiresAt - this.now; },
    get remainingSec() { return Math.max(0, Math.floor(this.remainingMs / 1000)); },
    get percent() { return Math.max(0, Math.min(100, (this.remainingMs / this.total) * 100)); },
    get urgent() { return this.remainingMs > 0 && (this.remainingSec <= 600 || this.percent <= 15); },
    get label() {
        let s = this.remainingSec;
        const h = Math.floor(s / 3600); s -= h * 3600;
        const m = Math.floor(s / 60); s -= m * 60;
        const pad = (n) => String(n).padStart(2, '0');
        return h > 0 ? `${h}:${pad(m)}:${pad(s)}` : `${m}:${pad(s)}`;
    },

    init() {
        this.tick();
        this.timer = setInterval(() => this.tick(), 1000);
    },
    destroy() {
        clearInterval(this.timer);
    },
    tick() {
        this.now = Date.now();
        if (!this.expired && this.remainingMs <= 0) {
            this.release();
        }
    },
    async release() {
        if (this.settled || this.busy) return;
        this.busy = true;
        try {
            const res = await fetch(opts.releaseUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                },
                body: JSON.stringify({ reason: 'Reservation window elapsed (auto-released)' }),
            });
            const data = await res.json().catch(() => ({}));
            if (data.retry) {
                this.busy = false;
                return;
            }
            this.settled = true;
            this.busy = false;
            if (data.released) {
                this.expired = true;
                window.dispatchEvent(new CustomEvent('toast', { detail: {
                    type: 'info',
                    message: data.message || `Reservation${opts.sku ? ' ' + opts.sku : ''} expired and was released.`,
                }}));
            }
        } catch (e) {
            this.busy = false;
        }
    },
}));


Alpine.store('exportModal', {
    open: false,
    show() { this.open = true; },
    hide() { this.open = false; },
});

Alpine.data('exportBuilder', (opts = {}) => ({
    preset: 'all',
    content: 'all',
    sections: Object.assign(
        { summary: true, sales: true, expenses: true, sessions: true, batches: true, tiers: true, inventory: true },
        opts.sections || {}
    ),
    channel: 'all',
    paymentMethod: 'all',
    orderStatus: 'paid_fulfilled',
    granularity: 'pair',
    batchId: '',
    staffId: '',
    inventoryStatus: 'all',
    includeRepair: true,
    includePaymentRef: true,
    includeCustomer: true,
    includeNotes: false,

    get isCustom() {
        return this.preset === 'custom';
    },
    get sectionCount() {
        return Object.values(this.sections).filter(Boolean).length;
    },
    applyContent(mode) {
        this.content = mode;
        if (mode === 'sales') {
            this.sections = { summary: true, sales: true, sessions: true, batches: true, tiers: true, expenses: false, inventory: false };
        } else if (mode === 'expenses') {
            this.sections = { summary: true, sales: false, sessions: false, batches: false, tiers: false, expenses: true, inventory: false };
        } else if (mode === 'all') {
            this.sections = { summary: true, sales: true, expenses: true, sessions: true, batches: true, tiers: true, inventory: true };
        }
    },
    toggleAll(value) {
        Object.keys(this.sections).forEach((key) => { this.sections[key] = value; });
    },
}));


Alpine.start();

function runPageEnhancements() {
    document.querySelectorAll('[data-scroll-to]').forEach((el) => {
        el.scrollIntoView({ behavior: 'smooth', block: 'center' });
    });

    const autofocus = document.querySelector('[data-autofocus]');
    if (autofocus) {
        autofocus.focus();
    }
}

document.addEventListener('DOMContentLoaded', runPageEnhancements);

// Delegated so print buttons work wherever they are rendered.
document.addEventListener('click', (event) => {
    const printButton = event.target.closest('[data-print]');
    if (printButton) {
        window.print();
    }
});

// Delegated confirm dialog. Any element with [data-confirm] opens the shared
// dialog, keeps it open with a spinner on confirm, then submits its form.
document.addEventListener('click', (event) => {
    const trigger = event.target.closest('[data-confirm]');
    if (!trigger) return;

    event.preventDefault();
    event.stopImmediatePropagation();

    const form = trigger.closest('form');
    const proceed = () => { if (form) form.submit(); };
    const store = window.Alpine && window.Alpine.store('dialog');

    if (!store) {
        proceed();
        return;
    }

    store.show({
        variant: trigger.dataset.confirmVariant || 'neutral',
        icon: trigger.dataset.confirmIcon || null,
        title: trigger.dataset.confirm,
        message: trigger.dataset.confirmMessage || '',
        confirmLabel: trigger.dataset.confirmLabel || 'Confirm',
    }).then((ok) => { if (ok) proceed(); });
}, true);

// ---------------------------------------------------------------------------
// Top loading bar + button spinners.
// One shared "I'm busy" switch: shows the thin bar at the top and, when we know
// which button started the request, sticks a spinner on that button.
// ---------------------------------------------------------------------------
(() => {
    const FLAG = 'shoeboy:nav';

    let track = null;
    let bar = null;
    let tickTimer = null;
    let showTimer = null;
    let safetyTimer = null;
    let active = false;
    let value = 0;
    let currentButton = null;

    // Some actions (file downloads) never navigate away, so we can't rely on
    // unload to stop the bar — finish shortly after the request is kicked off.
    function scheduleDownloadEnd() {
        setTimeout(done, 1200);
    }

    function refs() {
        if (!track) {
            track = document.getElementById('app-progress');
            bar = track ? track.firstElementChild : null;
        }
        return track && bar;
    }

    function paint(v) {
        value = v;
        if (bar) bar.style.transform = `scaleX(${v / 100})`;
    }

    function clearButton() {
        if (!currentButton) return;
        currentButton.classList.remove('is-loading', 'is-compact');
        currentButton.removeAttribute('aria-busy');
        currentButton = null;
    }

    function isCompact(button) {
        const mode = button.dataset.loading;
        if (mode === 'inline') return false;
        if (mode === 'replace') return true;
        if (button.classList.contains('app-btn-sm')) return true;
        if (button.offsetWidth && button.offsetWidth < 96) return true;
        if (!(button.textContent || '').trim()) return true;
        return false;
    }

    function markButton(button) {
        if (!(button instanceof HTMLElement)) return;
        clearButton();
        currentButton = button;
        button.classList.add('is-loading');
        if (isCompact(button)) button.classList.add('is-compact');
        button.setAttribute('aria-busy', 'true');
    }

    function tick() {
        if (!active) return;
        const remaining = 90 - value;
        const increment = Math.max(0.4, remaining * 0.08);
        paint(Math.min(90, value + increment));
        tickTimer = setTimeout(tick, Math.max(140, 260 - value * 2));
    }

    function start(button, isNav = true) {
        if (isNav) rememberNav();
        if (button) markButton(button);
        if (active) return;
        active = true;

        // Hard safety net so a stuck request can never spin forever.
        if (safetyTimer) clearTimeout(safetyTimer);
        safetyTimer = setTimeout(done, 15000);

        // Small delay so instant navigations don't flash the bar.
        showTimer = setTimeout(() => {
            showTimer = null;
            if (!refs()) return;
            track.classList.add('is-active');
            bar.style.transition = 'none';
            paint(0);
            requestAnimationFrame(() => {
                bar.style.transition = 'transform 0.2s ease-out';
                tick();
            });
        }, 120);
    }

    function sweep() {
        if (!refs()) return;
        active = true;
        track.classList.add('is-active');
        bar.style.transition = 'none';
        paint(30);
        requestAnimationFrame(() => {
            bar.style.transition = 'transform 0.2s ease-out';
            paint(100);
            setTimeout(() => {
                track.classList.remove('is-active');
                setTimeout(() => {
                    bar.style.transition = 'none';
                    paint(0);
                    bar.style.transition = '';
                    active = false;
                }, 220);
            }, 160);
        });
    }

    function done() {
        if (safetyTimer) { clearTimeout(safetyTimer); safetyTimer = null; }
        if (showTimer) { clearTimeout(showTimer); showTimer = null; }
        if (!active) { clearButton(); return; }
        active = false;
        clearTimeout(tickTimer);
        if (!refs()) { clearButton(); return; }
        bar.style.transition = 'transform 0.22s ease-out';
        paint(100);
        setTimeout(() => {
            if (track) track.classList.remove('is-active');
            setTimeout(() => {
                bar.style.transition = 'none';
                paint(0);
                bar.style.transition = '';
                clearButton();
            }, 220);
        }, 160);
    }

    function reset() {
        if (safetyTimer) { clearTimeout(safetyTimer); safetyTimer = null; }
        if (showTimer) { clearTimeout(showTimer); showTimer = null; }
        clearTimeout(tickTimer);
        tickTimer = null;
        active = false;
        if (refs()) {
            track.classList.remove('is-active');
            bar.style.transition = 'none';
            paint(0);
            bar.style.transition = '';
        }
        clearButton();
        try { sessionStorage.removeItem(FLAG); } catch (e) { /* ignore */ }
    }

    window.appLoading = { start, done, reset };

    function rememberNav() {
        try { sessionStorage.setItem(FLAG, '1'); } catch (e) { /* ignore */ }
    }

    function isNavigableLink(a) {
        if (!a || !a.href) return false;
        if (a.target && a.target !== '_self') return false;
        if (a.hasAttribute('download')) return false;
        if (a.dataset.progress === 'off') return false;
        const href = a.getAttribute('href') || '';
        if (href.startsWith('#')) return false;
        const url = new URL(a.href, location.href);
        if (url.origin !== location.origin) return false;
        if (url.href === location.href) return false;
        return true;
    }

    document.addEventListener('click', (event) => {
        if (event.defaultPrevented) return;
        if (event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;
        const link = event.target.closest('a[href]');
        if (!link || !isNavigableLink(link)) return;
        const button = link.classList.contains('app-btn') ? link : null;
        if (link.dataset.progress === 'download') {
            start(button, false);
            scheduleDownloadEnd();
        } else {
            start(button);
        }
    }, true);

    document.addEventListener('submit', (event) => {
        const form = event.target;
        if (!(form instanceof HTMLFormElement)) return;
        if (form.dataset.progress === 'off') return;
        if (form.target && form.target !== '_self') return;
        const button = event.submitter
            || form.querySelector('button[type="submit"], input[type="submit"], button:not([type])');
        if (form.dataset.progress === 'download') {
            start(button, false);
            scheduleDownloadEnd();
        } else {
            start(button);
        }
    }, true);

    // Finish animation when we land after an in-app navigation.
    function finishFromNav() {
        let navigated = false;
        try {
            navigated = sessionStorage.getItem(FLAG) === '1';
            sessionStorage.removeItem(FLAG);
        } catch (e) { /* ignore */ }
        if (navigated) sweep();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', finishFromNav);
    } else {
        finishFromNav();
    }

    window.addEventListener('pageshow', (event) => {
        if (event.persisted) reset();
    });
})();
