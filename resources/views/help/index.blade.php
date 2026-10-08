@extends('layouts.app')

@section('title', 'Help & Guide')

@section('content')
@php
    $sections = [
        [
            'id' => 'getting-started',
            'title' => 'Getting started',
            'icon' => 'M13 10V3L4 14h7v7l9-11h-7z',
            'steps' => [
                'Sign in with your email and password. Owners see the <strong>Admin Workspace</strong>; staff see the <strong>Staff Workspace</strong>.',
                'Owners can open the staff console anytime from <strong>Staff Workspace</strong> in the menu (or go to <span class="font-mono">/console</span>).',
                'Use the ☀/🌙 icon or <strong>Settings</strong> to switch light or dark mode — your choice is remembered.',
            ],
        ],
        [
            'id' => 'intake',
            'title' => 'Batches & intake',
            'icon' => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4',
            'steps' => [
                'Open <strong>Batches</strong> → <strong>Record Intake</strong> and enter the supplier, batch code, sacks, total pairs, and total cost.',
                'Open a batch to see its pairs, add new pairs, and manage them directly.',
                'New pairs start in the <strong>Washing</strong> triage stage until they are ready to sell.',
            ],
        ],
        [
            'id' => 'triage',
            'title' => 'Pair triage (wash & repair)',
            'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
            'steps' => [
                'Every pair moves through <strong>Washing → Under repair → Ready</strong>.',
                'In <strong>Inventory</strong>, use the Triage filter to see what still needs work; edit a pair to change its stage.',
                'Use the staff <strong>Triage</strong> tab as a worklist: click <strong>Mark ready</strong>, <strong>Send to repair</strong>, or <strong>Repair done</strong>.',
                'A pair can only be reserved or sold once it is <strong>Ready</strong>.',
            ],
        ],
        [
            'id' => 'selling',
            'title' => 'Selling & claims',
            'icon' => 'M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z',
            'steps' => [
                'Staff Workspace → <strong>Live Claim Ticket</strong>: search the shoe code, add pairs, set the buyer, and lock the reservation.',
                '<strong>Walk-in POS</strong>: build the ticket and check out on the spot.',
                'When the buyer pays, click <strong>Verify Payment</strong> and record cash or GCash (with reference number).',
                'The amount is locked to the order total — you cannot over- or under-charge by mistake.',
            ],
        ],
        [
            'id' => 'delivery',
            'title' => 'Deliveries',
            'icon' => 'M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1',
            'steps' => [
                'Open <strong>Deliveries</strong> and set the method: <strong>Pickup</strong> or <strong>J&amp;T delivery</strong>.',
                'J&amp;T deliveries require a tracking number.',
                'Move the status <strong>Pending → Shipped → Completed</strong>. Completing locks the delivery and marks the order fulfilled.',
            ],
        ],
        [
            'id' => 'money',
            'title' => 'Expenses & reports',
            'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z',
            'steps' => [
                'Log costs under <strong>Expenses</strong>; link one to a batch for accurate batch profit.',
                'Owners open <strong>Reports</strong> for sales, profit by batch, and profit by price tier.',
                'Use <strong>Export</strong> to download a spreadsheet.',
            ],
        ],
        [
            'id' => 'admin',
            'title' => 'Owner tools',
            'icon' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z',
            'steps' => [
                '<strong>Staff</strong>: create accounts and activate/deactivate access.',
                '<strong>Suppliers</strong>: keep a clean list referenced by every batch.',
                '<strong>Data &amp; Backups</strong> (from Reports): download, create, import, and restore database backups.',
                '<strong>Audit Log</strong>: review sign-ins and every important action.',
            ],
        ],
        [
            'id' => 'tips',
            'title' => 'Everyday tips',
            'icon' => 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
            'steps' => [
                'Use the search box and column sorters on list pages to find records fast.',
                'Deleting a batch, expense, or pair shows an <strong>Undo</strong> button — nothing is lost by accident.',
                'Open <strong>Settings</strong> to change theme, table density, and the navigation layout.',
            ],
        ],
    ];
@endphp

<div class="space-y-6">

    <div class="app-card p-5 lg:p-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="text-[11px] font-semibold uppercase tracking-wider text-neutral-500">Guide</div>
            <h1 class="text-xl md:text-2xl font-bold tracking-tight text-[#1D1D1F] dark:text-white mt-0.5">How to use The Shoe Boy</h1>
            <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">Take the quick interactive tour, or read short instructions for every part of the system below.</p>
        </div>
        <button type="button" onclick="window.appTour && window.appTour.start(document.body.dataset.page || '')"
                class="app-btn app-btn-primary shrink-0 self-start sm:self-auto">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>Take the tour</span>
        </button>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        @foreach($sections as $section)
        <section id="{{ $section['id'] }}" class="app-card p-5 lg:p-6">
            <div class="flex items-center gap-3">
                <span class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0 bg-neutral-100 text-neutral-600 dark:bg-neutral-800 dark:text-neutral-300">
                    <svg class="w-4.5 h-4.5" style="width:1.125rem;height:1.125rem" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $section['icon'] }}"/></svg>
                </span>
                <h2 class="font-bold text-base text-[#1D1D1F] dark:text-white">{{ $section['title'] }}</h2>
            </div>
            <ol class="mt-4 space-y-2.5 text-sm text-neutral-600 dark:text-neutral-300 list-decimal list-inside marker:text-neutral-400">
                @foreach($section['steps'] as $step)
                <li class="leading-relaxed">{!! $step !!}</li>
                @endforeach
            </ol>
        </section>
        @endforeach
    </div>

</div>
@endsection
