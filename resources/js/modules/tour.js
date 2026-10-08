/**
 * Lightweight guided tour with per-page scripts. Dims the page and spotlights
 * real UI elements with a tooltip card (Next / Back / Skip). Steps whose target
 * is not on the current page are skipped automatically.
 *
 * Exposed as `window.appTour`:
 *   - start(key)  → run the tour for a page key (falls back to a general tour)
 *   - start()     → general tour
 */
export default function registerTour() {
    const GENERAL = [
        { sel: '[data-tour="nav"]', title: 'Your main menu', body: 'Move between modules here. The order follows your daily flow: batches → inventory → selling → deliveries → money.' },
        { sel: '[data-tour="profile"]', title: 'Your account', body: 'Open this for page-by-page guides, Help, Settings, and Sign out.' },
        { sel: '[data-tour="search"]', title: 'Search', body: 'Find any record fast with the search box on a list page.' },
        { sel: '[data-tour="primary"]', title: 'Main action', body: 'The primary action for the current page lives here.' },
    ];

    const PAGE_TOURS = {
        dashboard: [
            { sel: '[data-tour="kpis"]', title: 'Operational snapshot', body: 'Your key numbers at a glance: cash, GCash, active reservations, and floor stock.' },
            { sel: '[data-tour="profit"]', title: 'Batch profitability', body: 'Unit economics per batch — outlay, sales, and order profit.' },
            { sel: '[data-tour="attention"]', title: 'Needs attention', body: 'Pending deliveries, expiring reservations, and the wash/repair pipeline.' },
        ],
        batches: [
            { sel: '[data-tour="search"]', title: 'Find a batch', body: 'Search by batch code or supplier.' },
            { sel: '[data-tour="primary"]', title: 'Record intake', body: 'Log a new bale shipment with its supplier, sacks, pairs, and cost.' },
            { sel: '[data-tour="view"]', title: 'Cards or table', body: 'Switch between the card view and a compact table.' },
        ],
        inventory: [
            { sel: '[data-tour="search"]', title: 'Search inventory', body: 'Look up a pair by SKU, brand, or model.' },
            { sel: '[data-tour="filters"]', title: 'Filters', body: 'Narrow by batch, sale status, or triage stage.' },
            { sel: '[data-tour="primary"]', title: 'Add a pair', body: 'Serialize a new pair into a batch. It starts in Washing until marked Ready.' },
        ],
        console: [
            { sel: '[data-tour="tabs"]', title: 'The console tabs', body: 'Live Claims, Walk-in POS, and the Triage Table all live here.' },
            { sel: '[data-tour="lookup"]', title: 'Short-code lookup', body: 'Type or pick a shoe code to start a claim.' },
        ],
        'console:claims': [
            { sel: '[data-tour="lookup"]', title: 'Shoe short-code lookup', body: 'Search a code, then add the pair to the claim ticket.' },
            { sel: '[data-tour="ticket"]', title: 'Live claim ticket', body: 'Set the buyer, lock the reservation, and verify payment when it arrives.' },
            { sel: '[data-tour="claims"]', title: 'Active stream claims', body: 'Reservations with countdowns. Verify payment or release back to stock.' },
        ],
        'console:pos': [
            { sel: '[data-tour="pos-catalog"]', title: 'Catalog', body: 'Filter by brand and tap Add to drop pairs into the walk-in ticket.' },
            { sel: '[data-tour="pos-ticket"]', title: 'Walk-in ticket', body: 'Review the pairs, apply a discount, and pick cash or GCash.' },
            { sel: '[data-tour="pos-payment"]', title: 'Payment', body: 'Enter cash received (change is computed) or the GCash reference, then complete the sale.' },
        ],
        'console:triage': [
            { sel: '[data-tour="triage-table"]', title: 'Triage worklist', body: 'Move pairs through Washing → Under repair → Ready. Only Ready pairs can be sold.' },
        ],
        orders: [
            { sel: '[data-tour="search"]', title: 'Search orders', body: 'Find an order by number, SKU, or customer.' },
        ],
        deliveries: [
            { sel: '[data-tour="search"]', title: 'Search deliveries', body: 'Find a parcel by order, buyer, or tracking number. Pending work is listed first.' },
        ],
        expenses: [
            { sel: '[data-tour="primary"]', title: 'Record an expense', body: 'Log a store or batch-linked cost. You can undo a deletion right after.' },
        ],
        reports: [
            { sel: '[data-tour="tabs"]', title: 'Report views', body: 'Switch between batch, session, tier, sales, and expense ledgers.' },
            { sel: '[data-tour="export"]', title: 'Export', body: 'Download the report as a spreadsheet.' },
        ],
        staff: [
            { sel: '[data-tour="primary"]', title: 'Add a staff account', body: 'Create staff logins and activate or deactivate access.' },
        ],
        suppliers: [
            { sel: '[data-tour="primary"]', title: 'Add a supplier', body: 'Keep a clean, reusable supplier list that batches reference.' },
        ],
        backups: [
            { sel: '[data-tour="primary"]', title: 'Backup & restore', body: 'Download a copy, create one on the server, or import a .sqlite backup.' },
            { sel: '[data-tour="list"]', title: 'Stored backups', body: 'Every snapshot is kept here — restore or delete as needed.' },
        ],
    };

    let root, spot, tip, countEl, titleEl, bodyEl;
    let steps = [];
    let index = 0;

    function findVisible(sel) {
        return Array.from(document.querySelectorAll(sel)).find((el) => {
            const r = el.getBoundingClientRect();
            return r.width > 0 && r.height > 0 && getComputedStyle(el).visibility !== 'hidden';
        }) || null;
    }

    function build() {
        root = document.createElement('div');
        root.id = 'app-tour';
        root.className = 'fixed inset-0 z-[200]';
        root.style.display = 'none';
        root.innerHTML = `
            <div data-tour-spot class="fixed rounded-xl pointer-events-none transition-all duration-200"
                 style="box-shadow: 0 0 0 9999px rgba(0,0,0,.55), 0 0 0 2px rgba(255,255,255,.35) inset;"></div>
            <div data-tour-tip class="fixed w-[min(92vw,20rem)] rounded-2xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-[#1C1C1E] shadow-2xl p-4">
                <div data-tour-count class="text-[11px] font-semibold uppercase tracking-wider text-neutral-400 mb-1"></div>
                <h3 data-tour-title class="font-bold text-sm text-[#1D1D1F] dark:text-white"></h3>
                <p data-tour-body class="mt-1 text-sm text-neutral-500 dark:text-neutral-400 leading-relaxed"></p>
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
        countEl = root.querySelector('[data-tour-count]');
        titleEl = root.querySelector('[data-tour-title]');
        bodyEl = root.querySelector('[data-tour-body]');

        root.querySelector('[data-tour-skip]').addEventListener('click', () => end());
        root.querySelector('[data-tour-back]').addEventListener('click', () => { if (index > 0) { index--; render(); } });
        root.querySelector('[data-tour-next]').addEventListener('click', () => { if (index < steps.length - 1) { index++; render(); } else { end(); } });

        document.addEventListener('keydown', onKey);
        window.addEventListener('resize', render);
        window.addEventListener('scroll', render, true);
    }

    function onKey(e) {
        if (!root || root.style.display === 'none') return;
        if (e.key === 'Escape') end();
        if (e.key === 'ArrowRight') { if (index < steps.length - 1) { index++; render(); } else { end(); } }
        if (e.key === 'ArrowLeft' && index > 0) { index--; render(); }
    }

    function render() {
        if (!root || root.style.display === 'none') return;
        const step = steps[index];
        if (!step) return end();
        const target = findVisible(step.sel);
        if (!target) return nextOrEnd();

        target.scrollIntoView({ block: 'center', behavior: 'smooth' });
        const rect = target.getBoundingClientRect();
        const pad = 8;

        spot.style.left = (rect.left - pad) + 'px';
        spot.style.top = (rect.top - pad) + 'px';
        spot.style.width = (rect.width + pad * 2) + 'px';
        spot.style.height = (rect.height + pad * 2) + 'px';

        tip.style.visibility = 'hidden';
        tip.style.left = '0px';
        tip.style.top = '0px';

        countEl.textContent = `Step ${index + 1} of ${steps.length}`;
        titleEl.textContent = step.title;
        bodyEl.textContent = step.body;
        root.querySelector('[data-tour-back]').style.visibility = index === 0 ? 'hidden' : 'visible';
        root.querySelector('[data-tour-next]').textContent = index === steps.length - 1 ? 'Finish' : 'Next';

        const tipW = tip.offsetWidth;
        const tipH = tip.offsetHeight;
        const vw = window.innerWidth;
        const vh = window.innerHeight;

        let top = rect.bottom + pad + 12;
        if (top + tipH > vh - 12) top = Math.max(12, rect.top - pad - 12 - tipH);
        let left = Math.min(Math.max(12, rect.left), vw - tipW - 12);

        tip.style.left = left + 'px';
        tip.style.top = top + 'px';
        tip.style.visibility = 'visible';
    }

    function nextOrEnd() {
        if (index < steps.length - 1) { index++; render(); } else { end(); }
    }

    function start(key) {
        if (!root) build();
        const script = (key && PAGE_TOURS[key]) ? PAGE_TOURS[key] : GENERAL;
        steps = script.filter((s) => findVisible(s.sel));
        if (!steps.length) return;
        index = 0;
        root.style.display = 'block';
        render();
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
