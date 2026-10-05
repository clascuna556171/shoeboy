<?php

namespace App\Services;

use App\Models\Batch;
use App\Models\Expense;
use App\Models\Item;
use App\Models\Order;
use App\Models\Payment;
use App\Support\XlsxWriter;
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

    // Ledger sa matag pares nga nahalin (individual sales) sa gipiling range
    public function getSalesLedger(?string $startDate = null, ?string $endDate = null): array
    {
        $query = Order::with(['items.batch', 'customer', 'staff', 'payment'])
            ->whereIn('status', ['paid', 'fulfilled']);

        if ($startDate) {
            $query->whereDate('date_awarded', '>=', $startDate);
        }
        if ($endDate) {
            $query->whereDate('date_awarded', '<=', $endDate);
        }

        $orders = $query->orderByDesc('date_awarded')->get();

        $rows = [];
        foreach ($orders as $order) {
            foreach ($order->items as $item) {
                $awarded = (float) ($item->pivot->awarded_price ?? 0);
                $avgCost = $item->batch?->average_item_cost ?? 0;
                $repair = (float) $item->repair_cost;

                $rows[] = [
                    'order_number' => $order->order_number,
                    'date' => $order->date_awarded?->format('Y-m-d H:i'),
                    'channel' => $order->order_type === 'walkin_pos' ? 'POS Walk-In' : 'Live Stream',
                    'sku' => $item->sku,
                    'brand_model' => trim(($item->brand ?? '').' '.($item->model ?? '')),
                    'size' => $item->size,
                    'condition' => $item->condition,
                    'awarded_price' => round($awarded, 2),
                    'repair_cost' => round($repair, 2),
                    'unit_profit' => round($awarded - $avgCost - $repair, 2),
                    'customer' => $order->customer?->display_handle,
                    'payment_method' => $order->payment?->method ? strtoupper($order->payment->method) : '',
                    'payment_ref' => $order->payment?->reference_no,
                    'staff' => $order->staff?->name,
                ];
            }
        }

        return $rows;
    }

    // Ledger sa matag gasto (individual expenses) sa gipiling range
    public function getExpenseLedger(?string $startDate = null, ?string $endDate = null): array
    {
        $query = Expense::with('batch');

        if ($startDate) {
            $query->whereDate('date', '>=', $startDate);
        }
        if ($endDate) {
            $query->whereDate('date', '<=', $endDate);
        }

        $rows = [];
        foreach ($query->orderByDesc('date')->get() as $expense) {
            $rows[] = [
                'date' => (string) $expense->date,
                'category' => $expense->category,
                'description' => $expense->description,
                'reference_no' => $expense->reference_no,
                'batch' => $expense->batch?->batch_code ?? 'General',
                'amount' => round((float) $expense->amount, 2),
            ];
        }

        return $rows;
    }

    // Halin karon nga adlaw
    public function getDaySales(?string $date = null): array
    {
        $day = $date ? Carbon::parse($date) : Carbon::now();

        return $this->summarizeSales($day->copy()->startOfDay(), $day->copy()->endOfDay());
    }

    // Halin karong bulana
    public function getMonthSales(?string $month = null): array
    {
        $base = $month ? Carbon::parse($month.'-01') : Carbon::now();

        return $this->summarizeSales($base->copy()->startOfMonth(), $base->copy()->endOfMonth());
    }

    protected function summarizeSales(Carbon $start, Carbon $end): array
    {
        $orders = Order::with(['items.batch', 'payment'])
            ->whereIn('status', ['paid', 'fulfilled'])
            ->whereBetween('date_awarded', [$start, $end])
            ->get();

        $gross = 0.0;
        $cash = 0.0;
        $gcash = 0.0;

        foreach ($orders as $order) {
            foreach ($order->items as $item) {
                $gross += (float) ($item->pivot->awarded_price ?? 0);
            }

            $orderTotal = (float) $order->awarded_price;
            if ($order->payment?->method === 'gcash') {
                $gcash += $orderTotal;
            } else {
                $cash += $orderTotal;
            }
        }

        return [
            'orders_count' => $orders->count(),
            'gross_sales' => round($gross, 2),
            'cash_collected' => round($cash, 2),
            'gcash_collected' => round($gcash, 2),
        ];
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
            'total_sales' => round($totalRevenue, 2),
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

    /**
     * Build an Excel workbook (SpreadsheetML 2003) string for the requested range/sections.
     * One worksheet per section with fixed column widths so Excel renders readable columns.
     */
    public function generateExcelExport(array $options = []): string
    {
        $available = ['summary', 'sales', 'expenses', 'sessions', 'batches', 'tiers', 'inventory'];
        $sections = $options['sections'] ?? [];
        $sections = empty($sections) ? $available : array_values(array_intersect($available, $sections));
        $has = fn (string $key) => in_array($key, $sections, true);

        $startDate = $options['start_date'] ?? null;
        $endDate = $options['end_date'] ?? null;
        $channel = $options['channel'] ?? 'all';
        $paymentMethod = $options['payment_method'] ?? 'all';
        $batchId = ! empty($options['batch_id']) ? (int) $options['batch_id'] : null;
        $staffId = ! empty($options['staff_id']) ? (int) $options['staff_id'] : null;
        $orderStatus = $options['order_status'] ?? 'paid_fulfilled';
        $granularity = $options['sales_granularity'] ?? 'pair';
        $inventoryStatus = $options['inventory_status'] ?? 'all';

        $includeRepair = (bool) ($options['include_repair'] ?? true);
        $includePaymentRef = (bool) ($options['include_payment_ref'] ?? true);
        $includeCustomer = (bool) ($options['include_customer'] ?? true);
        $includeNotes = (bool) ($options['include_notes'] ?? false);

        $now = Carbon::now();
        $metrics = $this->getOverallFinancialMetrics();

        $statuses = $orderStatus === 'fulfilled' ? ['fulfilled'] : ['paid', 'fulfilled'];

        $ordersQuery = Order::with(['items.batch', 'customer', 'staff', 'payment'])
            ->whereIn('status', $statuses)
            ->orderByDesc('date_awarded');

        if ($startDate) {
            $ordersQuery->whereDate('date_awarded', '>=', $startDate);
        }
        if ($endDate) {
            $ordersQuery->whereDate('date_awarded', '<=', $endDate);
        }
        if ($channel !== 'all') {
            $ordersQuery->where('order_type', $channel);
        }
        if ($paymentMethod !== 'all') {
            $ordersQuery->whereHas('payment', fn ($q) => $q->where('method', $paymentMethod));
        }
        if ($staffId) {
            $ordersQuery->where('staff_id', $staffId);
        }
        if ($batchId) {
            $ordersQuery->whereHas('items', fn ($q) => $q->where('batch_id', $batchId));
        }

        $paidOrders = $ordersQuery->get();

        $filters = [];
        $filters[] = ($startDate || $endDate)
            ? 'range '.($startDate ?: 'start').' → '.($endDate ?: 'today')
            : 'all time';
        if ($channel !== 'all') {
            $filters[] = 'channel: '.($channel === 'walkin_pos' ? 'Walk-in POS' : 'Live Stream');
        }
        if ($paymentMethod !== 'all') {
            $filters[] = 'payment: '.strtoupper($paymentMethod);
        }
        if ($orderStatus === 'fulfilled') {
            $filters[] = 'fulfilled only';
        }
        if ($staffId) {
            $filters[] = 'staff #'.$staffId;
        }
        if ($batchId) {
            $batchCode = Batch::whereKey($batchId)->value('batch_code');
            $filters[] = 'batch '.($batchCode ?? ('#'.$batchId));
        }
        $rangeLabel = ucfirst(implode(' · ', $filters));

        $sheets = [];

        if ($has('summary')) {
            $daySales = $this->getDaySales();
            $monthSales = $this->getMonthSales();

            $sheets[] = [
                'name' => 'Summary',
                'widths' => [240, 150, 80, 120, 120],
                'rows' => [
                    ['style' => 'sTitle', 'cells' => ['THE SHOE BOY — FINANCIAL & PROFIT REPORT']],
                    ['cells' => ['Generated at', $now->toDateTimeString()]],
                    ['cells' => ['Scope', $rangeLabel]],
                    [],
                    ['style' => 'sHeader', 'cells' => ['Period', 'Gross Sales (PHP)', 'Orders', 'Cash (PHP)', 'GCash (PHP)']],
                    ['cells' => ['Sales Today ('.$now->format('Y-m-d').')', $this->n($daySales['gross_sales']), $this->n($daySales['orders_count']), $this->n($daySales['cash_collected']), $this->n($daySales['gcash_collected'])]],
                    ['cells' => ['Sales This Month ('.$now->format('Y-m').')', $this->n($monthSales['gross_sales']), $this->n($monthSales['orders_count']), $this->n($monthSales['cash_collected']), $this->n($monthSales['gcash_collected'])]],
                    [],
                    ['style' => 'sHeader', 'cells' => ['Metric', 'Amount (PHP)']],
                    ['cells' => ['Total Sales', $this->cur($metrics['total_sales'])]],
                    ['cells' => ['Total Expenses', $this->cur($metrics['total_expenses'])]],
                    ['cells' => ['Total Footwear Gross Revenue', $this->cur($metrics['total_revenue'])]],
                    ['cells' => ['Cost of Goods Sold (Allocated Base + Repairs)', $this->cur($metrics['total_cogs'])]],
                    ['cells' => ['Gross Profit on Sales', $this->cur($metrics['gross_profit'])]],
                    ['cells' => ['Net Operating Profit', $this->cur($metrics['net_operating_balance'])]],
                    ['cells' => ['Cash Drawer Collected', $this->cur($metrics['cash_total'])]],
                    ['cells' => ['GCash Verified', $this->cur($metrics['gcash_total'])]],
                ],
            ];
        }

        if ($has('batches')) {
            $rows = [
                ['style' => 'sHeader', 'cells' => ['Batch Code', 'Supplier', 'Date Acquired', 'Total Sacks', 'Total Pairs', 'Batch Cost (PHP)', 'Sold Pairs', 'Realized Revenue (PHP)', 'Order Profit Sum (PHP)', 'Expenses (PHP)', 'Net Proceeds (PHP)']],
            ];
            $batchRows = $this->getBatchProfitSummary();
            if ($batchId) {
                $batchRows = array_values(array_filter($batchRows, fn ($b) => (int) $b['batch_id'] === $batchId));
            }
            foreach ($batchRows as $b) {
                $rows[] = ['cells' => [
                    $b['batch_code'], $b['supplier_name'], (string) $b['date_acquired'],
                    $this->n($b['total_sacks']), $this->n($b['total_pairs']), $this->cur($b['total_cost']),
                    $this->n($b['sold_pairs']), $this->cur($b['realized_revenue']), $this->cur($b['order_profit_sum']),
                    $this->cur($b['batch_expenses']), $this->cur($b['net_proceeds']),
                ]];
            }
            $sheets[] = ['name' => 'Batch Profitability', 'widths' => [110, 180, 120, 80, 80, 120, 80, 150, 150, 120, 140], 'rows' => $rows];
        }

        if ($has('tiers')) {
            $rows = [
                ['style' => 'sHeader', 'cells' => ['Price Tier', 'Units Sold', 'Total Units', 'Gross Sales (PHP)', 'Allocated COGS (PHP)', 'Net Profit (PHP)']],
            ];
            foreach ($this->getPriceTierSummary() as $t) {
                $rows[] = ['cells' => [
                    $t['tier'], $this->n($t['sold_count']), $this->n($t['total_items']),
                    $this->cur($t['total_revenue']), $this->cur($t['total_cogs']), $this->cur($t['net_profit']),
                ]];
            }
            $sheets[] = ['name' => 'Price Tiers', 'widths' => [180, 90, 90, 140, 150, 140], 'rows' => $rows];
        }

        if ($has('sessions')) {
            $rows = [
                ['style' => 'sHeader', 'cells' => ['Date', 'Orders', 'Pairs', 'Gross Sales (PHP)', 'Net Profit (PHP)', 'Cash (PHP)', 'GCash (PHP)']],
            ];
            $grouped = $paidOrders->groupBy(fn ($o) => Carbon::parse($o->date_awarded)->format('Y-m-d'));
            foreach ($grouped as $date => $dayOrders) {
                $sales = 0.0;
                $profit = 0.0;
                $cash = 0.0;
                $gcash = 0.0;
                $pairs = 0;
                foreach ($dayOrders as $ord) {
                    foreach ($ord->items as $item) {
                        $awarded = (float) ($item->pivot->awarded_price ?? 0);
                        $avgCost = $item->batch?->average_item_cost ?? 0;
                        $sales += $awarded;
                        $profit += $awarded - $avgCost - (float) $item->repair_cost;
                        $pairs++;
                    }
                    $orderTotal = (float) $ord->awarded_price;
                    if ($ord->payment?->method === 'gcash') {
                        $gcash += $orderTotal;
                    } else {
                        $cash += $orderTotal;
                    }
                }
                $rows[] = ['cells' => [
                    $date, $this->n($dayOrders->count()), $this->n($pairs),
                    $this->cur($sales), $this->cur($profit), $this->cur($cash), $this->cur($gcash),
                ]];
            }
            $sheets[] = ['name' => 'Session Summary', 'widths' => [130, 80, 80, 150, 150, 130, 130], 'rows' => $rows];
        }

        if ($has('sales')) {
            if ($granularity === 'order') {
                $header = ['Order No', 'Date', 'Channel', 'Pairs', 'Brands', 'Awarded Price (PHP)', 'Order Profit (PHP)'];
                if ($includeCustomer) {
                    $header[] = 'Customer';
                }
                $header[] = 'Payment Method';
                if ($includePaymentRef) {
                    $header[] = 'Payment Ref';
                }
                $header[] = 'Staff';
                if ($includeNotes) {
                    $header[] = 'Notes';
                }

                $rows = [['style' => 'sHeader', 'cells' => $header]];
                foreach ($paidOrders as $o) {
                    $total = 0.0;
                    $profit = 0.0;
                    foreach ($o->items as $item) {
                        $awarded = (float) ($item->pivot->awarded_price ?? 0);
                        $avgCost = $item->batch?->average_item_cost ?? 0;
                        $total += $awarded;
                        $profit += $awarded - $avgCost - (float) $item->repair_cost;
                    }
                    $brands = $o->items->pluck('brand')->filter()->unique()->take(3)->implode(', ');

                    $line = [
                        $o->order_number, (string) $o->date_awarded,
                        $o->order_type === 'walkin_pos' ? 'POS Walk-In' : 'Live Stream',
                        $this->n($o->items->count()), $brands !== '' ? $brands : 'Mixed',
                        $this->cur($total), $this->cur($profit),
                    ];
                    if ($includeCustomer) {
                        $line[] = $o->customer?->display_handle;
                    }
                    $line[] = $o->payment?->method ? strtoupper($o->payment->method) : '';
                    if ($includePaymentRef) {
                        $line[] = $o->payment?->reference_no;
                    }
                    $line[] = $o->staff?->name;
                    if ($includeNotes) {
                        $line[] = $o->notes;
                    }
                    $rows[] = ['cells' => $line];
                }
                $sheets[] = ['name' => 'Sales Ledger', 'widths' => array_fill(0, count($header), 140), 'rows' => $rows];
            } else {
                $header = ['Order No', 'Date', 'Channel', 'SKU', 'Brand & Model', 'Size', 'Condition'];
                if ($includeRepair) {
                    $header[] = 'Repair Cost (PHP)';
                }
                $header[] = 'Awarded Price (PHP)';
                $header[] = 'Unit Profit (PHP)';
                if ($includeCustomer) {
                    $header[] = 'Customer';
                }
                $header[] = 'Payment Method';
                if ($includePaymentRef) {
                    $header[] = 'Payment Ref';
                }
                $header[] = 'Staff';
                if ($includeNotes) {
                    $header[] = 'Notes';
                }

                $rows = [['style' => 'sHeader', 'cells' => $header]];
                foreach ($paidOrders as $o) {
                    foreach ($o->items as $item) {
                        $awarded = (float) ($item->pivot->awarded_price ?? 0);
                        $avgCost = $item->batch?->average_item_cost ?? 0;
                        $repair = (float) $item->repair_cost;

                        $line = [
                            $o->order_number, (string) $o->date_awarded,
                            $o->order_type === 'walkin_pos' ? 'POS Walk-In' : 'Live Stream',
                            $item->sku, trim(($item->brand ?? '').' '.($item->model ?? '')), $item->size, $item->condition,
                        ];
                        if ($includeRepair) {
                            $line[] = $this->cur($repair);
                        }
                        $line[] = $this->cur($awarded);
                        $line[] = $this->cur($awarded - $avgCost - $repair);
                        if ($includeCustomer) {
                            $line[] = $o->customer?->display_handle;
                        }
                        $line[] = $o->payment?->method ? strtoupper($o->payment->method) : '';
                        if ($includePaymentRef) {
                            $line[] = $o->payment?->reference_no;
                        }
                        $line[] = $o->staff?->name;
                        if ($includeNotes) {
                            $line[] = $o->notes;
                        }
                        $rows[] = ['cells' => $line];
                    }
                }
                $widths = [150, 140, 110, 90, 240, 70, 90];
                for ($i = 7; $i < count($header); $i++) {
                    $widths[] = 130;
                }
                $sheets[] = ['name' => 'Sales Ledger', 'widths' => $widths, 'rows' => $rows];
            }
        }

        if ($has('expenses')) {
            $rows = [
                ['style' => 'sHeader', 'cells' => ['Operating Expense Ledger']],
                ['style' => 'sHeader', 'cells' => ['Date', 'Category', 'Description', 'Reference No', 'Batch', 'Amount (PHP)']],
            ];
            $expenseQuery = Expense::with('batch')->orderByDesc('date');
            if ($startDate) {
                $expenseQuery->whereDate('date', '>=', $startDate);
            }
            if ($endDate) {
                $expenseQuery->whereDate('date', '<=', $endDate);
            }
            if ($batchId) {
                $expenseQuery->where('batch_id', $batchId);
            }
            $expenseTotal = 0.0;
            foreach ($expenseQuery->get() as $e) {
                $expenseTotal += (float) $e->amount;
                $rows[] = ['cells' => [
                    (string) $e->date, $e->category, $e->description, (string) ($e->reference_no ?? ''),
                    $e->batch?->batch_code ?? 'General', $this->cur($e->amount),
                ]];
            }
            $rows[] = ['style' => 'sHeader', 'cells' => ['', '', '', '', 'Total (PHP)', $this->cur($expenseTotal)]];
            $sheets[] = ['name' => 'Expense Ledger', 'widths' => [120, 160, 280, 150, 110, 130], 'rows' => $rows];
        }

        if ($has('inventory')) {
            $invQuery = Item::query();
            if ($batchId) {
                $invQuery->where('batch_id', $batchId);
            }
            $invTotal = (clone $invQuery)->count();
            $invAvailable = (clone $invQuery)->where('status', 'available')->count();
            $invReserved = (clone $invQuery)->where('status', 'reserved')->count();
            $invSold = (clone $invQuery)->where('status', 'sold')->count();

            $invRows = [['style' => 'sHeader', 'cells' => ['Metric', 'Count']]];
            if ($inventoryStatus === 'all') {
                $invRows[] = ['cells' => ['Total Pairs', $this->n($invTotal)]];
                $invRows[] = ['cells' => ['Available', $this->n($invAvailable)]];
                $invRows[] = ['cells' => ['Reserved', $this->n($invReserved)]];
                $invRows[] = ['cells' => ['Sold', $this->n($invSold)]];
            } else {
                $map = ['available' => $invAvailable, 'reserved' => $invReserved, 'sold' => $invSold];
                $invRows[] = ['cells' => [ucfirst($inventoryStatus), $this->n($map[$inventoryStatus] ?? 0)]];
            }

            $sheets[] = ['name' => 'Inventory', 'widths' => [180, 90], 'rows' => $invRows];
        }

        return (new XlsxWriter)->write($sheets);
    }

    private function n($value): array
    {
        return ['v' => $value, 'num' => true];
    }

    private function cur($value): array
    {
        return ['v' => $value, 'num' => true, 'cur' => true];
    }
}
