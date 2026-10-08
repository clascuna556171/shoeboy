<?php

/**
 * Single source of truth for Help content and interactive tours.
 *
 * Every entry drives TWO things at once so they can never drift:
 *   - the Help page prose ("help" sections), and
 *   - the spotlighted walkthrough ("steps").
 *
 * Step shape:
 *   title : heading on the card
 *   body  : 1–2 short sentences
 *   sel   : optional data-tour selector to spotlight; omit for a
 *           centered narrative card (Welcome / About this page / outro)
 *   core  : include in the short "Essentials" pass
 *   roles : restrict to certain roles (owner/staff)
 *   learn : help section id for a "Learn more" link
 */

return [

    // =====================================================================
    // GENERAL — first-run orientation and Page guides → "System overview"
    // =====================================================================
    'general' => [
        'label' => 'System overview',
        'steps' => [
            ['title' => 'Welcome to The Shoe Boy', 'body' => 'Your order & inventory system for the Davao shop. Everything follows one daily loop: bring in bales, prepare the pairs, sell them, deliver, then track the money.', 'core' => true],
            ['sel' => '[data-tour="nav"]', 'title' => 'Your main menu', 'body' => 'Every module lives here, arranged in the order you use them day to day. Let’s walk through each one.', 'core' => true, 'learn' => 'getting-started'],
            ['sel' => '[data-tour="nav-workspace"]', 'title' => 'Workspace — your home', 'body' => 'Your landing page. Staff land in the selling console; owners get an admin snapshot of the whole shop.', 'core' => true],
            ['sel' => '[data-tour="nav-batches"]', 'title' => 'Batches — intake', 'body' => 'Log each bale shipment here: supplier, sacks, total pairs, and cost. Every pair belongs to a batch.', 'learn' => 'batches-intake'],
            ['sel' => '[data-tour="nav-inventory"]', 'title' => 'Inventory — the catalog', 'body' => 'The master list of individual pairs, each with its own code. Pairs also move through washing and repair here.', 'learn' => 'inventory-units'],
            ['sel' => '[data-tour="nav-console"]', 'title' => 'Console — where you sell', 'body' => 'The staff selling desk: live-stream claims, walk-in POS, and the triage worklist.', 'roles' => ['owner'], 'learn' => 'console-selling'],
            ['sel' => '[data-tour="nav-orders"]', 'title' => 'Orders — reservations & sales', 'body' => 'Every sale and reservation is recorded here, with status and payment details.', 'learn' => 'orders'],
            ['sel' => '[data-tour="nav-deliveries"]', 'title' => 'Deliveries — fulfilment', 'body' => 'Track each parcel from pending to shipped to completed (pickup or J&T).', 'learn' => 'deliveries'],
            ['sel' => '[data-tour="nav-expenses"]', 'title' => 'Expenses', 'body' => 'Record store and batch-linked costs so profit stays accurate.', 'learn' => 'expenses'],
            ['sel' => '[data-tour="nav-reports"]', 'title' => 'Reports', 'body' => 'Owners view profit by batch, session, and price tier, plus full ledgers — and export them.', 'roles' => ['owner'], 'learn' => 'reports'],
            ['sel' => '[data-tour="nav-staff"]', 'title' => 'Staff', 'body' => 'Create staff logins and switch access on or off.', 'roles' => ['owner'], 'learn' => 'staff'],
            ['sel' => '[data-tour="nav-suppliers"]', 'title' => 'Suppliers', 'body' => 'Keep your reusable supplier list; batches reference these.', 'roles' => ['owner'], 'learn' => 'suppliers'],
            ['sel' => '[data-tour="profile"]', 'title' => 'Your account', 'body' => 'Open this menu for page-by-page guides, Help, Settings, and Sign out.', 'core' => true, 'learn' => 'getting-started'],
            ['title' => 'That’s the lay of the land', 'body' => 'Pick a page to start, or open Page guides in your profile menu for a walkthrough of any specific screen.', 'core' => true],
        ],
        'help' => [
            [
                'id' => 'getting-started',
                'title' => 'Getting started',
                'icon' => 'M13 10V3L4 14h7v7l9-11h-7z',
                'steps' => [
                    'Sign in with your email and password. Owners see the <strong>Admin Workspace</strong>; staff see the <strong>Staff Workspace</strong>.',
                    'Owners can open the staff selling console anytime from <strong>Console</strong> (or <span class="font-mono">/console</span>).',
                    'Open the profile menu (top-right, or the bottom of the left rail) for <strong>Page guides</strong>, Help, Settings, and Sign out.',
                    'Switch theme and table density in <strong>Settings</strong> — your choice is remembered.',
                ],
            ],
            [
                'id' => 'tips',
                'title' => 'Everyday tips',
                'icon' => 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
                'steps' => [
                    'Use the search box and column sorters on list pages to find records fast.',
                    'Deleting a batch, expense, or pair shows an <strong>Undo</strong> button — nothing is lost by accident.',
                    'Open <strong>Page guides</strong> in the profile menu to take a walkthrough of any screen.',
                ],
            ],
        ],
    ],

    // =====================================================================
    // PAGES — keyed by the body data-page / Page guides key
    // =====================================================================
    'pages' => [

        'dashboard' => [
            'label' => 'Workspace',
            'route' => 'dashboard',
            'icon' => 'M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z',
            'roles' => ['owner'],
            'help' => [
                [
                    'id' => 'dashboard-snapshot',
                    'title' => 'Owner workspace',
                    'icon' => 'M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z',
                    'steps' => [
                        '<strong>Financial overview</strong>: cash, GCash, revenue, and profit for the session.',
                        '<strong>Operational snapshot</strong>: cash in drawer, GCash settled, active reservations, and floor stock.',
                        '<strong>Batch profitability</strong>: outlay, sales, and order profit per bale.',
                        '<strong>Needs attention</strong>: pending deliveries, expiring reservations, and the wash/repair pipeline.',
                        '<strong>Recent settled sales</strong> and the <strong>audit log</strong> keep you current.',
                    ],
                ],
            ],
            'steps' => [
                ['title' => 'Your command centre', 'body' => 'This is the owner’s home. It summarises money, stock, and anything that needs a decision today.', 'core' => true, 'learn' => 'dashboard-snapshot'],
                ['sel' => '[data-tour="financial"]', 'title' => 'Financial overview', 'body' => 'Cash, GCash, revenue, and profit for the current session sit at the top.', 'core' => true],
                ['sel' => '[data-tour="kpis"]', 'title' => 'Operational snapshot', 'body' => 'Cash in drawer, GCash settled, active reservations, and floor stock at a glance.'],
                ['sel' => '[data-tour="profit"]', 'title' => 'Batch profitability', 'body' => 'Unit economics per bale — outlay, sales, and order profit — so you can see which batch earned its keep.'],
                ['sel' => '[data-tour="sales"]', 'title' => 'Recent settled sales', 'body' => 'The latest confirmed sales roll in here.'],
                ['sel' => '[data-tour="attention"]', 'title' => 'Needs attention', 'body' => 'Pending deliveries, reservations about to expire, and the wash/repair pipeline — click a card to act.', 'core' => true],
                ['sel' => '[data-tour="audit"]', 'title' => 'Audit log', 'body' => 'A running record of sign-ins and important actions for accountability.'],
                ['title' => 'Stay ahead of the day', 'body' => 'Check this page first each morning, then jump into the console or reports.', 'core' => true, 'learn' => 'dashboard-snapshot'],
            ],
        ],

        'batches' => [
            'label' => 'Batches',
            'route' => 'batches.index',
            'icon' => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4',
            'help' => [
                [
                    'id' => 'batches-intake',
                    'title' => 'Batches & intake',
                    'icon' => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4',
                    'steps' => [
                        'Open <strong>Batches</strong> → <strong>Record Intake</strong> and enter the supplier, batch code, sacks, total pairs, and total cost.',
                        'Open a batch to see its pairs, add new pairs, and manage them directly.',
                        'Every pair belongs to a batch and carries that batch’s cost basis.',
                    ],
                ],
            ],
            'steps' => [
                ['title' => 'What a batch is', 'body' => 'A batch is one bale intake — a shipment from a supplier with its own code, pair count, and total cost.', 'core' => true, 'learn' => 'batches-intake'],
                ['sel' => '[data-tour="search"]', 'title' => 'Find a batch', 'body' => 'Search by batch code or supplier.', 'core' => true],
                ['sel' => '[data-tour="view"]', 'title' => 'Cards or table', 'body' => 'Switch between the card view and a compact table.'],
                ['sel' => '[data-tour="list"]', 'title' => 'Your batches', 'body' => 'Each entry shows the supplier, pairs, and cost. Open one to see and manage its pairs.'],
                ['sel' => '[data-tour="primary"]', 'title' => 'Record intake', 'body' => 'Log a new shipment: supplier, batch code, sacks, total pairs, and total cost.', 'core' => true],
                ['title' => 'Then serialise the pairs', 'body' => 'After intake, add each pair in Inventory (or inside the batch) so it can be tracked and sold.', 'core' => true, 'learn' => 'batches-intake'],
            ],
        ],

        'inventory' => [
            'label' => 'Inventory',
            'route' => 'items.index',
            'icon' => 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10',
            'help' => [
                [
                    'id' => 'inventory-units',
                    'title' => 'Inventory & pair codes',
                    'icon' => 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10',
                    'steps' => [
                        '<strong>Inventory</strong> is the master catalog of individual pairs.',
                        'Each pair has a unique code (e.g. <span class="font-mono">B04-001</span>) and a price tier.',
                        'Search by code, brand, or model; filter by batch, sale status, or triage stage.',
                    ],
                ],
                [
                    'id' => 'inventory-triage',
                    'title' => 'Pair triage (wash & repair)',
                    'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
                    'steps' => [
                        'Every pair moves through <strong>Washing → Under repair → Ready</strong>.',
                        'Use the <strong>Triage</strong> filter in Inventory, or the staff <strong>Triage</strong> tab, as a worklist.',
                        'A pair can only be reserved or sold once it is <strong>Ready</strong>.',
                    ],
                ],
            ],
            'steps' => [
                ['title' => 'The master catalog', 'body' => 'Inventory lists every individual pair, each with a unique code like B04-001. Pairs are created here or from inside a batch.', 'core' => true, 'learn' => 'inventory-units'],
                ['sel' => '[data-tour="search"]', 'title' => 'Find a pair', 'body' => 'Search by code, brand, or model.', 'core' => true],
                ['sel' => '[data-tour="filters"]', 'title' => 'Filters', 'body' => 'Narrow by batch, sale status, or triage stage.'],
                ['sel' => '[data-tour="triage"]', 'title' => 'Triage stage', 'body' => 'Washing → Under repair → Ready. Only Ready pairs can be reserved or sold.'],
                ['sel' => '[data-tour="list"]', 'title' => 'The pair table', 'body' => 'Each row shows the pair’s code, price tier, status, and actions.'],
                ['sel' => '[data-tour="primary"]', 'title' => 'Add a pair', 'body' => 'Serialise a new pair into a batch. It starts in Washing until you mark it Ready.', 'core' => true],
                ['title' => 'Ready to sell', 'body' => 'Once ready, pairs appear in the console for live claims and walk-in sales.', 'core' => true, 'learn' => 'inventory-triage'],
            ],
        ],

        'console' => [
            'label' => 'Console',
            'route' => 'staff.workspace',
            'icon' => 'M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z',
            'help' => [
                [
                    'id' => 'console-selling',
                    'title' => 'The staff console',
                    'icon' => 'M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z',
                    'steps' => [
                        '<strong>Live Claims</strong>: search a code, build the ticket, set the buyer, and lock the reservation.',
                        '<strong>Walk-in POS</strong>: build a ticket and check out on the spot (cash or GCash).',
                        '<strong>Triage Table</strong>: move pairs through washing and repair.',
                        'When the buyer pays, click <strong>Verify Payment</strong> — the amount is locked to the order total.',
                    ],
                ],
            ],
            'steps' => [
                ['title' => 'The selling desk', 'body' => 'The console is where sales happen. It has three tabs: Live Claims, Walk-in POS, and the Triage Table.', 'core' => true, 'learn' => 'console-selling'],
                ['sel' => '[data-tour="tabs"]', 'title' => 'The three tabs', 'body' => 'Switch between Live Claims, Walk-in POS, and Triage here.', 'core' => true],
                ['title' => 'Live Claims', 'body' => 'Search a code, build the ticket, set the buyer, and lock the reservation. Active claims show a live countdown.'],
                ['title' => 'Walk-in POS', 'body' => 'Build a walk-in ticket and check out on the spot — cash (with change) or GCash.'],
                ['title' => 'Triage Table', 'body' => 'The wash/repair worklist: move pairs to Washing, Under repair, or Ready.'],
                ['title' => 'Every sale lands in Orders', 'body' => 'Claims and POS sales are recorded as orders you can track and deliver.', 'core' => true, 'learn' => 'console-selling'],
            ],
        ],

        'console:claims' => [
            'label' => 'Live Claims',
            'help' => [],
            'steps' => [
                ['title' => 'Live claims', 'body' => 'Use this tab during a live stream: find a pair, build the ticket, and hold it for the buyer.', 'core' => true, 'learn' => 'console-selling'],
                ['sel' => '[data-tour="lookup"]', 'title' => 'Shoe-code lookup', 'body' => 'Search a code, then add the pair to the claim ticket.', 'core' => true],
                ['sel' => '[data-tour="ticket"]', 'title' => 'Claim ticket', 'body' => 'Set the buyer, lock the reservation, and verify payment when it arrives.'],
                ['sel' => '[data-tour="claims"]', 'title' => 'Active claims', 'body' => 'Live reservations with countdowns — verify payment or release back to stock.', 'core' => true],
                ['title' => 'Reservation held', 'body' => 'A locked claim reserves the pair until it is paid or released.', 'core' => true, 'learn' => 'console-selling'],
            ],
        ],

        'console:pos' => [
            'label' => 'Walk-in POS',
            'help' => [],
            'steps' => [
                ['title' => 'Walk-in POS', 'body' => 'For customers buying in person — build a ticket and check out immediately.', 'core' => true, 'learn' => 'console-selling'],
                ['sel' => '[data-tour="pos-catalog"]', 'title' => 'Catalog', 'body' => 'Filter by brand and tap Add to drop pairs into the walk-in ticket.'],
                ['sel' => '[data-tour="pos-ticket"]', 'title' => 'Walk-in ticket', 'body' => 'Review the pairs and apply a discount if needed.', 'core' => true],
                ['sel' => '[data-tour="pos-payment"]', 'title' => 'Payment', 'body' => 'Enter cash received (change is computed) or the GCash reference, then complete the sale.', 'core' => true],
                ['title' => 'Done — and recorded', 'body' => 'The sale is completed and saved as an order automatically.', 'core' => true, 'learn' => 'console-selling'],
            ],
        ],

        'console:triage' => [
            'label' => 'Triage Table',
            'help' => [],
            'steps' => [
                ['title' => 'Triage worklist', 'body' => 'The quick way to keep the wash/repair pipeline moving.', 'core' => true, 'learn' => 'inventory-triage'],
                ['sel' => '[data-tour="triage-table"]', 'title' => 'Move pairs forward', 'body' => 'Move pairs through Washing → Under repair → Ready. Only Ready pairs can be sold.', 'core' => true],
                ['title' => 'Keep it current', 'body' => 'Prices and buyers only apply once a pair is Ready.', 'core' => true, 'learn' => 'inventory-triage'],
            ],
        ],

        'orders' => [
            'label' => 'Orders',
            'route' => 'orders.index',
            'icon' => 'M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z',
            'help' => [
                [
                    'id' => 'orders',
                    'title' => 'Orders & reservations',
                    'icon' => 'M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z',
                    'steps' => [
                        'Every claim and POS sale is recorded as an <strong>order</strong>.',
                        'An order shows the buyer, pairs, amount, status, and payment method.',
                        'Release a reservation back to stock, or cancel; a paid order can print a receipt.',
                    ],
                ],
            ],
            'steps' => [
                ['title' => 'Every sale lives here', 'body' => 'Orders records each reservation and sale from the console, with the buyer, pairs, amount, and payment.', 'core' => true, 'learn' => 'orders'],
                ['sel' => '[data-tour="search"]', 'title' => 'Search orders', 'body' => 'Find an order by number, code, or customer.', 'core' => true],
                ['sel' => '[data-tour="list"]', 'title' => 'The orders table', 'body' => 'Status, total, and payment method at a glance.'],
                ['sel' => '[data-tour="row-actions"]', 'title' => 'Row actions', 'body' => 'Open the receipt, release a reservation, or cancel from the row.'],
                ['title' => 'From reservation to receipt', 'body' => 'A reservation holds a pair; once paid it is verified and a receipt can be printed.', 'core' => true, 'learn' => 'orders'],
            ],
        ],

        'deliveries' => [
            'label' => 'Deliveries',
            'route' => 'deliveries.index',
            'icon' => 'M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1',
            'help' => [
                [
                    'id' => 'deliveries',
                    'title' => 'Deliveries',
                    'icon' => 'M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1',
                    'steps' => [
                        'Set the method: <strong>Pickup</strong> or <strong>J&amp;T</strong> (J&amp;T needs a tracking number).',
                        'Move status <strong>Pending → Shipped → Completed</strong>.',
                        'Completing a delivery marks the order fulfilled.',
                    ],
                ],
            ],
            'steps' => [
                ['title' => 'Deliveries = fulfilment', 'body' => 'Each paid order needs to reach the buyer — by pickup or courier.', 'core' => true, 'learn' => 'deliveries'],
                ['sel' => '[data-tour="search"]', 'title' => 'Search deliveries', 'body' => 'Find a parcel by order, buyer, or tracking number. Pending work is listed first.', 'core' => true],
                ['sel' => '[data-tour="list"]', 'title' => 'The delivery queue', 'body' => 'Cards/rows show the buyer, method, and current status.'],
                ['sel' => '[data-tour="method"]', 'title' => 'Method', 'body' => 'Pickup or J&T — J&T needs a tracking number.'],
                ['sel' => '[data-tour="status"]', 'title' => 'Status', 'body' => 'Move Pending → Shipped → Completed. Completing marks the order fulfilled.'],
                ['title' => 'Close the loop', 'body' => 'Completing a delivery is the final step of a sale.', 'core' => true, 'learn' => 'deliveries'],
            ],
        ],

        'expenses' => [
            'label' => 'Expenses',
            'route' => 'expenses.index',
            'icon' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
            'help' => [
                [
                    'id' => 'expenses',
                    'title' => 'Expenses',
                    'icon' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
                    'steps' => [
                        'Log store costs and batch-linked costs.',
                        'Link an expense to a batch to sharpen that batch’s profit.',
                        'Deleting an expense shows an <strong>Undo</strong> button.',
                    ],
                ],
            ],
            'steps' => [
                ['title' => 'Track every cost', 'body' => 'Expenses captures store costs and batch-linked costs so profit stays honest.', 'core' => true, 'learn' => 'expenses'],
                ['sel' => '[data-tour="primary"]', 'title' => 'Record an expense', 'body' => 'Add a cost — link it to a batch to sharpen that batch’s profit.', 'core' => true],
                ['sel' => '[data-tour="list"]', 'title' => 'The expense log', 'body' => 'Every recorded cost, newest first.'],
                ['sel' => '[data-tour="batch-link"]', 'title' => 'Batch link', 'body' => 'Linked expenses are subtracted from that batch’s profit.'],
                ['title' => 'Undo is safe', 'body' => 'Deleting an expense shows an Undo button for a moment — nothing is lost by accident.', 'core' => true, 'learn' => 'expenses'],
            ],
        ],

        'reports' => [
            'label' => 'Reports',
            'route' => 'reports.index',
            'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z',
            'roles' => ['owner'],
            'help' => [
                [
                    'id' => 'reports',
                    'title' => 'Reports',
                    'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z',
                    'steps' => [
                        'Profit by <strong>batch</strong>, by <strong>session/date</strong>, and by <strong>price tier</strong>.',
                        'Sales ledger and expenses ledger for full detail.',
                        'Export any report as a spreadsheet.',
                    ],
                ],
            ],
            'steps' => [
                ['title' => 'Where the numbers come together', 'body' => 'Reports is the owner’s financial view: profit by batch, session, and price tier, plus full ledgers.', 'core' => true, 'learn' => 'reports'],
                ['sel' => '[data-tour="tabs"]', 'title' => 'Report views', 'body' => 'Switch between batch, session, tier, sales, and expense ledgers.', 'core' => true],
                ['sel' => '[data-tour="list"]', 'title' => 'The report table', 'body' => 'The selected view renders here, with totals at the bottom.'],
                ['sel' => '[data-tour="export"]', 'title' => 'Export', 'body' => 'Download the current report as a spreadsheet.', 'core' => true],
                ['title' => 'Owners only', 'body' => 'Reports is visible to owner accounts. Staff see the selling tools instead.', 'core' => true, 'learn' => 'reports'],
            ],
        ],

        'staff' => [
            'label' => 'Staff',
            'route' => 'staff.index',
            'icon' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z',
            'roles' => ['owner'],
            'help' => [
                [
                    'id' => 'staff',
                    'title' => 'Staff accounts',
                    'icon' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z',
                    'steps' => [
                        'Create staff logins with a name, email, and password.',
                        'Activate or deactivate access at any time.',
                        'Only owners can manage staff.',
                    ],
                ],
            ],
            'steps' => [
                ['title' => 'Manage access', 'body' => 'Create staff logins and switch their access on or off.', 'core' => true, 'learn' => 'staff'],
                ['sel' => '[data-tour="primary"]', 'title' => 'Add a staff account', 'body' => 'Enter a name, email, and password.', 'core' => true],
                ['sel' => '[data-tour="list"]', 'title' => 'Staff accounts', 'body' => 'Active and inactive users, with their role and status.'],
                ['title' => 'Least privilege', 'body' => 'Only owners can manage staff; staff never see these admin tools.', 'core' => true, 'learn' => 'staff'],
            ],
        ],

        'suppliers' => [
            'label' => 'Suppliers',
            'route' => 'suppliers.index',
            'icon' => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4',
            'roles' => ['owner'],
            'help' => [
                [
                    'id' => 'suppliers',
                    'title' => 'Suppliers',
                    'icon' => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4',
                    'steps' => [
                        'Keep one clean list of suppliers.',
                        'Batches reference suppliers, so names stay consistent.',
                        'Add or edit suppliers before recording intake.',
                    ],
                ],
            ],
            'steps' => [
                ['title' => 'Reusable supplier list', 'body' => 'Keep suppliers in one place — every batch references one, so names stay consistent.', 'core' => true, 'learn' => 'suppliers'],
                ['sel' => '[data-tour="primary"]', 'title' => 'Add a supplier', 'body' => 'Create a supplier to pick when recording intake.', 'core' => true],
                ['sel' => '[data-tour="list"]', 'title' => 'Suppliers', 'body' => 'Each entry can be edited or removed.'],
                ['title' => 'Then use it in intake', 'body' => 'New suppliers appear in the batch intake form right away.', 'core' => true, 'learn' => 'suppliers'],
            ],
        ],

        'backups' => [
            'label' => 'Data & Backups',
            'route' => 'backups.index',
            'icon' => 'M4 7v10a2 2 0 002 2h12a2 2 0 002-2V9a2 2 0 00-2-2h-5L9 5H6a2 2 0 00-2 2z',
            'roles' => ['owner'],
            'help' => [
                [
                    'id' => 'backups',
                    'title' => 'Data & backups',
                    'icon' => 'M4 7v10a2 2 0 002 2h12a2 2 0 002-2V9a2 2 0 00-2-2h-5L9 5H6a2 2 0 00-2 2z',
                    'steps' => [
                        'Download a copy of the database, or create a server-side backup.',
                        'Import a <span class="font-mono">.sqlite</span> backup to restore.',
                        'Restoring replaces the current database; a deleted backup can be undone right after.',
                    ],
                ],
            ],
            'steps' => [
                ['title' => 'Protect your data', 'body' => 'Backups lets owners save and restore the whole database.', 'core' => true, 'learn' => 'backups'],
                ['sel' => '[data-tour="primary"]', 'title' => 'Backup & restore', 'body' => 'Download a copy, create one on the server, or import a .sqlite backup.', 'core' => true],
                ['sel' => '[data-tour="list"]', 'title' => 'Stored backups', 'body' => 'Every snapshot is kept here — restore or delete as needed.', 'core' => true],
                ['title' => 'Restore carefully', 'body' => 'Restoring replaces the current database, and a deleted backup can be undone right after.', 'core' => true, 'learn' => 'backups'],
            ],
        ],
    ],
];
