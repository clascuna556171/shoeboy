/**
 * Shared confirm dialog.
 *
 * `$store.dialog.show({...})` opens the dialog and returns a Promise that
 * resolves to `true` (confirmed) or `false` (cancelled). Confirm keeps the
 * dialog open with a spinner on the primary button while the caller submits.
 */
export default function registerDialog(Alpine) {
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
}
