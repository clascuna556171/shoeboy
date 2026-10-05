/**
 * Lazily-loaded detail panels (order / receipt / audit).
 *
 * One host modal lives in the layout; triggers just carry `data-panel-url`.
 * Panels are fetched on demand and cached, so page loads never ship per-row
 * modal markup. Hover/focus preloads the panel so opening feels instant.
 */
export default function registerLazyModal(Alpine) {
    Alpine.store('lazyModal', {
        open: false,
        loading: false,
        html: '',
        title: '',
        eyebrow: '',
        size: 'md',
        type: 'order',
        _cache: new Map(),

        // The panel type/width is known from the URL, so the shell (and its
        // matching skeleton) renders at the correct size before the fetch starts.
        _metaForUrl(url) {
            if (url.includes('/panel/receipt/')) return { type: 'receipt', size: 'md' };
            if (url.includes('/panel/audit/')) return { type: 'audit', size: 'md' };
            return { type: 'order', size: 'lg' };
        },

        _apply(data) {
            this.title = data.title || '';
            this.eyebrow = data.eyebrow || '';
            if (data.size) this.size = data.size;
            if (data.type) this.type = data.type;
            this.html = data.html || '';
        },

        async _fetch(url) {
            if (this._cache.has(url)) return this._cache.get(url);
            const res = await fetch(url, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            });
            if (!res.ok) throw new Error('Panel request failed: ' + res.status);
            const data = await res.json();
            this._cache.set(url, data);
            return data;
        },

        preload(url) {
            if (!url || this._cache.has(url)) return;
            this._fetch(url).catch(() => { /* ignore */ });
        },

        async openPanel(url) {
            if (!url) return;

            const meta = this._metaForUrl(url);
            this.type = meta.type;
            this.size = meta.size;
            this.html = '';
            this.title = '';
            this.eyebrow = '';
            this._resetPanelHeight();
            this.open = true;

            // Preloaded/cached: show content instantly, no skeleton flash.
            if (this._cache.has(url)) {
                this._apply(this._cache.get(url));
                this.loading = false;
                return;
            }

            this.loading = true;
            try {
                const data = await this._fetch(url);
                if (!this.open) return;

                // FLIP: measure the skeleton, swap to content, animate to the new height.
                const panel = this._panel();
                const from = panel ? panel.offsetHeight : 0;
                this._apply(data);
                this.loading = false;

                if (panel) {
                    await Alpine.nextTick();
                    this._animateHeight(panel, from, panel.offsetHeight);
                }
            } catch (e) {
                this.open = false;
                this.loading = false;
                window.dispatchEvent(new CustomEvent('toast', {
                    detail: { type: 'error', message: 'Could not load details. Please try again.' },
                }));
            }
        },

        _panel() {
            return document.querySelector('[data-lazy-panel]');
        },

        _resetPanelHeight() {
            const panel = this._panel();
            if (!panel) return;
            panel.style.transition = '';
            panel.style.height = '';
            panel.style.overflow = '';
        },

        _animateHeight(panel, from, to) {
            if (!from || !to || Math.abs(from - to) < 2) {
                this._resetPanelHeight();
                return;
            }

            panel.style.transition = 'none';
            panel.style.height = from + 'px';
            panel.style.overflow = 'hidden';
            void panel.offsetHeight; // force reflow
            panel.style.transition = 'height 0.15s ease-out';
            panel.style.height = to + 'px';

            const finish = (event) => {
                if (event && event.target !== panel) return;
                if (event && event.propertyName && event.propertyName !== 'height') return;
                panel.removeEventListener('transitionend', finish);
                panel.style.transition = '';
                panel.style.height = '';
                panel.style.overflow = '';
            };

            panel.addEventListener('transitionend', finish);
            setTimeout(finish, 220); // fallback if transitionend never fires
        },

        close() {
            this.open = false;
            this.loading = false;
            this.html = '';
            this._resetPanelHeight();
        },
    });

    const store = () => window.Alpine?.store('lazyModal');

    // Click / Enter open the panel.
    document.addEventListener('click', (event) => {
        if (event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;
        const trigger = event.target.closest('[data-panel-url]');
        if (!trigger) return;
        event.preventDefault();
        store()?.openPanel(trigger.dataset.panelUrl);
    }, true);

    document.addEventListener('keydown', (event) => {
        if (event.key !== 'Enter' && event.key !== ' ') return;
        const trigger = event.target.closest('[data-panel-url]');
        if (!trigger) return;
        event.preventDefault();
        store()?.openPanel(trigger.dataset.panelUrl);
    });

    // Hover / focus preload.
    const preload = (event) => {
        const trigger = event.target.closest('[data-panel-url]');
        if (trigger) store()?.preload(trigger.dataset.panelUrl);
    };
    document.addEventListener('mouseover', preload);
    document.addEventListener('focusin', preload);
}
