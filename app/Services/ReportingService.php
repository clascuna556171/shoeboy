<?php

namespace App\Services;

use App\Models\Batch;
use App\Models\Expense;
use App\Models\Item;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Carbon;

class ReportingService
{
    // Ginansya kada batch (allocated cost + repairs + expenses)
    public function getBatchProfitSummary(): array
    {
        $batches = Batch::with(['supplier', 'items.orderItems.order'])->get();

        $report = [];
        foreach ($batches as $batch) {
            $totalPairs = (int) $batch->total_pairs;
            $batchCost = (float) $batch->total_cost;
            $avgCost = $batch->average_item_cost;

            $items = $batch->items;
            $soldItems = $items->where('status', 'sold');
            $reservedItems = $items->where('status', 'reserved');
            $availableItems = $items->where('status', 'available');

            $realizedRevenue = 0.0;
            $realizedProfit = 0.0;
            $totalRepairs = (float) $items->sum('repair_cost');

            foreach ($soldItems as $item) {
                $line = $item->orderItems->first(
                    fn ($oi) => in_array($oi->order?->status, ['paid', 'fulfilled'], true)
                );
                if ($line) {
                    $awarded = (float) $line->awarded_price;
                    $realizedRevenue += $awarded;
                    $realizedProfit += ($awarded - $avgCost - (float) $item->repair_cost);
                }
            }

            $batchExpenses = (float) Expense::where('batch_id', $batch->id)->sum('amount');
            $netBatchProceeds = $realizedRevenue - $batchCost - $batchExpenses;

            $report[] = [
                'batch_id' => $batch->id,
                'batch_code' => $batch->batch_code,
                'supplier_name' => $batch->supplier?->name ?? 'Unknown',
                'date_acquired' => $batch->date_acquired?->format('Y-m-d'),
                'total_sacks' => $batch->total_sacks,
                'total_pairs' => $totalPairs,
                'total_cost' => $batchCost,
                'available_pairs' => $availableItems->count(),
                'reserved_pairs' => $reservedItems->count(),
                'sold_pairs' => $soldItems->count(),
                'realized_revenue' => round($realizedRevenue, 2),
                'order_profit_sum' => round($realizedProfit, 2),
                'batch_expenses' => round($batchExpenses, 2),
                'net_proceeds' => round($netBatchProceeds, 2),
            ];
        }

        return $report;
    }

    // Ginansya kada price tier (Budget, Mid, High, Grails)
    public function getPriceTierSummary(): array
    {
        $tiers = Item::select('price_tier')
            ->distinct()
            ->pluck('price_tier');

        $report = [];
        foreach ($tiers as $tier) {
            $items = Item::with(['batch', 'orderItems.order' => function ($q) {
                $q->whereIn('status', ['paid', 'fulfilled']);
            }])->where('price_tier', $tier)->get();

            $totalItems = $items->count();
            $soldCount = 0;
            $totalRevenue = 0.0;
            $totalCost = 0.0;

            foreach ($items as $item) {
                $line = $item->orderItems->first();
                if ($line) {
                    $soldCount++;
                    $totalRevenue += (float) $line->awarded_price;
                    $avgCost = $item->batch?->average_item_cost ?? 0;
                    $totalCost += ($avgCost + (float) $item->repair_cost);
                }
            }

            $profit = $totalRevenue - $totalCost;

            $report[] = [
                'tier' => $tier,
                'total_items' => $totalItems,
                'sold_count' => $soldCount,
                'total_revenue' => round($totalRevenue, 2),
                'total_cogs' => round($totalCost, 2),
                'net_profit' => round($profit, 2),
            ];
        }

        return $report;
    }

