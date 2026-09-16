import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.data('shoeBoyApp', () => ({
    // Appearance & Navigation
    darkMode: localStorage.getItem('shoeboy_theme') === 'dark' || (!('shoeboy_theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches),
    activeTab: 'claims', // 'claims', 'pos', 'triage', 'ledger'
    currentTime: '',
    showShortcutsModal: false,
    showReceiptModal: false,
    lastTransaction: null,
    toasts: [],

    // Active Batch Context
    activeBatch: 'B04',
    batches: [
        { id: 'B04', code: 'B04', name: 'Bale B04 - Men\'s Basketball Mix', arrived: '2026-03-12', sackCost: 18000, pairCount: 24, baseCost: 750, supplier: 'Cebu Port Import Bales' },
        { id: 'B05', code: 'B05', name: 'Bale B05 - Premium Retro Runners', arrived: '2026-03-14', sackCost: 21600, pairCount: 24, baseCost: 900, supplier: 'Davao Footwear Distributors' }
    ],

    // Live Claims State
    claimInput: '',
    buyerInput: '',
    selectedClaimShoe: null,
    drawerOpen: true,
    claimFilter: 'all', // 'all', 'pending', 'paid'

    // POS State
    posSearch: '',
    posBrandFilter: 'All',
    posSizeFilter: 'All',
    posConditionFilter: 'All',
    posCart: [],
    posDiscount: 0,
    posDiscountNote: '',
    posPaymentMethod: 'cash', // 'cash', 'gcash'
    posCashTendered: '',
    posGcashRef: '',

    // Triage State
    triageSearch: '',
    triageStatusFilter: 'all',
    triageConditionFilter: 'all',

    // Financial Ledger State
    ledgerChannelFilter: 'all', // 'all', 'Live Stream', 'Walk-In POS'
    newExpenseCategory: 'Shipping & Freight',
    newExpenseDescription: '',
    newExpenseAmount: '',
    newExpenseDate: new Date().toISOString().slice(0, 10),

    expenses: [
        { id: 1, date: '2026-03-12', category: 'Sack Purchase', description: 'Bale B04 (24 pairs basketball mix)', amount: 18000 },
        { id: 2, date: '2026-03-13', category: 'Shipping & Freight', description: 'Sea freight cargo delivery fee from Cebu', amount: 1850 },
        { id: 3, date: '2026-03-14', category: 'Shoe Restoration', description: 'Deep cleaner foam, brushes & sole contact cement', amount: 920 },
        { id: 4, date: '2026-03-15', category: 'Packaging & Labels', description: 'J&T parcel pouches & thermal label rolls', amount: 650 },
        { id: 5, date: '2026-03-16', category: 'Store Utilities', description: 'Physical shop power & lighting allowance', amount: 1200 }
    ],

    salesLedger: [
        {
            id: 'TX-2026-001',
            date: '2026-03-16 14:22',
            channel: 'Live Stream',
            sku: 'B04-008',
            brand: 'Nike',
            model: 'Kobe 6 Protro "Grinch"',
            size: 'US 10.0',
            condition: 'Pristine',
            grossSale: 6500,
            baseCost: 750,
            repairCost: 0,
            netProfit: 5750,
            paymentMethod: 'GCash',
            paymentRef: 'GCASH-992182746',
            buyer: '@adrian_sole'
        },
        {
            id: 'TX-2026-002',
            date: '2026-03-16 16:45',
            channel: 'Walk-In POS',
            sku: 'B04-014',
            brand: 'Nike',
            model: 'Ja 1 "Day One"',
            size: 'US 10.5',
            condition: 'Pristine',
            grossSale: 3800,
            baseCost: 750,
            repairCost: 0,
            netProfit: 3050,
            paymentMethod: 'Cash',
            paymentRef: 'DRAWER-TX-002',
            buyer: 'Walk-In Customer'
        }
    ],

    // Full Sneaker Catalog (Sack B04 & B05)
    catalog: [
        {
            id: 1,
            sku: 'B04-001',
            batch: 'B04',
            brand: 'Li-Ning',
            model: 'Way of Wade 10 "South Beach"',
            size: 'US 10.5',
            condition: 'Pristine',
            base_cost: 750,
            repair_cost: 0,
            target_price: 4500,
            sold_price: null,
            status: 'available',
            claimed_by: null,
            reserved_at: null,
            expires_at: null,
            payment_ref: null,
            category: 'Basketball'
        },
        {
            id: 2,
            sku: 'B04-002',
            batch: 'B04',
            brand: 'Anta',
            model: 'KT 8 "Splash Party"',
            size: 'US 9.0',
            condition: 'Good',
            base_cost: 750,
            repair_cost: 0,
            target_price: 3200,
            sold_price: null,
            status: 'reserved',
            claimed_by: '@ken_hoops23',
            reserved_at: new Date(Date.now() - 35 * 60 * 1000).toISOString(),
            expires_at: new Date(Date.now() + 85 * 60 * 1000).toISOString(),
            payment_ref: null,
            category: 'Basketball'
        },
        {
            id: 3,
            sku: 'B04-003',
            batch: 'B04',
            brand: 'Peak',
            model: 'Taichi Flash 4 "Underground"',
            size: 'US 10.0',
            condition: 'Good',
            base_cost: 750,
            repair_cost: 0,
            target_price: 2800,
            sold_price: null,
            status: 'available',
            claimed_by: null,
            reserved_at: null,
            expires_at: null,
            payment_ref: null,
            category: 'Basketball'
        },
        {
            id: 4,
            sku: 'B04-004',
            batch: 'B04',
            brand: 'Nike',
            model: 'Dunk Low Retro "Panda"',
            size: 'US 8.5',
            condition: 'Pristine',
            base_cost: 750,
            repair_cost: 0,
            target_price: 4200,
            sold_price: null,
            status: 'available',
            claimed_by: null,
            reserved_at: null,
            expires_at: null,
            payment_ref: null,
            category: 'Lifestyle'
        },
        {
            id: 5,
            sku: 'B04-005',
            batch: 'B04',
            brand: 'Jordan',
            model: 'Air Jordan 1 Retro High OG "Chicago Lost & Found"',
            size: 'US 11.0',
            condition: 'Pristine',
            base_cost: 750,
            repair_cost: 0,
            target_price: 5800,
            sold_price: null,
            status: 'reserved',
            claimed_by: '@davao_sneakerhead',
            reserved_at: new Date(Date.now() - 105 * 60 * 1000).toISOString(),
            expires_at: new Date(Date.now() + 15 * 60 * 1000).toISOString(),
            payment_ref: null,
            category: 'Basketball'
        },
        {
            id: 6,
            sku: 'B04-006',
            batch: 'B04',
            brand: 'Li-Ning',
            model: 'Yushuai 16 V2 Low',
            size: 'US 9.5',
            condition: 'Fair',
            base_cost: 750,
            repair_cost: 150,
            target_price: 2400,
            sold_price: null,
            status: 'under_repair',
            claimed_by: null,
            reserved_at: null,
            expires_at: null,
            payment_ref: null,
            category: 'Basketball'
        },
        {
            id: 7,
            sku: 'B04-007',
            batch: 'B04',
            brand: 'Anta',
            model: 'Shock The Game 6.0',
            size: 'US 10.5',
            condition: 'Fair',
            base_cost: 750,
            repair_cost: 0,
            target_price: 2200,
            sold_price: null,
            status: 'washing',
            claimed_by: null,
            reserved_at: null,
            expires_at: null,
            payment_ref: null,
            category: 'Basketball'
        },
        {
            id: 8,
            sku: 'B04-008',
            batch: 'B04',
            brand: 'Nike',
            model: 'Kobe 6 Protro "Grinch"',
            size: 'US 10.0',
            condition: 'Pristine',
            base_cost: 750,
            repair_cost: 0,
            target_price: 6500,
            sold_price: 6500,
            status: 'sold',
            claimed_by: '@adrian_sole',
            reserved_at: null,
            expires_at: null,
            payment_ref: 'GCASH-992182746',
            category: 'Basketball'
        },
        {
            id: 9,
            sku: 'B04-009',
            batch: 'B04',
            brand: 'Asics',
            model: 'Gel-Kayano 14 "Silver Cream"',
            size: 'US 8.0',
            condition: 'Pristine',
            base_cost: 750,
            repair_cost: 0,
            target_price: 3900,
            sold_price: null,
            status: 'available',
            claimed_by: null,
            reserved_at: null,
            expires_at: null,
            payment_ref: null,
            category: 'Running'
        },
        {
            id: 10,
            sku: 'B04-010',
            batch: 'B04',
            brand: 'Peak',
            model: 'Attitude Basketball Mid',
            size: 'US 11.5',
            condition: 'Good',
            base_cost: 750,
            repair_cost: 0,
            target_price: 2600,
            sold_price: null,
            status: 'available',
            claimed_by: null,
            reserved_at: null,
            expires_at: null,
            payment_ref: null,
            category: 'Basketball'
        },
        {
            id: 11,
            sku: 'B04-011',
            batch: 'B04',
            brand: 'Jordan',
            model: 'Air Jordan 4 Retro "Military Black"',
            size: 'US 9.0',
            condition: 'Good',
            base_cost: 750,
            repair_cost: 0,
            target_price: 5200,
            sold_price: null,
            status: 'available',
            claimed_by: null,
            reserved_at: null,
            expires_at: null,
            payment_ref: null,
            category: 'Basketball'
        },
        {
            id: 12,
            sku: 'B04-012',
            batch: 'B04',
            brand: 'Li-Ning',
            model: 'Jimmy Butler JB1 "Tough"',
            size: 'US 10.0',
            condition: 'Pristine',
            base_cost: 750,
            repair_cost: 0,
            target_price: 3600,
            sold_price: null,
            status: 'available',
            claimed_by: null,
            reserved_at: null,
            expires_at: null,
            payment_ref: null,
            category: 'Basketball'
        },
        {
            id: 13,
            sku: 'B04-013',
            batch: 'B04',
            brand: 'Anta',
            model: 'Gordon Hayward GH3 "Racing"',
            size: 'US 9.5',
            condition: 'Good',
            base_cost: 750,
            repair_cost: 0,
            target_price: 2900,
            sold_price: null,
            status: 'available',
            claimed_by: null,
            reserved_at: null,
            expires_at: null,
            payment_ref: null,
            category: 'Basketball'
        },
        {
            id: 14,
            sku: 'B04-014',
            batch: 'B04',
            brand: 'Nike',
            model: 'Ja 1 "Day One"',
            size: 'US 10.5',
            condition: 'Pristine',
            base_cost: 750,
            repair_cost: 0,
            target_price: 3800,
            sold_price: 3800,
            status: 'sold',
            claimed_by: null,
            reserved_at: null,
            expires_at: null,
            payment_ref: 'DRAWER-TX-002',
            category: 'Basketball'
        },
        {
            id: 15,
            sku: 'B04-015',
            batch: 'B04',
            brand: 'New Balance',
            model: '550 "White Green"',
            size: 'US 8.5',
            condition: 'Good',
            base_cost: 750,
            repair_cost: 0,
            target_price: 3100,
            sold_price: null,
            status: 'available',
            claimed_by: null,
            reserved_at: null,
            expires_at: null,
            payment_ref: null,
            category: 'Lifestyle'
        },
        {
            id: 16,
            sku: 'B04-016',
            batch: 'B04',
            brand: 'Peak',
            model: 'Lou Williams Streetball Master',
            size: 'US 11.0',
            condition: 'Fair',
            base_cost: 750,
            repair_cost: 120,
            target_price: 2100,
            sold_price: null,
            status: 'under_repair',
            claimed_by: null,
            reserved_at: null,
            expires_at: null,
            payment_ref: null,
            category: 'Basketball'
        }
    ],

    init() {
        // Sync theme with DOM immediately
        if (this.darkMode) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }

        this.updateClock();
        setInterval(() => this.updateClock(), 1000);
        setInterval(() => this.tickTimers(), 1000);

        // Keyboard navigation setup
        window.addEventListener('keydown', (e) => {
            if (['input', 'textarea', 'select'].includes(e.target.tagName.toLowerCase())) {
                if (e.key === 'Escape') {
                    e.target.blur();
                    this.selectedClaimShoe = null;
                }
                return;
            }

            if (e.key === '1') {
                this.activeTab = 'claims';
                this.$nextTick(() => document.getElementById('claimSearchInput')?.focus());
            } else if (e.key === '2') {
                this.activeTab = 'pos';
                this.$nextTick(() => document.getElementById('posSearchInput')?.focus());
            } else if (e.key === '3') {
                this.activeTab = 'triage';
            } else if (e.key === '4') {
                this.activeTab = 'ledger';
            } else if ((e.metaKey || e.ctrlKey) && e.key.toLowerCase() === 'k') {
                e.preventDefault();
                if (this.activeTab === 'claims') {
                    document.getElementById('claimSearchInput')?.focus();
                } else if (this.activeTab === 'pos') {
                    document.getElementById('posSearchInput')?.focus();
                } else if (this.activeTab === 'triage') {
                    document.getElementById('triageSearchInput')?.focus();
                }
            } else if (e.key === '?') {
                e.preventDefault();
                this.showShortcutsModal = !this.showShortcutsModal;
            } else if (e.key === 'Escape') {
                this.showShortcutsModal = false;
                this.showReceiptModal = false;
            }
        });

        // Watch for claim search input changes
        this.$watch('claimInput', (val) => {
            this.handleClaimSearch(val);
        });
    },

    updateClock() {
        const now = new Date();
        this.currentTime = now.toLocaleTimeString('en-US', { hour12: false, hour: '2-digit', minute: '2-digit', second: '2-digit' });
    },

    tickTimers() {
        const now = Date.now();
        this.catalog.forEach(shoe => {
            if (shoe.status === 'reserved' && shoe.expires_at) {
                const exp = new Date(shoe.expires_at).getTime();
                if (exp <= now) {
                    shoe.status = 'available';
                    const buyer = shoe.claimed_by;
                    shoe.claimed_by = null;
                    shoe.reserved_at = null;
                    shoe.expires_at = null;
                    this.pushToast('Reservation Expired', `${shoe.sku} (${shoe.brand} ${shoe.model}) held by ${buyer} returned to stock.`, 'warning');
                }
            }
        });
    },

    toggleTheme() {
        this.darkMode = !this.darkMode;
        if (this.darkMode) {
            document.documentElement.classList.add('dark');
            localStorage.setItem('shoeboy_theme', 'dark');
        } else {
            document.documentElement.classList.remove('dark');
            localStorage.setItem('shoeboy_theme', 'light');
        }
    },

    pushToast(title, message, type = 'info') {
        const id = Date.now() + Math.random();
        this.toasts.push({ id, title, message, type });
        setTimeout(() => {
            this.toasts = this.toasts.filter(t => t.id !== id);
        }, 4000);
    },

    // ----------------------------------------------------
    // LIVE CLAIMS LOGIC
    // ----------------------------------------------------
    handleClaimSearch(query) {
        if (!query || query.trim() === '') {
            this.selectedClaimShoe = null;
            return;
        }
        const clean = query.trim().toUpperCase();
        const found = this.catalog.find(s => s.sku.toUpperCase() === clean) ||
                      this.catalog.find(s => s.sku.toUpperCase().includes(clean)) ||
                      this.catalog.find(s => `${s.brand} ${s.model}`.toUpperCase().includes(clean));
        this.selectedClaimShoe = found || null;
    },

    confirmClaim() {
        if (!this.selectedClaimShoe) return;
        if (this.selectedClaimShoe.status === 'reserved') {
            this.pushToast('Item Already Reserved', `${this.selectedClaimShoe.sku} is currently held by ${this.selectedClaimShoe.claimed_by}.`, 'warning');
            return;
        }
        if (this.selectedClaimShoe.status === 'sold') {
            this.pushToast('Item Already Sold', `${this.selectedClaimShoe.sku} has already been settled and sold.`, 'error');
            return;
        }

        const buyer = this.buyerInput.trim() || '@live_viewer_' + Math.floor(100 + Math.random() * 900);
        const now = new Date();
        const twoHoursLater = new Date(now.getTime() + 2 * 60 * 60 * 1000);

        this.selectedClaimShoe.status = 'reserved';
        this.selectedClaimShoe.claimed_by = buyer.startsWith('@') ? buyer : '@' + buyer;
        this.selectedClaimShoe.reserved_at = now.toISOString();
        this.selectedClaimShoe.expires_at = twoHoursLater.toISOString();

        this.pushToast('Reservation Locked', `${this.selectedClaimShoe.sku} locked for ${this.selectedClaimShoe.claimed_by}. 2-hour payment window active.`, 'success');

        this.claimInput = '';
        this.buyerInput = '';
        this.selectedClaimShoe = null;
        this.$nextTick(() => document.getElementById('claimSearchInput')?.focus());
    },

    releaseClaim(sku) {
        const shoe = this.catalog.find(s => s.sku === sku);
        if (shoe) {
            const prevBuyer = shoe.claimed_by;
            shoe.status = 'available';
            shoe.claimed_by = null;
            shoe.reserved_at = null;
            shoe.expires_at = null;
            this.pushToast('Released to Stock', `${shoe.sku} released from ${prevBuyer}. Status is now Available.`, 'info');
        }
    },

    markPaid(sku) {
        const shoe = this.catalog.find(s => s.sku === sku);
        if (!shoe) return;
        const ref = prompt(`Enter GCash Reference Number for ${shoe.sku} (${shoe.claimed_by}):`, 'GCASH-' + Math.floor(100000000 + Math.random() * 900000000));
        if (ref && ref.trim()) {
            shoe.status = 'sold';
            shoe.sold_price = shoe.target_price;
            shoe.payment_ref = ref.trim();

            // Append to sales ledger
            const now = new Date();
            const dateStr = now.toISOString().slice(0, 10) + ' ' + now.toTimeString().slice(0, 5);
            const profit = shoe.target_price - shoe.base_cost - shoe.repair_cost;

            this.salesLedger.unshift({
                id: 'TX-LS-' + Date.now().toString().slice(-6),
                date: dateStr,
                channel: 'Live Stream',
                sku: shoe.sku,
                brand: shoe.brand,
                model: shoe.model,
                size: shoe.size,
                condition: shoe.condition,
                grossSale: shoe.target_price,
                baseCost: shoe.base_cost,
                repairCost: shoe.repair_cost,
                netProfit: profit,
                paymentMethod: 'GCash',
                paymentRef: ref.trim(),
                buyer: shoe.claimed_by
            });

            this.pushToast('Payment Verified', `Order for ${shoe.sku} verified. Reference: ${ref}. Ready for courier packing.`, 'success');
        }
    },

    getTimeRemaining(expiresAt) {
        if (!expiresAt) return { text: '--:--', percent: 0, isUrgent: false };
        const diff = new Date(expiresAt).getTime() - Date.now();
        if (diff <= 0) return { text: 'Expired', percent: 0, isUrgent: true };

        const totalWindow = 2 * 60 * 60 * 1000;
        const percent = Math.max(0, Math.min(100, Math.round((diff / totalWindow) * 100)));
        const minutes = Math.floor(diff / 60000);
        const seconds = Math.floor((diff % 60000) / 1000);
        const hours = Math.floor(minutes / 60);
        const remMinutes = minutes % 60;

        let text = `${remMinutes}m ${seconds}s`;
        if (hours > 0) {
            text = `${hours}h ${remMinutes}m ${seconds}s`;
        }

        return {
            text,
            percent,
            isUrgent: diff < 20 * 60 * 1000
        };
    },

    get activeClaimsList() {
        return this.catalog.filter(s => {
            if (this.claimFilter === 'pending') return s.status === 'reserved';
            if (this.claimFilter === 'paid') return s.status === 'sold' && s.claimed_by;
            return (s.status === 'reserved' || (s.status === 'sold' && s.claimed_by));
        });
    },

    get activeClaimsCount() {
        return this.catalog.filter(s => s.status === 'reserved').length;
    },

    get totalClaimedValue() {
        return this.catalog
            .filter(s => s.status === 'reserved' || (s.status === 'sold' && s.claimed_by))
            .reduce((sum, s) => sum + s.target_price, 0);
    },

    // ----------------------------------------------------
    // WALK-IN POS LOGIC
    // ----------------------------------------------------
    get availablePosInventory() {
        return this.catalog.filter(shoe => {
            const matchesStatus = shoe.status === 'available';
            const matchesBrand = this.posBrandFilter === 'All' || shoe.brand === this.posBrandFilter;
            const matchesSize = this.posSizeFilter === 'All' || shoe.size === this.posSizeFilter;
            const matchesCondition = this.posConditionFilter === 'All' || shoe.condition === this.posConditionFilter;
            const matchesSearch = !this.posSearch.trim() ||
                shoe.sku.toLowerCase().includes(this.posSearch.toLowerCase()) ||
                shoe.brand.toLowerCase().includes(this.posSearch.toLowerCase()) ||
                shoe.model.toLowerCase().includes(this.posSearch.toLowerCase());

            return matchesStatus && matchesBrand && matchesSize && matchesCondition && matchesSearch;
        });
    },

    addToPosCart(shoe) {
        if (this.posCart.some(item => item.sku === shoe.sku)) {
            this.pushToast('Item Already in Cart', `${shoe.sku} is already added to current ticket.`, 'warning');
            return;
        }
        this.posCart.push({ ...shoe });
        this.pushToast('Added to Cart', `${shoe.sku} added to ticket.`, 'info');
    },

    removeFromPosCart(sku) {
        this.posCart = this.posCart.filter(item => item.sku !== sku);
    },

    clearPosCart() {
        this.posCart = [];
        this.posDiscount = 0;
        this.posDiscountNote = '';
        this.posCashTendered = '';
        this.posGcashRef = '';
    },

    get posSubtotal() {
        return this.posCart.reduce((sum, item) => sum + item.target_price, 0);
    },

    get posFinalTotal() {
        const sub = this.posSubtotal;
        const disc = Math.max(0, parseFloat(this.posDiscount) || 0);
        return Math.max(0, sub - disc);
    },

    get posChangeDue() {
        if (this.posPaymentMethod !== 'cash') return 0;
        const tendered = parseFloat(this.posCashTendered) || 0;
        const finalTot = this.posFinalTotal;
        return tendered >= finalTot ? tendered - finalTot : 0;
    },

    completePosSale() {
        if (this.posCart.length === 0) {
            this.pushToast('Empty Ticket', 'Please add at least one pair to the ticket before checkout.', 'error');
            return;
        }

        const finalTotal = this.posFinalTotal;

        if (this.posPaymentMethod === 'cash') {
            const tendered = parseFloat(this.posCashTendered) || 0;
            if (tendered < finalTotal) {
                this.pushToast('Insufficient Cash', `Total is ₱${finalTotal.toLocaleString()}, but tendered amount is ₱${tendered.toLocaleString()}.`, 'error');
                return;
            }
        } else {
            if (!this.posGcashRef || this.posGcashRef.trim().length < 6) {
                this.pushToast('Missing GCash Reference', 'Please enter a valid GCash Reference Number.', 'error');
                return;
            }
        }

        const receiptNo = 'SB-POS-' + Date.now().toString().slice(-6);
        const soldItems = [...this.posCart];
        const now = new Date();
        const dateStr = now.toISOString().slice(0, 10) + ' ' + now.toTimeString().slice(0, 5);

        soldItems.forEach(cartItem => {
            const catalogItem = this.catalog.find(s => s.sku === cartItem.sku);
            if (catalogItem) {
                catalogItem.status = 'sold';
                catalogItem.sold_price = catalogItem.target_price;
                catalogItem.payment_ref = this.posPaymentMethod === 'cash' ? `CASH-${receiptNo}` : this.posGcashRef.trim();

                const profit = catalogItem.target_price - catalogItem.base_cost - catalogItem.repair_cost;

                this.salesLedger.unshift({
                    id: receiptNo,
                    date: dateStr,
                    channel: 'Walk-In POS',
                    sku: catalogItem.sku,
                    brand: catalogItem.brand,
                    model: catalogItem.model,
                    size: catalogItem.size,
                    condition: catalogItem.condition,
                    grossSale: catalogItem.target_price,
                    baseCost: catalogItem.base_cost,
                    repairCost: catalogItem.repair_cost,
                    netProfit: profit,
                    paymentMethod: this.posPaymentMethod === 'cash' ? 'Cash' : 'GCash',
                    paymentRef: this.posPaymentMethod === 'cash' ? `DRAWER-${receiptNo}` : this.posGcashRef.trim(),
                    buyer: 'Walk-In Customer'
                });
            }
        });

        this.lastTransaction = {
            receiptNo,
            timestamp: new Date().toLocaleString(),
            items: soldItems,
            subtotal: this.posSubtotal,
            discount: parseFloat(this.posDiscount) || 0,
            discountNote: this.posDiscountNote,
            total: finalTotal,
            paymentMethod: this.posPaymentMethod,
            cashTendered: parseFloat(this.posCashTendered) || 0,
            changeDue: this.posChangeDue,
            gcashRef: this.posGcashRef
        };

        this.showReceiptModal = true;
        this.clearPosCart();
        this.pushToast('Sale Completed', `Receipt ${receiptNo} recorded. Stock updated.`, 'success');
    },

    // ----------------------------------------------------
    // BATCH INGESTION & TRIAGE LOGIC
    // ----------------------------------------------------
    get currentBatchMeta() {
        return this.batches.find(b => b.id === this.activeBatch) || this.batches[0];
    },

    get triageInventory() {
        return this.catalog.filter(shoe => {
            const matchesBatch = shoe.batch === this.activeBatch;
            const matchesStatus = this.triageStatusFilter === 'all' || shoe.status === this.triageStatusFilter;
            const matchesCondition = this.triageConditionFilter === 'all' || shoe.condition === this.triageConditionFilter;
            const matchesSearch = !this.triageSearch.trim() ||
                shoe.sku.toLowerCase().includes(this.triageSearch.toLowerCase()) ||
                shoe.brand.toLowerCase().includes(this.triageSearch.toLowerCase()) ||
                shoe.model.toLowerCase().includes(this.triageSearch.toLowerCase());

            return matchesBatch && matchesStatus && matchesCondition && matchesSearch;
        });
    },

    get batchStats() {
        const items = this.catalog.filter(s => s.batch === this.activeBatch);
        const count = items.length;
        const available = items.filter(s => s.status === 'available').length;
        const reserved = items.filter(s => s.status === 'reserved').length;
        const underRepair = items.filter(s => s.status === 'under_repair' || s.status === 'washing').length;
        const sold = items.filter(s => s.status === 'sold').length;
        const totalTargetRevenue = items.reduce((sum, s) => sum + s.target_price, 0);
        const totalAllocatedCost = items.reduce((sum, s) => sum + s.base_cost + s.repair_cost, 0);
        const projectedNetProfit = totalTargetRevenue - totalAllocatedCost;

        return {
            count,
            available,
            reserved,
            underRepair,
            sold,
            totalTargetRevenue,
            totalAllocatedCost,
            projectedNetProfit,
            marginPercent: totalTargetRevenue > 0 ? Math.round((projectedNetProfit / totalTargetRevenue) * 100) : 0
        };
    },

    cycleStatus(sku) {
        const shoe = this.catalog.find(s => s.sku === sku);
        if (!shoe) return;
        const sequence = ['washing', 'under_repair', 'available'];
        const currentIndex = sequence.indexOf(shoe.status);
        if (currentIndex !== -1) {
            shoe.status = sequence[(currentIndex + 1) % sequence.length];
            this.pushToast('Status Updated', `${shoe.sku} transitioned to ${shoe.status.replace('_', ' ').toUpperCase()}`, 'info');
        }
    },

    updateShoeCondition(sku, condition) {
        const shoe = this.catalog.find(s => s.sku === sku);
        if (shoe) {
            shoe.condition = condition;
            this.pushToast('Condition Updated', `${shoe.sku} grade set to ${condition}`, 'info');
        }
    },

    updateRepairCost(sku, cost) {
        const shoe = this.catalog.find(s => s.sku === sku);
        if (shoe) {
            shoe.repair_cost = Math.max(0, parseFloat(cost) || 0);
        }
    },

    // ----------------------------------------------------
    // FINANCIAL LEDGER & EXPENSES LOGIC
    // ----------------------------------------------------
    addExpense() {
        const amt = parseFloat(this.newExpenseAmount);
        if (!amt || amt <= 0) {
            this.pushToast('Invalid Amount', 'Please enter a valid expense amount.', 'error');
            return;
        }
        if (!this.newExpenseDescription.trim()) {
            this.pushToast('Missing Description', 'Please provide a brief expense description.', 'error');
            return;
        }

        const newId = Date.now();
        this.expenses.unshift({
            id: newId,
            date: this.newExpenseDate || new Date().toISOString().slice(0, 10),
            category: this.newExpenseCategory,
            description: this.newExpenseDescription.trim(),
            amount: amt
        });

        this.pushToast('Expense Logged', `₱${amt.toLocaleString()} recorded under ${this.newExpenseCategory}.`, 'success');
        this.newExpenseDescription = '';
        this.newExpenseAmount = '';
    },

    removeExpense(id) {
        this.expenses = this.expenses.filter(e => e.id !== id);
        this.pushToast('Expense Removed', 'Expense item deleted from ledger.', 'info');
    },

    get filteredSalesLedger() {
        if (this.ledgerChannelFilter === 'all') return this.salesLedger;
        return this.salesLedger.filter(s => s.channel === this.ledgerChannelFilter);
    },

    get totalGrossSales() {
        return this.salesLedger.reduce((sum, item) => sum + item.grossSale, 0);
    },

    get totalCogs() {
        return this.salesLedger.reduce((sum, item) => sum + item.baseCost + item.repairCost, 0);
    },

    get grossSalesProfit() {
        return this.totalGrossSales - this.totalCogs;
    },

    get totalOperatingExpenses() {
        return this.expenses.reduce((sum, item) => sum + item.amount, 0);
    },

    get netStoreProfit() {
        return this.grossSalesProfit - this.totalOperatingExpenses;
    },

    get cashCollectedTotal() {
        return this.salesLedger
            .filter(s => s.paymentMethod === 'Cash')
            .reduce((sum, s) => sum + s.grossSale, 0);
    },

    get gcashCollectedTotal() {
        return this.salesLedger
            .filter(s => s.paymentMethod === 'GCash')
            .reduce((sum, s) => sum + s.grossSale, 0);
    },

    // Excel / CSV Export Engine
    exportToExcel() {
        const now = new Date();
        const ymd = now.toISOString().slice(0, 10).replace(/-/g, '');
        const filename = `The_Shoe_Boy_Financial_Report_${ymd}.csv`;

        let csv = '\uFEFF'; // UTF-8 BOM for Microsoft Excel

        // 1. Title & Metadata
        csv += 'THE SHOE BOY - INTEGRATED FINANCIAL & SALES REPORT\r\n';
        csv += `Report Generated:,"${now.toLocaleString()}"\r\n`;
        csv += `Active Batch:,"${this.activeBatch}"\r\n\r\n`;

        // 2. Executive Financial Summary
        csv += 'EXECUTIVE FINANCIAL SUMMARY\r\n';
        csv += 'Metric,Amount (PHP),Notes\r\n';
        csv += `Total Gross Sales,${this.totalGrossSales},"Total revenue from sold footwear"\r\n`;
        csv += `Cost of Goods Sold (COGS),${this.totalCogs},"Allocated batch base cost + unit repairs"\r\n`;
        csv += `Gross Profit from Sales,${this.grossSalesProfit},"Sales minus direct unit costs"\r\n`;
        csv += `Total Store Operating Expenses,${this.totalOperatingExpenses},"Sack purchases, freight, restoration, utilities"\r\n`;
        csv += `Net Operating Profit,${this.netStoreProfit},"Gross Profit minus Operating Expenses"\r\n`;
        csv += `Cash Drawer Total,${this.cashCollectedTotal},"Physical in-store cash settlements"\r\n`;
        csv += `GCash Settled Total,${this.gcashCollectedTotal},"Verified electronic transfers"\r\n\r\n`;

        // 3. Sales Transactions Ledger
        csv += 'SALES TRANSACTIONS LEDGER\r\n';
        csv += 'Transaction ID,Date & Time,Channel,SKU,Brand,Model,Size,Condition,Gross Sale (PHP),Base Cost (PHP),Repair Cost (PHP),Net Profit (PHP),Payment Method,Reference Number,Buyer / Customer\r\n';
        this.salesLedger.forEach(item => {
            csv += `"${item.id}","${item.date}","${item.channel}","${item.sku}","${item.brand}","${item.model.replace(/"/g, '""')}","${item.size}","${item.condition}",${item.grossSale},${item.baseCost},${item.repairCost},${item.netProfit},"${item.paymentMethod}","${item.paymentRef}","${item.buyer}"\r\n`;
        });
        csv += '\r\n';

        // 4. Operating Expenses
        csv += 'STORE OPERATING EXPENSES\r\n';
        csv += 'Expense ID,Date,Category,Description,Amount (PHP)\r\n';
        this.expenses.forEach(exp => {
            csv += `"${exp.id}","${exp.date}","${exp.category}","${exp.description.replace(/"/g, '""')}",${exp.amount}\r\n`;
        });

        // Trigger native download
        const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        const url = URL.createObjectURL(blob);
        link.setAttribute('href', url);
        link.setAttribute('download', filename);
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        URL.revokeObjectURL(url);

        this.pushToast('Report Exported', `Generated ${filename} for Microsoft Excel.`, 'success');
    },

    // Overall Session Summary
    get globalStats() {
        const totalInventory = this.catalog.length;
        const totalAvailable = this.catalog.filter(s => s.status === 'available').length;
        const totalReserved = this.catalog.filter(s => s.status === 'reserved').length;
        const totalSold = this.catalog.filter(s => s.status === 'sold').length;
        const grossRevenue = this.catalog
            .filter(s => s.status === 'sold')
            .reduce((sum, s) => sum + (s.sold_price || s.target_price), 0);

        return {
            totalInventory,
            totalAvailable,
            totalReserved,
            totalSold,
            grossRevenue
        };
    }
}));

Alpine.start();
