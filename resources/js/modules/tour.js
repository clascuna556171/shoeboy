/**
 * Guided tour with per-page scripts driven by the shared guide config
 * (config/guides.php → window.__GUIDES__). Spotlights real UI elements and,
 * for steps without a target (or whose target is not on the page), shows a
 * centered narrative card so nothing is silently skipped.
 *
 * Scrolling and positioning are decoupled: `showStep()` scrolls once when a
 * step changes, while `place()` repositions the spotlight + card on every
 * scroll/resize so they track the element through smooth scrolling and never
 * land on stale coordinates. Placement tries below → right → left → above and
 * always avoids covering the highlighted element.
 *
 * Exposed as `window.appTour`:
 *   - start(key, mode) → run a page tour (falls back to the general tour)
 *   - start()          → general tour
 *   mode: 'full' (default) | 'essentials'
 */
export default function registerTour() {
    const data = window.__GUIDES__ || {};
    const pages = data.pages || {};
    const general = data.general || { steps: [] };
    const role = data.role || null;
    const helpUrl = data.helpUrl || '';

    const MARGIN = 12;
    const GAP = 12;

    let root, spot, tip, arrow, countEl, titleEl, bodyEl, learnEl, nextBtn, backBtn;
    let steps = [];
    let index = 0;

    function findVisible(sel) {
        return Array.from(document.querySelectorAll(sel)).find((el) => {
            const r = el.getBoundingClientRect();
            return r.width > 0 && r.height > 0 && getComputedStyle(el).visibility !== 'hidden';
        }) || null;
    }

    function allows(step) {
        return !step.roles || (role && step.roles.includes(role));
    }

    function resolve(key) {
        if (key && pages[key]) return pages[key].steps || [];
        return general.steps || [];
    }

    function build() {
        root = document.createElement('div');
        root.id = 'app-tour';
        root.className = 'fixed inset-0 z-[200]';
        root.style.display = 'none';
        root.innerHTML = `
            <div data-tour-spot class="fixed rounded-xl pointer-events-none transition-[left,top,width,height] duration-100 ease-out"
                 style="box-shadow: 0 0 0 9999px rgba(0,0,0,.55), 0 0 0 2px rgba(255,255,255,.35) inset;"></div>
            <div data-tour-tip role="dialog" aria-modal="true" aria-labelledby="app-tour-title" tabindex="-1"
                 class="fixed w-[min(92vw,20rem)] rounded-2xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-[#1C1C1E] shadow-2xl p-4 outline-none">
                <div data-tour-arrow class="absolute" style="display:none;width:0;height:0"></div>
                <div data-tour-count class="text-[11px] font-semibold uppercase tracking-wider text-neutral-400 mb-1"></div>
                <h3 id="app-tour-title" data-tour-title class="font-bold text-sm text-[#1D1D1F] dark:text-white"></h3>
                <p data-tour-body aria-live="polite" class="mt-1 text-sm text-neutral-500 dark:text-neutral-400 leading-relaxed"></p>
                <a data-tour-learn href="#" class="mt-2 hidden text-xs font-semibold text-[#0071E3] dark:text-[#0A84FF] hover:underline">Learn more →</a>
                <div class="mt-3 flex items-center justify-between">
                    <button type="button" data-tour-skip class="text-xs font-semibold text-neutral-500 hover:text-neutral-800 dark:hover:text-neutral-200">Skip</button>
                    <div class="flex items-center gap-2">
                        <button type="button" data-tour-back class="app-btn app-btn-secondary app-btn-sm">Back</button>
                        <button type="button" data-tour-next class="app-btn app-btn-primary app-btn-sm">Next</button>
                    </div>
                </div>
            </div>`;
        document.body.appendChild(root);

        spot = root.querySelector('[data-tour-spot]');
        tip = root.querySelector('[data-tour-tip]');
        arrow = root.querySelector('[data-tour-arrow]');
        countEl = root.querySelector('[data-tour-count]');
        titleEl = root.querySelector('[data-tour-title]');
        bodyEl = root.querySelector('[data-tour-body]');
        learnEl = root.querySelector('[data-tour-learn]');
        nextBtn = root.querySelector('[data-tour-next]');
        backBtn = root.querySelector('[data-tour-back]');

        root.querySelector('[data-tour-skip]').addEventListener('click', () => end());
        backBtn.addEventListener('click', () => { if (index > 0) { index--; showStep(); } });
        nextBtn.addEventListener('click', () => nextOrEnd());

        document.addEventListener('keydown', onKey);
        window.addEventListener('resize', place);
        window.addEventListener('scroll', place, true);
    }

    function onKey(e) {
        if (!root || root.style.display === 'none') return;
        if (e.key === 'Escape') end();
        if (e.key === 'ArrowRight') nextOrEnd();
        if (e.key === 'ArrowLeft' && index > 0) { index--; showStep(); }
    }

    // Called when the step changes: fills content, scrolls once, positions.
    function showStep() {
        if (!root || root.style.display === 'none') return;
        const step = steps[index];
        if (!step) return end();

        countEl.textContent = `Step ${index + 1} of ${steps.length}`;
        titleEl.textContent = step.title;
        bodyEl.textContent = step.body;
        if (step.learn && helpUrl) {
            learnEl.href = helpUrl + '#' + step.learn;
            learnEl.classList.remove('hidden');
        } else {
            learnEl.classList.add('hidden');
        }
        backBtn.style.visibility = index === 0 ? 'hidden' : 'visible';
        nextBtn.textContent = index === steps.length - 1 ? 'Finish' : 'Next';

        const target = step.sel ? findVisible(step.sel) : null;
        if (target) {
            try { target.scrollIntoView({ block: 'center', behavior: 'smooth' }); } catch (e) { /* ignore */ }
        }
        // Position immediately, then keep tracking as the smooth scroll proceeds.
        place();
        tip.style.visibility = 'visible';
        try { tip.focus({ preventScroll: true }); } catch (e) { /* ignore */ }
    }

    // Called on scroll/resize: repositions only, never scrolls.
    function place() {
        if (!root || root.style.display === 'none') return;
        const step = steps[index];
        if (!step) return;
        const target = step.sel ? findVisible(step.sel) : null;

        if (!target) {
            spot.style.display = 'none';
            arrow.style.display = 'none';
            tip.style.left = Math.max(MARGIN, (window.innerWidth - tip.offsetWidth) / 2) + 'px';
            tip.style.top = Math.max(MARGIN, (window.innerHeight - tip.offsetHeight) / 2) + 'px';
            return;
        }

        spot.style.display = '';
        const rect = target.getBoundingClientRect();
        const pad = 8;
        spot.style.left = (rect.left - pad) + 'px';
        spot.style.top = (rect.top - pad) + 'px';
        spot.style.width = (rect.width + pad * 2) + 'px';
        spot.style.height = (rect.height + pad * 2) + 'px';

        const tipW = tip.offsetWidth;
        const tipH = tip.offsetHeight;
        const vw = window.innerWidth;
        const vh = window.innerHeight;

        const clamp = (v, lo, hi) => Math.min(Math.max(v, lo), Math.max(lo, hi));
        const fits = (l, t) => l >= MARGIN && t >= MARGIN && l <= vw - tipW - MARGIN && t <= vh - tipH - MARGIN;

        // Candidate placements, each kept clear of the target by a GAP.
        const candidates = [
            { side: 'bottom', left: clamp(rect.left, MARGIN, vw - tipW - MARGIN), top: rect.bottom + GAP },
            { side: 'right', left: rect.right + GAP, top: clamp(rect.top, MARGIN, vh - tipH - MARGIN) },
            { side: 'left', left: rect.left - GAP - tipW, top: clamp(rect.top, MARGIN, vh - tipH - MARGIN) },
            { side: 'top', left: clamp(rect.left, MARGIN, vw - tipW - MARGIN), top: rect.top - GAP - tipH },
        ];

        let chosen = candidates.find((c) => fits(c.left, c.top));

        if (!chosen) {
            // Very tall/large target: pick the candidate showing the most card.
            const score = (c) => {
                const l = clamp(c.left, MARGIN, vw - tipW - MARGIN);
                const t = clamp(c.top, MARGIN, vh - tipH - MARGIN);
                const onX = Math.max(0, tipW - Math.max(0, MARGIN - c.left) - Math.max(0, c.left + tipW - (vw - MARGIN)));
                const onY = Math.max(0, tipH - Math.max(0, MARGIN - c.top) - Math.max(0, c.top + tipH - (vh - MARGIN)));
                return onX * onY || (c.side === 'bottom' ? 1 : 0);
            };
            chosen = candidates.slice().sort((a, b) => score(b) - score(a))[0];
        }

        let left = clamp(chosen.left, MARGIN, vw - tipW - MARGIN);
        let top = clamp(chosen.top, MARGIN, vh - tipH - MARGIN);
        tip.style.left = left + 'px';
        tip.style.top = top + 'px';

        positionArrow(chosen.side, rect, left, top, tipW, tipH);
    }

    function positionArrow(side, rect, left, top, tipW, tipH) {
        const cx = rect.left + rect.width / 2;
        const cy = rect.top + rect.height / 2;
        const color = document.documentElement.classList.contains('dark') ? '#1C1C1E' : '#ffffff';
        const s = 6;
        const t = `solid transparent`;
        const solid = `${s}px solid ${color}`;
        arrow.style.cssText = 'position:absolute;width:0;height:0;display:block';
        arrow.style.borderTop = arrow.style.borderRight = arrow.style.borderBottom = arrow.style.borderLeft = '';
        if (side === 'bottom' || side === 'top') {
            const ax = clamp(cx - left - s, 12, tipW - 2 * s);
            arrow.style.left = ax + 'px';
            arrow.style[side === 'bottom' ? 'borderBottom' : 'borderTop'] = solid;
            arrow.style.borderLeft = `${s}px ${t}`;
            arrow.style.borderRight = `${s}px ${t}`;
            arrow.style.top = (side === 'bottom' ? -s : tipH) + 'px';
        } else {
            const ay = clamp(cy - top - s, 12, tipH - 2 * s);
            arrow.style.top = ay + 'px';
            arrow.style[side === 'right' ? 'borderRight' : 'borderLeft'] = solid;
            arrow.style.borderTop = `${s}px ${t}`;
            arrow.style.borderBottom = `${s}px ${t}`;
            arrow.style.left = (side === 'right' ? -s : tipW) + 'px';
        }
    }

    function clamp(v, lo, hi) { return Math.min(Math.max(v, lo), Math.max(lo, hi)); }

    function nextOrEnd() {
        if (index < steps.length - 1) { index++; showStep(); } else { end(); }
    }

    function start(key, mode) {
        if (!root) build();
        let list = resolve(key).filter(allows);
        if (mode === 'essentials') {
            const core = list.filter((s) => s.core);
            if (core.length) list = core;
        }
        if (!list.length) return;
        steps = list;
        index = 0;
        root.style.display = 'block';
        tip.style.visibility = 'hidden';
        showStep();
    }

    function end() {
        if (root) root.style.display = 'none';
        steps = [];
        index = 0;
        try { localStorage.setItem('shoeboy.tour.seen', '1'); } catch (e) { /* ignore */ }
        window.dispatchEvent(new CustomEvent('tour:closed'));
    }

    window.appTour = { start };
}