    // Halin ug ginansya kada adlaw / live session
    public function getSessionSummary(?string $startDate = null, ?string $endDate = null): array
    {
        $query = Order::with(['items.batch', 'payment', 'customer', 'staff'])
            ->whereIn('status', ['paid', 'fulfilled']);

        if ($startDate) {
            $query->whereDate('date_awarded', '>=', $startDate);
        }
        if ($endDate) {
            $query->whereDate('date_awarded', '<=', $endDate);
        }

        $orders = $query->orderByDesc('date_awarded')->get();

        $grouped = $orders->groupBy(function ($o) {
            return Carbon::parse($o->date_awarded)->format('Y-m-d');
        });

        $report = [];
        foreach ($grouped as $date => $dayOrders) {
            $totalSales = 0.0;
            $totalProfit = 0.0;
            $gcashTotal = 0.0;
            $cashTotal = 0.0;

            foreach ($dayOrders as $ord) {
                foreach ($ord->items as $item) {
                    $awarded = (float) ($item->pivot->awarded_price ?? 0);
                    $avgCost = $item->batch?->average_item_cost ?? 0;
                    $repair = (float) $item->repair_cost;

                    $totalSales += $awarded;
                    $totalProfit += ($awarded - $avgCost - $repair);
                }

                $orderTotal = (float) $ord->awarded_price;
                if ($ord->payment?->method === 'gcash') {
                    $gcashTotal += $orderTotal;
                } else {
                    $cashTotal += $orderTotal;
                }
            }

            $report[] = [
                'date' => $date,
                'orders_count' => $dayOrders->count(),
                'gross_sales' => round($totalSales, 2),
                'net_profit' => round($totalProfit, 2),
                'gcash_collected' => round($gcashTotal, 2),
                'cash_collected' => round($cashTotal, 2),
            ];
        }

        return $report;
    }

    // Kinatibuk-ang financial status (Sales, COGS, Gasto, Net)
    public function getOverallFinancialMetrics(): array
    {
        $paidOrders = Order::with('items.batch')->whereIn('status', ['paid', 'fulfilled'])->get();

        $totalRevenue = 0.0;
        $totalCogs = 0.0;

        foreach ($paidOrders as $ord) {
            $totalRevenue += (float) $ord->awarded_price;

            foreach ($ord->items as $item) {
                $avgCost = $item->batch?->average_item_cost ?? 0;
                $totalCogs += ($avgCost + (float) $item->repair_cost);
            }
        }

        $grossProfit = $totalRevenue - $totalCogs;
        $totalExpenses = (float) Expense::sum('amount');
        $netOperatingBalance = $grossProfit - $totalExpenses;

        $cashTotal = (float) Payment::where('method', 'cash')->sum('amount');
        $gcashTotal = (float) Payment::where('method', 'gcash')->sum('amount');

        return [
            'total_revenue' => round($totalRevenue, 2),
            'total_cogs' => round($totalCogs, 2),
            'gross_profit' => round($grossProfit, 2),
            'total_expenses' => round($totalExpenses, 2),
            'net_operating_balance' => round($netOperatingBalance, 2),
            'cash_total' => round($cashTotal, 2),
            'gcash_total' => round($gcashTotal, 2),
            'total_inventory' => Item::count(),
            'available_inventory' => Item::where('status', 'available')->count(),
            'reserved_inventory' => Item::where('status', 'reserved')->count(),
            'sold_inventory' => Item::where('status', 'sold')->count(),
        ];
    }

