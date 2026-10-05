/**
 * Top loading bar + button spinners.
 *
 * One shared "I'm busy" switch: shows the thin bar at the top and, when we know
 * which button started the request, sticks a spinner on that button. Exposed as
 * `window.appLoading = { start, done, reset }`.
 */
export default function registerProgress() {
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
        // Inline @submit.prevent handlers (confirm dialogs, POS validation) own
        // their submissions — don't start the bar before they decide.
        if (event.defaultPrevented) return;
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
    }, false);

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
}
