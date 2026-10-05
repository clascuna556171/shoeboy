/**
 * Report export builder (custom export modal).
 */
export default function registerExport(Alpine) {
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
}