    // Pang-export sa Excel (UTF-8 BOM para di maguba formatting)
    public function generateCsvExport(): string
    {
        $now = Carbon::now();
        $metrics = $this->getOverallFinancialMetrics();
        $batchReport = $this->getBatchProfitSummary();
        $paidOrders = Order::with(['items.batch', 'customer', 'staff', 'payment'])
            ->whereIn('status', ['paid', 'fulfilled'])
            ->orderByDesc('date_awarded')
            ->get();
        $expenses = Expense::with('batch')->orderByDesc('date')->get();

        $csv = "\xEF\xBB\xBF"; // UTF-8 BOM
        $csv .= "THE SHOE BOY - INTEGRATED FINANCIAL & PROFIT REPORT\r\n";
        $csv .= "Generated At:,\"" . $now->toDateTimeString() . "\"\r\n\r\n";

        // 1. Executive Summary
        $csv .= "EXECUTIVE SUMMARY\r\n";
        $csv .= "Metric,Amount (PHP)\r\n";
        $csv .= "Total Footwear Gross Revenue," . $metrics['total_revenue'] . "\r\n";
        $csv .= "Cost of Goods Sold (Allocated Base + Repairs)," . $metrics['total_cogs'] . "\r\n";
        $csv .= "Gross Profit on Sales," . $metrics['gross_profit'] . "\r\n";
        $csv .= "Total Shop Operating Expenses," . $metrics['total_expenses'] . "\r\n";
        $csv .= "Net Operating Profit," . $metrics['net_operating_balance'] . "\r\n";
        $csv .= "Cash Drawer Collected," . $metrics['cash_total'] . "\r\n";
        $csv .= "GCash Verified," . $metrics['gcash_total'] . "\r\n\r\n";

        // 2. Batch Profit Summary
        $csv .= "BATCH PROFITABILITY REPORT\r\n";
        $csv .= "Batch Code,Supplier,Date Acquired,Total Sacks,Total Pairs,Batch Cost (PHP),Sold Pairs,Realized Revenue (PHP),Order Profit Sum (PHP),Expenses (PHP),Net Proceeds (PHP)\r\n";
        foreach ($batchReport as $b) {
            $csv .= "\"{$b['batch_code']}\",\"{$b['supplier_name']}\",\"{$b['date_acquired']}\",{$b['total_sacks']},{$b['total_pairs']},{$b['total_cost']},{$b['sold_pairs']},{$b['realized_revenue']},{$b['order_profit_sum']},{$b['batch_expenses']},{$b['net_proceeds']}\r\n";
        }
        $csv .= "\r\n";

        // 3. Transactions Ledger
        $csv .= "COMPLETED SALES TRANSACTIONS\r\n";
        $csv .= "Order No,Date,Channel,SKU,Brand & Model,Size,Condition,Awarded Price (PHP),Repair Cost (PHP),Unit Profit (PHP),Customer,Payment Method,Payment Ref,Staff\r\n";
        foreach ($paidOrders as $o) {
            foreach ($o->items as $item) {
                $awarded = (float) ($item->pivot->awarded_price ?? 0);
                $avgCost = $item->batch?->average_item_cost ?? 0;
                $repair = (float) $item->repair_cost;
                $unitProfit = $awarded - $avgCost - $repair;
                $title = str_replace('"', '""', ($item->brand ?? '') . ' ' . ($item->model ?? ''));

                $csv .= "\"{$o->order_number}\",\"{$o->date_awarded}\",\"{$o->order_type}\",\"{$item->sku}\",\"{$title}\",\"{$item->size}\",\"{$item->condition}\",{$awarded},{$repair},{$unitProfit},\"{$o->customer?->name}\",\"{$o->payment?->method}\",\"{$o->payment?->reference_no}\",\"{$o->staff?->name}\"\r\n";
            }
        }
        $csv .= "\r\n";

        // 4. Operating Expenses
        $csv .= "OPERATING EXPENSES\r\n";
        $csv .= "Date,Category,Description,Reference No,Batch,Amount (PHP)\r\n";
        foreach ($expenses as $e) {
            $desc = str_replace('"', '""', $e->description);
            $ref = str_replace('"', '""', (string) ($e->reference_no ?? ''));
            $bCode = $e->batch?->batch_code ?? 'General';
            $csv .= "\"{$e->date}\",\"{$e->category}\",\"{$desc}\",\"{$ref}\",\"{$bCode}\",{$e->amount}\r\n";
        }

        return $csv;
    }
}
