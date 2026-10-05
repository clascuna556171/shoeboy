/**
 * Page-level behaviour: sticky-header height, scroll memory, autofocus/scroll
 * targets, print buttons, and the delegated confirm dialog.
 */
export default function registerLayout() {
    // Sticky table headers park just below the top nav.
    const header = document.querySelector('header');
    if (header) {
        const setHeight = () => {
            document.documentElement.style.setProperty('--app-header-h', header.offsetHeight + 'px');
        };
        setHeight();
        window.addEventListener('resize', setHeight);
        if ('ResizeObserver' in window) {
            new ResizeObserver(setHeight).observe(header);
        }
    }

    registerScrollMemory();
    registerEnhancements();
    registerPrintButtons();
    registerConfirmDialog();
}

/**
 * Remember scroll position across in-page actions. Non-GET submits are keyed to
 * the current URL; same-page GET navigations (sort, filters, pagination) are
 * keyed to their destination URL. Cross-page links start fresh.
 *
 * The actual restore lives in a pre-paint inline script in the layout.
 */
function registerScrollMemory() {
    const PREFIX = 'shoeboy:scroll:';

    // Canonicalise the query (sorted params) so the saved key matches the
    // browser's own serialisation regardless of field order.
    const canonicalKey = (pathname, search) => {
        const params = new URLSearchParams(search || '');
        const sorted = [...params.entries()].sort((a, b) => (a[0] < b[0] ? -1 : a[0] > b[0] ? 1 : 0));
        const qs = new URLSearchParams(sorted).toString();
        return pathname + (qs ? '?' + qs : '');
    };

    const save = (key) => {
        try { sessionStorage.setItem(PREFIX + key, String(window.scrollY)); } catch (e) { /* ignore */ }
    };

    const remember = () => save(canonicalKey(location.pathname, location.search));
    window.appScroll = { remember };

    // Capture phase so we save even when an inline handler preventDefaults.
    document.addEventListener('submit', (event) => {
        const form = event.target;
        if (!(form instanceof HTMLFormElement)) return;

        if ((form.getAttribute('method') || 'get').toLowerCase() === 'get') {
            // GET forms (search/filter) change the query — key by destination.
            try {
                const dest = new URL(form.getAttribute('action') || location.href, location.href);
                new FormData(form).forEach((value, field) => dest.searchParams.set(field, value));
                save(canonicalKey(dest.pathname, dest.search));
            } catch (e) { /* ignore */ }
            return;
        }

        remember();
    }, true);

    // Same-page GET links: sort headers, pagination, reset/clear.
    document.addEventListener('click', (event) => {
        if (event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;
        const link = event.target.closest('a[href]');
        if (!link || (link.target && link.target !== '_self') || link.hasAttribute('download')) return;

        const href = link.getAttribute('href') || '';
        if (href.startsWith('#')) return;

        try {
            const dest = new URL(link.href, location.href);
            if (dest.origin !== location.origin) return;
            if (dest.pathname !== location.pathname) return;   // same page only
            if (dest.href === location.href) return;            // no-op
            save(canonicalKey(dest.pathname, dest.search));
        } catch (e) { /* ignore */ }
    }, true);
}

/** Smooth-scroll to `[data-scroll-to]` targets and focus `[data-autofocus]`. */
function registerEnhancements() {
    const run = () => {
        document.querySelectorAll('[data-scroll-to]').forEach((el) => {
            el.scrollIntoView({ behavior: 'smooth', block: 'center' });
        });

        const autofocus = document.querySelector('[data-autofocus]');
        if (autofocus) autofocus.focus();
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', run);
    } else {
        run();
    }
}

/** Delegated so print buttons work wherever they are rendered. */
function registerPrintButtons() {
    document.addEventListener('click', (event) => {
        if (event.target.closest('[data-print]')) window.print();
    });
}

/**
 * Delegated confirm dialog. Any element with `[data-confirm]` opens the shared
 * dialog, keeps it open with a spinner on confirm, then submits its form.
 */
function registerConfirmDialog() {
    document.addEventListener('click', (event) => {
        const trigger = event.target.closest('[data-confirm]');
        if (!trigger) return;

        event.preventDefault();
        event.stopImmediatePropagation();

        const form = trigger.closest('form');
        const proceed = () => {
            if (!form) return;
            // Native form.submit() fires no submit event, so remember scroll here.
            if (window.appScroll) window.appScroll.remember();
            form.submit();
        };

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
}
