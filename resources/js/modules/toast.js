/**
 * Toast notifications.
 *
 * `x-data="toastHub({{ Js::from($toasts) }})"` seeds initial toasts from the
 * server; anything can push a new one by dispatching a `window` `toast` event
 * with `{ type, message, undo?, link?, duration? }`.
 */
export default function registerToast(Alpine) {
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
}
