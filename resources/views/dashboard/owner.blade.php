@extends('layouts.app')

@section('title', 'Owner Executive Center')

@section('content')

@php
    $net = $metrics['net_operating_balance'];
    $netPositive = $net >= 0;
@endphp

<div class="space-y-8">

    {{-- ============================================================
         PAGE HEADER
    ============================================================= --}}
    <header class="app-card p-5 lg:p-6 flex flex-col md:flex-row items-start md:items-center justify-between gap-4">

        <div class="min-w-0">
            <div class="text-[11px] font-semibold uppercase tracking-wider text-neutral-500">
                Owner Command Center
            </div>

            <h1 class="text-xl md:text-2xl font-bold tracking-tight text-[#1D1D1F] dark:text-white mt-0.5">
                Executive Overview
            </h1>
        </div>


        {{-- Header Actions --}}
        <div class="flex flex-wrap items-center gap-2 shrink-0">

            <a href="{{ route('reports.index') }}"
               title="Open financial and profitability reports"
               class="inline-flex items-center justify-center gap-2
                      min-h-10 px-4
                      rounded-xl
                      border border-neutral-200 dark:border-neutral-700
                      bg-white dark:bg-[#1C1C1E]
                      text-sm font-semibold
                      text-neutral-700 dark:text-neutral-200
                      hover:bg-neutral-50 dark:hover:bg-neutral-800
                      transition-colors
                      focus:outline-none
                      focus-visible:ring-2
                      focus-visible:ring-neutral-400/40
                      focus-visible:ring-offset-2
                      dark:focus-visible:ring-offset-[#1C1C1E]">

                <svg class="w-4 h-4"
                     fill="none"
                     stroke="currentColor"
                     viewBox="0 0 24 24">
                    <path stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="2"
                          d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2"/>
                </svg>

                Full Reports
            </a>


            <a href="{{ route('reports.export') }}"
               title="Download current financial report"
               class="inline-flex items-center justify-center gap-2
                      min-h-10 px-4
                      rounded-xl
                      border border-neutral-200 dark:border-neutral-700
                      bg-white dark:bg-[#1C1C1E]
                      text-sm font-semibold
                      text-neutral-700 dark:text-neutral-200
                      hover:bg-neutral-50 dark:hover:bg-neutral-800
                      transition-colors
                      focus:outline-none
                      focus-visible:ring-2
                      focus-visible:ring-neutral-400/40
                      focus-visible:ring-offset-2
                      dark:focus-visible:ring-offset-[#1C1C1E]">

                <svg class="w-4 h-4 text-neutral-500 dark:text-neutral-400"
                     fill="none"
                     stroke="currentColor"
                     viewBox="0 0 24 24">
                    <path stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="2"
                          d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>

                Export CSV
            </a>


            <a href="{{ route('staff.index') }}"
               title="Manage staff accounts and access"
               class="inline-flex items-center justify-center gap-2
                      min-h-10 px-4
                      rounded-xl
                      border border-neutral-200 dark:border-neutral-700
                      bg-white dark:bg-[#1C1C1E]
                      text-sm font-semibold
                      text-neutral-700 dark:text-neutral-200
                      hover:bg-neutral-50 dark:hover:bg-neutral-800
                      transition-colors
                      focus:outline-none
                      focus-visible:ring-2
                      focus-visible:ring-neutral-400/40
                      focus-visible:ring-offset-2
                      dark:focus-visible:ring-offset-[#1C1C1E]">

                <svg class="w-4 h-4 text-neutral-500 dark:text-neutral-400"
                     fill="none"
                     stroke="currentColor"
                     viewBox="0 0 24 24">
                    <path stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="2"
                          d="M17 20h5v-1a6 6 0 00-9-5.197M9 20H3v-1a6 6 0 0112 0v1m-3-9a4 4 0 100-8 4 4 0 000 8zm6 0a4 4 0 100-8 4 4 0 000 8z"/>
                </svg>

                Manage Staff
            </a>

        </div>

    </header>


    {{-- ============================================================
         FINANCIAL OVERVIEW
    ============================================================= --}}
    <section aria-labelledby="financial-overview">

        <div class="mb-3">
            <h2 id="financial-overview"
                class="text-base font-semibold text-neutral-900 dark:text-white">
                Financial position
            </h2>

        </div>


        <div class="grid grid-cols-1 xl:grid-cols-12 gap-5">


            {{-- PRIMARY FINANCIAL CARD --}}
            <div class="xl:col-span-7
                        rounded-2xl
                        border border-neutral-200 dark:border-neutral-800
                        bg-white dark:bg-[#1C1C1E]
                        shadow-sm
                        overflow-hidden">

                <div class="p-6 sm:p-7">

                    <div class="flex flex-col sm:flex-row
                                sm:items-start sm:justify-between gap-4">

                        <div>

                            <p class="text-sm font-medium
                                      text-neutral-500 dark:text-neutral-400">
                                Net Store Balance
                            </p>

                            <div class="mt-3
                                        text-3xl sm:text-4xl
                                        font-bold tracking-tight
                                        font-mono
                                        {{ $netPositive
                                            ? 'text-emerald-600 dark:text-emerald-400'
                                            : 'text-amber-600 dark:text-amber-400' }}">

                                {{ $net < 0 ? '−' : '' }}₱{{ number_format(abs($net), 2) }}

                            </div>

                        </div>


                        <span class="inline-flex self-start
                                     items-center gap-1.5
                                     rounded-full
                                     px-3 py-1.5
                                     text-xs font-semibold
                                     {{ $netPositive
                                        ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300'
                                        : 'bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-300' }}">

                            <span class="w-1.5 h-1.5 rounded-full
                                         {{ $netPositive ? 'bg-emerald-500' : 'bg-amber-500' }}">
                            </span>

                            {{ $netPositive ? 'Positive balance' : 'Recovering costs' }}

                        </span>

                    </div>


                    <p class="mt-4 max-w-2xl
                              text-sm leading-6
                              text-neutral-600 dark:text-neutral-400">

                        What the shop keeps after paying for shoes and
                        shop costs.

                        @if(! $netPositive)
                            Negative just means cash is tied up in unsold
                            stock — not that sales lost money.
                        @endif

                    </p>

                </div>

            </div>


            {{-- MONEY FLOW --}}
            <div class="xl:col-span-5
                        rounded-2xl
                        border border-neutral-200 dark:border-neutral-800
                        bg-white dark:bg-[#1C1C1E]
                        shadow-sm">

                <div class="p-6">

                    <div>
                        <h3 class="text-base font-semibold
                                   text-neutral-900 dark:text-white">
                            How the money moved
                        </h3>

                        <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">
                            From collected sales to the current balance.
                        </p>
                    </div>


                    <div class="mt-6 space-y-4">

                        <div class="flex items-center justify-between gap-4">
                            <span class="text-sm text-neutral-600 dark:text-neutral-400">
                                Sales collected
                            </span>

                            <span class="font-mono text-sm font-semibold
                                         text-neutral-900 dark:text-white">
                                ₱{{ number_format($metrics['total_revenue'], 2) }}
                            </span>
                        </div>


                        <div class="flex items-center justify-between gap-4">
                            <span class="text-sm text-neutral-600 dark:text-neutral-400">
                                Cost of shoes sold
                            </span>

                            <span class="font-mono text-sm
                                         text-neutral-500 dark:text-neutral-400">
                                −₱{{ number_format($metrics['total_cogs'], 2) }}
                            </span>
                        </div>


                        <div class="flex items-center justify-between gap-4
                                    pt-4
                                    border-t border-neutral-100 dark:border-neutral-800">

                            <span class="text-sm font-semibold
                                         text-neutral-800 dark:text-neutral-200">
                                Profit on sales
                            </span>

                            <span class="font-mono text-sm font-bold
                                         text-emerald-600 dark:text-emerald-400">
                                ₱{{ number_format($metrics['gross_profit'], 2) }}
                            </span>

                        </div>


                        <div class="flex items-center justify-between gap-4">
                            <span class="text-sm text-neutral-600 dark:text-neutral-400">
                                Shop expenses
                            </span>

                            <span class="font-mono text-sm
                                         text-neutral-500 dark:text-neutral-400">
                                −₱{{ number_format($metrics['total_expenses'], 2) }}
                            </span>
                        </div>


                        <div class="flex items-center justify-between gap-4
                                    pt-4
                                    border-t border-neutral-200 dark:border-neutral-700">

                            <span class="text-sm font-bold
                                         text-neutral-900 dark:text-white">
                                Net balance
                            </span>

                            <span class="font-mono text-base font-bold
                                         {{ $netPositive
                                            ? 'text-emerald-600 dark:text-emerald-400'
                                            : 'text-amber-600 dark:text-amber-400' }}">

                                {{ $net < 0 ? '−' : '' }}₱{{ number_format(abs($net), 2) }}

                            </span>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </section>


    {{-- ============================================================
         OPERATIONAL SNAPSHOT
    ============================================================= --}}
    <section aria-labelledby="operational-snapshot">

        <div class="mb-3">

            <h2 id="operational-snapshot"
                class="text-base font-semibold text-neutral-900 dark:text-white">
                Operational snapshot
            </h2>

        </div>


        <div class="rounded-2xl
                    border border-neutral-200 dark:border-neutral-800
                    bg-white dark:bg-[#1C1C1E]
                    shadow-sm overflow-hidden">

            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4">


                {{-- CASH --}}
                <div class="p-5 border-b sm:border-r xl:border-b-0
                            border-neutral-100 dark:border-neutral-800">

                    <div class="flex items-center gap-2
                                text-neutral-500 dark:text-neutral-500">

                        <svg class="w-4 h-4"
                             fill="none"
                             stroke="currentColor"
                             viewBox="0 0 24 24">
                            <path stroke-linecap="round"
                                  stroke-linejoin="round"
                                  stroke-width="2"
                                  d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>

                        <p class="text-[11px] font-semibold uppercase tracking-wider">
                            Cash in Drawer
                        </p>

                    </div>

                    <p class="mt-3 text-xl font-bold font-mono
                              text-neutral-950 dark:text-white">
                        ₱{{ number_format($metrics['cash_total'], 2) }}
                    </p>

                    <p class="mt-1 text-xs text-neutral-500 dark:text-neutral-500">
                        Physical cash collected
                    </p>

                </div>


                {{-- GCASH --}}
                <div class="p-5 border-b xl:border-b-0 xl:border-r
                            border-neutral-100 dark:border-neutral-800">

                    <div class="flex items-center gap-2
                                text-neutral-500 dark:text-neutral-500">

                        <svg class="w-4 h-4 text-[#0071E3] dark:text-[#0A84FF]"
                             fill="none"
                             stroke="currentColor"
                             viewBox="0 0 24 24">
                            <path stroke-linecap="round"
                                  stroke-linejoin="round"
                                  stroke-width="2"
                                  d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        </svg>

                        <p class="text-[11px] font-semibold uppercase tracking-wider">
                            GCash Settled
                        </p>

                    </div>

                    <p class="mt-3 text-xl font-bold font-mono
                              text-neutral-950 dark:text-white">
                        ₱{{ number_format($metrics['gcash_total'], 2) }}
                    </p>

                    <p class="mt-1 text-xs text-neutral-500 dark:text-neutral-500">
                        Verified digital payments
                    </p>

                </div>


                {{-- RESERVATIONS --}}
                <a href="{{ route('orders.index') }}"
                   class="block p-5 border-b sm:border-b-0 sm:border-r
                          border-neutral-100 dark:border-neutral-800
                          hover:bg-neutral-50 dark:hover:bg-neutral-800/40
                          transition-colors">

                    <div class="flex items-center gap-2
                                text-neutral-500 dark:text-neutral-500">

                        <svg class="w-4 h-4 text-amber-500 dark:text-amber-400"
                             fill="none"
                             stroke="currentColor"
                             viewBox="0 0 24 24">
                            <path stroke-linecap="round"
                                  stroke-linejoin="round"
                                  stroke-width="2"
                                  d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>

                        <p class="text-[11px] font-semibold uppercase tracking-wider">
                            Active Reservations
                        </p>

                    </div>

                    <p class="mt-3 text-xl font-bold font-mono
                              text-neutral-950 dark:text-white">

                        {{ $metrics['reserved_inventory'] }}

                        <span class="text-sm font-semibold
                                     text-neutral-500 dark:text-neutral-400">
                            pairs
                        </span>

                    </p>

                    <p class="mt-1 text-xs text-neutral-500 dark:text-neutral-500">
                        Awaiting payment
                    </p>

                </a>


                {{-- INVENTORY --}}
                <a href="{{ route('items.index') }}"
                   class="block p-5
                          border-neutral-100 dark:border-neutral-800
                          hover:bg-neutral-50 dark:hover:bg-neutral-800/40
                          transition-colors">

                    <div class="flex items-center gap-2
                                text-neutral-500 dark:text-neutral-500">

                        <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400"
                             fill="none"
                             stroke="currentColor"
                             viewBox="0 0 24 24">
                            <path stroke-linecap="round"
                                  stroke-linejoin="round"
                                  stroke-width="2"
                                  d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                        </svg>

                        <p class="text-[11px] font-semibold uppercase tracking-wider">
                            Available Floor Stock
                        </p>

                    </div>

                    <p class="mt-3 text-xl font-bold font-mono
                              text-neutral-950 dark:text-white">

                        {{ $metrics['available_inventory'] }}

                        <span class="text-sm font-semibold
                                     text-neutral-500 dark:text-neutral-400">
                            / {{ $metrics['total_inventory'] }} pairs
                        </span>

                    </p>

                    <p class="mt-1 text-xs text-neutral-500 dark:text-neutral-500">
                        Ready to sell
                    </p>

                </a>

            </div>

        </div>

    </section>


    {{-- ============================================================
         BATCH PROFITABILITY
    ============================================================= --}}
    <section class="rounded-2xl
                    border border-neutral-200 dark:border-neutral-800
                    bg-white dark:bg-[#1C1C1E]
                    shadow-sm overflow-hidden">

        <div class="px-6 py-5
                    flex flex-col lg:flex-row
                    lg:items-center lg:justify-between
                    gap-4
                    border-b border-neutral-200 dark:border-neutral-800">

            <div>

                <h2 class="text-base font-semibold
                           text-neutral-900 dark:text-white">
                    Batch profitability
                </h2>

                <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">
                    Unit economics and realized performance by inventory batch.
                </p>

            </div>


            <a href="{{ route('batches.index') }}"
               class="inline-flex items-center gap-1.5
                      text-sm font-semibold
                      text-[#0071E3] dark:text-[#0A84FF]
                      hover:underline">

                View all batches

                <svg class="w-4 h-4"
                     fill="none"
                     stroke="currentColor"
                     viewBox="0 0 24 24">
                    <path stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="2"
                          d="M9 5l7 7-7 7"/>
                </svg>

            </a>

        </div>

        @php
            $totalSales = collect($batchSummaries)->sum('realized_revenue');
            $totalProfit = collect($batchSummaries)->sum('order_profit_sum');
        @endphp


        <div class="overflow-x-auto">

            <table class="w-full min-w-[950px] text-left">

                <thead class="bg-neutral-50 dark:bg-neutral-900/50">

                    <tr class="border-b border-neutral-200 dark:border-neutral-800">

                        <th class="px-5 py-3.5
                                   text-xs font-semibold
                                   text-neutral-500 dark:text-neutral-400">
                            Batch
                        </th>

                        <th class="px-5 py-3.5
                                   text-xs font-semibold
                                   text-neutral-500 dark:text-neutral-400">
                            Supplier
                        </th>

                        <th class="px-5 py-3.5 text-center
                                   text-xs font-semibold
                                   text-neutral-500 dark:text-neutral-400">
                            Pairs
                        </th>

                        <th class="px-5 py-3.5 text-right
                                   text-xs font-semibold
                                   text-neutral-500 dark:text-neutral-400">
                            Batch Outlay
                        </th>

                        <th class="px-5 py-3.5 text-right
                                   text-xs font-semibold
                                   text-neutral-500 dark:text-neutral-400">
                            Sales
                        </th>

                        <th class="px-5 py-3.5 text-right
                                   text-xs font-semibold
                                   text-neutral-500 dark:text-neutral-400">
                            Order Profit
                        </th>

                    </tr>

                </thead>


                <tbody class="divide-y divide-neutral-100 dark:divide-neutral-800">

                    @foreach($batchSummaries as $batch)

                    <tr class="hover:bg-neutral-50/70 dark:hover:bg-neutral-800/30
                               transition-colors">

                        {{-- Batch --}}
                        <td class="px-5 py-4">

                            <a href="{{ route('batches.show', $batch['batch_id']) }}"
                               class="font-mono text-sm font-semibold
                                      text-[#0071E3] dark:text-[#0A84FF]
                                      hover:underline">

                                {{ $batch['batch_code'] }}

                            </a>

                            <span class="block mt-1 text-xs
                                         text-neutral-500 dark:text-neutral-500">
                                {{ $batch['date_acquired'] }}
                            </span>

                        </td>


                        {{-- Supplier --}}
                        <td class="px-5 py-4">

                            <span class="text-sm font-medium
                                         text-neutral-800 dark:text-neutral-200">
                                {{ $batch['supplier_name'] }}
                            </span>

                            <span class="block mt-1 text-xs
                                         text-neutral-500 dark:text-neutral-500">
                                {{ $batch['total_sacks'] }} sack(s)
                            </span>

                        </td>


                        {{-- Pairs --}}
                        <td class="px-5 py-4 text-center">

                            <div class="inline-flex items-center gap-2
                                        text-xs font-mono whitespace-nowrap">

                                <span class="font-semibold text-neutral-800 dark:text-neutral-200">
                                    {{ $batch['sold_pairs'] }} sold
                                </span>

                                <span class="text-neutral-300 dark:text-neutral-700">•</span>

                                <span class="text-amber-600 dark:text-amber-400">
                                    {{ $batch['reserved_pairs'] }} reserved
                                </span>

                                <span class="text-neutral-300 dark:text-neutral-700">•</span>

                                <span class="font-semibold text-emerald-600 dark:text-emerald-400">
                                    {{ $batch['available_pairs'] }} available
                                </span>

                            </div>

                        </td>


                        {{-- Cost --}}
                        <td class="px-5 py-4 text-right
                                   font-mono text-sm
                                   text-neutral-500 dark:text-neutral-500">

                            ₱{{ number_format($batch['total_cost'], 2) }}

                        </td>


                        {{-- Revenue --}}
                        <td class="px-5 py-4 text-right">

                            <span class="font-mono text-sm font-semibold
                                         text-neutral-900 dark:text-white">

                                ₱{{ number_format($batch['realized_revenue'], 2) }}

                            </span>

                        </td>


                        {{-- Profit --}}
                        <td class="px-5 py-4 text-right">

                            <span class="font-mono text-base font-bold
                                         text-emerald-600 dark:text-emerald-400">

                                +₱{{ number_format($batch['order_profit_sum'], 2) }}

                            </span>

                        </td>

                    </tr>

                    @endforeach

                </tbody>


                <tfoot class="border-t border-neutral-200 dark:border-neutral-800
                              bg-neutral-50 dark:bg-neutral-900/50">

                    <tr>

                        <td colspan="4"
                            class="px-5 py-4 text-right
                                   text-[11px] font-semibold uppercase tracking-wide
                                   text-neutral-500">
                            Totals
                        </td>

                        <td class="px-5 py-4 text-right
                                   font-mono text-sm font-bold
                                   text-neutral-900 dark:text-white">
                            ₱{{ number_format($totalSales, 2) }}
                        </td>

                        <td class="px-5 py-4 text-right
                                   font-mono text-base font-bold
                                   text-emerald-600 dark:text-emerald-400">
                            +₱{{ number_format($totalProfit, 2) }}
                        </td>

                    </tr>

                </tfoot>

            </table>

        </div>

    </section>


    {{-- ============================================================
         ACTIVITY
    ============================================================= --}}
    <section>


        {{-- RECENT SALES --}}
        <div class="rounded-2xl
                    border border-neutral-200 dark:border-neutral-800
                    bg-white dark:bg-[#1C1C1E]
                    shadow-sm overflow-hidden">

            <div class="px-6 py-5
                        flex items-center justify-between
                        border-b border-neutral-200 dark:border-neutral-800">

                <div>

                    <h2 class="text-base font-semibold
                               text-neutral-900 dark:text-white">
                        Recent settled sales
                    </h2>

                    <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">
                        Latest completed transactions.
                    </p>

                </div>


                <a href="{{ route('orders.index') }}"
                   class="text-sm font-semibold
                          text-[#0071E3] dark:text-[#0A84FF]
                          hover:underline">

                    All orders

                </a>

            </div>


            <div class="p-5 grid grid-cols-1 xl:grid-cols-2 gap-3">

                @forelse($recentTransactions as $tx)

                @php($isGcash = $tx->payment?->method === 'gcash')

                <div class="rounded-xl
                            border border-neutral-200 dark:border-neutral-800
                            p-4
                            hover:border-neutral-300 dark:hover:border-neutral-700
                            hover:bg-neutral-50 dark:hover:bg-neutral-800/40
                            transition-colors">

                    <div class="flex items-start gap-3.5">

                        {{-- Payment anchor --}}
                        <span class="w-10 h-10 shrink-0
                                     flex items-center justify-center
                                     rounded-xl
                                     {{ $isGcash
                                         ? 'bg-blue-50 text-[#0071E3] dark:bg-blue-950/40 dark:text-[#0A84FF]'
                                         : 'bg-emerald-50 text-emerald-600 dark:bg-emerald-950/40 dark:text-emerald-400' }}">

                            @if($isGcash)
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                            </svg>
                            @else
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                            @endif

                        </span>


                        <div class="min-w-0 flex-1">

                            <div class="flex items-start justify-between gap-3">

                                <div class="min-w-0">

                                    @php($txFirst = $tx->items->first())
                                    <p class="truncate text-sm font-semibold
                                              text-neutral-900 dark:text-white">
                                        {{ $txFirst?->brand }} {{ $txFirst?->model }}
                                        @if($tx->items->count() > 1)
                                            <span class="ml-1 text-xs font-medium text-neutral-500">+{{ $tx->items->count() - 1 }} pair(s)</span>
                                        @endif
                                    </p>

                                    <p class="mt-0.5 font-mono text-xs
                                              text-[#0071E3] dark:text-[#0A84FF]">
                                        {{ $tx->order_number }}
                                    </p>

                                </div>


                                <p class="shrink-0 font-mono text-lg font-bold
                                          text-neutral-950 dark:text-white">
                                    ₱{{ number_format($tx->awarded_price, 2) }}
                                </p>

                            </div>


                            <div class="mt-2 flex flex-wrap items-center gap-x-2 gap-y-1
                                        text-xs text-neutral-500 dark:text-neutral-400">

                                <span>
                                    Buyer:
                                    <strong class="font-medium text-neutral-700 dark:text-neutral-300">
                                        {{ $tx->customer->name }}
                                    </strong>
                                </span>

                                <span class="text-neutral-300 dark:text-neutral-700">•</span>

                                <span>{{ $tx->staff->name }}</span>

                                <span class="text-neutral-300 dark:text-neutral-700">•</span>

                                <span>{{ $tx->date_awarded->diffForHumans() }}</span>

                                <span class="ml-auto inline-flex rounded-md px-1.5 py-0.5
                                             text-[10px] font-semibold uppercase tracking-wide
                                             {{ $isGcash
                                                 ? 'bg-blue-50 text-blue-700 dark:bg-blue-950/40 dark:text-blue-300'
                                                 : 'bg-neutral-100 text-neutral-600 dark:bg-neutral-800 dark:text-neutral-300' }}">
                                    {{ $tx->payment?->method ?? 'Cash' }}
                                </span>

                            </div>

                        </div>

                    </div>

                </div>

                @empty

                <div class="py-12 text-center">

                    <div class="mx-auto w-10 h-10
                                flex items-center justify-center
                                rounded-full
                                bg-neutral-100 dark:bg-neutral-800
                                text-neutral-500">

                        <svg class="w-5 h-5"
                             fill="none"
                             stroke="currentColor"
                             viewBox="0 0 24 24">
                            <path stroke-linecap="round"
                                  stroke-linejoin="round"
                                  stroke-width="2"
                                  d="M9 14l6-6m0 0H9m6 0v6M5 5h14v14H5z"/>
                        </svg>

                    </div>

                    <p class="mt-3 text-sm font-medium
                              text-neutral-600 dark:text-neutral-300">
                        No settled transactions yet.
                    </p>

                    <p class="mt-1 text-xs text-neutral-500">
                        Completed sales will appear here.
                    </p>

                </div>

                @endforelse

            </div>

        </div>


    </section>


    {{-- ============================================================
         SECURITY & AUDIT (collapsible)
    ============================================================= --}}
    <section class="rounded-2xl
                    border border-neutral-200 dark:border-neutral-800
                    bg-white dark:bg-[#1C1C1E]
                    shadow-sm overflow-hidden"
             x-data="{ open: false }">

        <button type="button"
                @click="open = !open"
                :aria-expanded="open"
                class="w-full px-6 py-4
                       flex items-center justify-between gap-4
                       text-left
                       hover:bg-neutral-50 dark:hover:bg-neutral-800/40
                       transition-colors">

            <div class="flex items-center gap-3 min-w-0">

                <span class="w-9 h-9 shrink-0
                             flex items-center justify-center
                             rounded-xl
                             bg-neutral-100 dark:bg-neutral-800
                             text-neutral-500 dark:text-neutral-400">

                    <svg class="w-5 h-5"
                         fill="none"
                         stroke="currentColor"
                         viewBox="0 0 24 24">
                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              stroke-width="2"
                              d="M12 3l7 4v5c0 4.418-3.582 8-7 9-3.418-1-7-4.582-7-9V7l7-4z"/>
                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              stroke-width="2"
                              d="M9 12l2 2 4-4"/>
                    </svg>

                </span>

                <div class="min-w-0">

                    <h2 class="text-base font-semibold
                               text-neutral-900 dark:text-white">
                        Security &amp; Audit Log
                    </h2>

                    <p class="mt-0.5 text-xs
                              text-neutral-500 dark:text-neutral-500">
                        Sign-ins and system changes
                    </p>

                </div>

            </div>


            <span class="flex items-center gap-1.5 shrink-0
                         text-xs font-medium
                         text-neutral-500 dark:text-neutral-500">

                <span x-text="open ? 'Hide' : 'Show'">Show</span>

                <svg class="w-4 h-4 transition-transform duration-200"
                     :class="open ? 'rotate-180' : ''"
                     fill="none"
                     stroke="currentColor"
                     viewBox="0 0 24 24">
                    <path stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="2"
                          d="M19 9l-7 7-7-7"/>
                </svg>

            </span>

        </button>


        <div x-show="open"
             x-cloak
             x-transition:enter="transition ease-out duration-150"
             x-transition:enter-start="opacity-0 -translate-y-1"
             x-transition:enter-end="opacity-100 translate-y-0"
             class="border-t border-neutral-200 dark:border-neutral-800">

            <div class="p-5 max-h-[420px] overflow-y-auto">

                <div class="relative">

                    <span class="absolute left-[3px] top-2 bottom-2 w-px
                                 bg-neutral-200 dark:bg-neutral-800"
                          aria-hidden="true"></span>

                    @forelse($recentAuditLogs as $log)

                    <div class="relative pl-6 py-2.5">

                        <span class="absolute left-0 top-3
                                     w-[7px] h-[7px] rounded-full
                                     bg-neutral-300 dark:bg-neutral-600
                                     ring-4 ring-white dark:ring-[#1C1C1E]"></span>

                        <div class="flex items-center justify-between gap-3">

                            <span class="font-mono text-xs font-semibold
                                         text-neutral-800 dark:text-neutral-200">
                                {{ $log->action }}
                            </span>

                            <span class="shrink-0 text-xs
                                         text-neutral-500 dark:text-neutral-500">
                                {{ $log->created_at->diffForHumans() }}
                            </span>

                        </div>

                        <p class="mt-0.5 text-xs
                                  text-neutral-500 dark:text-neutral-400">

                            Actor:

                            <strong class="font-medium
                                           text-neutral-700 dark:text-neutral-300">
                                {{ $log->user?->name ?? 'System' }}
                            </strong>

                            @if($log->details)

                            <span class="ml-1.5 font-mono text-[10px]
                                         text-neutral-500 dark:text-neutral-500">
                                {{ json_encode($log->details) }}
                            </span>

                            @endif

                        </p>

                    </div>

                    @empty

                    <p class="py-8 text-center text-sm text-neutral-500">
                        No audit logs recorded yet.
                    </p>

                    @endforelse

                </div>

            </div>

        </div>

    </section>

</div>

@endsection