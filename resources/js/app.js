import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.store('dialog', {
    open: false,
    variant: 'neutral',
    title: '',
    message: '',
    confirmLabel: 'Confirm',
    cancelLabel: 'Cancel',
    _resolve: null,

    show(options = {}) {
        this.variant = options.variant || 'neutral';
        this.title = options.title || 'Are you sure?';
        this.message = options.message || '';
        this.confirmLabel = options.confirmLabel || 'Confirm';
        this.cancelLabel = options.cancelLabel || 'Cancel';
        this.open = true;

        return new Promise((resolve) => {
            this._resolve = resolve;
        });
    },

    close(result) {
        this.open = false;
        const resolve = this._resolve;
        this._resolve = null;
        if (resolve) resolve(result);
    },

    confirm() {
        this.close(true);
    },

    cancel() {
        this.close(false);
    },
});

Alpine.data('toastHub', (initial = []) => ({
    toasts: [],

    init() {
        (initial || []).forEach((toast) => this.push(toast));
    },

    push(toast) {
        const id = Math.random().toString(36).slice(2);
        const entry = {
            id,
            type: toast.type || 'info',
            message: toast.message || '',
            undo: toast.undo || null,
        };

        this.toasts.push(entry);

        setTimeout(() => this.dismiss(id), entry.undo ? 8000 : 4500);
    },

    dismiss(id) {
        this.toasts = this.toasts.filter((toast) => toast.id !== id);
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
