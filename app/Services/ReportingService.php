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

    // Halin karon nga adlaw
    public function getDaySales(?string $date = null): array
    {
        $day = $date ? Carbon::parse($date) : Carbon::now();

        return $this->summarizeSales($day->copy()->startOfDay(), $day->copy()->endOfDay());
    }

    // Halin karong bulana
    public function getMonthSales(?string $month = null): array
    {
        $base = $month ? Carbon::parse($month . '-01') : Carbon::now();

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
    public function generateExcelExport(?string $startDate = null, ?string $endDate = null, array $sections = []): string
    {
        $available = ['summary', 'transactions', 'batches', 'tiers', 'expenses', 'inventory'];
        $sections = empty($sections) ? $available : array_values(array_intersect($available, $sections));
        $has = fn (string $key) => in_array($key, $sections, true);

        $now = Carbon::now();
        $metrics = $this->getOverallFinancialMetrics();

        $ordersQuery = Order::with(['items.batch', 'customer', 'staff', 'payment'])
            ->whereIn('status', ['paid', 'fulfilled'])
            ->orderByDesc('date_awarded');

        if ($startDate) {
            $ordersQuery->whereDate('date_awarded', '>=', $startDate);
        }
        if ($endDate) {
            $ordersQuery->whereDate('date_awarded', '<=', $endDate);
        }

        $paidOrders = $ordersQuery->get();

        $rangeLabel = ($startDate || $endDate)
            ? 'Filtered range: ' . ($startDate ?: 'Start') . ' to ' . ($endDate ?: 'Today')
            : 'All time';

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
                    ['cells' => ['Sales Today (' . $now->format('Y-m-d') . ')', $this->n($daySales['gross_sales']), $this->n($daySales['orders_count']), $this->n($daySales['cash_collected']), $this->n($daySales['gcash_collected'])]],
                    ['cells' => ['Sales This Month (' . $now->format('Y-m') . ')', $this->n($monthSales['gross_sales']), $this->n($monthSales['orders_count']), $this->n($monthSales['cash_collected']), $this->n($monthSales['gcash_collected'])]],
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
            foreach ($this->getBatchProfitSummary() as $b) {
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

        if ($has('transactions')) {
            $rows = [
                ['style' => 'sHeader', 'cells' => ['Order No', 'Date', 'Channel', 'SKU', 'Brand & Model', 'Size', 'Condition', 'Awarded Price (PHP)', 'Repair Cost (PHP)', 'Unit Profit (PHP)', 'Customer', 'Payment Method', 'Payment Ref', 'Staff']],
            ];
            foreach ($paidOrders as $o) {
                foreach ($o->items as $item) {
                    $awarded = (float) ($item->pivot->awarded_price ?? 0);
                    $avgCost = $item->batch?->average_item_cost ?? 0;
                    $repair = (float) $item->repair_cost;
                    $rows[] = ['cells' => [
                        $o->order_number, (string) $o->date_awarded, $o->order_type === 'walkin_pos' ? 'POS Walk-In' : 'Live Stream',
                        $item->sku, trim(($item->brand ?? '') . ' ' . ($item->model ?? '')), $item->size, $item->condition,
                        $this->cur($awarded), $this->cur($repair), $this->cur($awarded - $avgCost - $repair),
                        $o->customer?->display_handle, $o->payment?->method ? strtoupper($o->payment->method) : '', $o->payment?->reference_no, $o->staff?->name,
                    ]];
                }
            }
            $sheets[] = ['name' => 'Transactions', 'widths' => [150, 140, 110, 90, 260, 80, 100, 130, 110, 120, 150, 110, 150, 130], 'rows' => $rows];
        }

        if ($has('expenses')) {
            $rows = [
                ['style' => 'sHeader', 'cells' => ['Operating Expenses']],
                ['style' => 'sHeader', 'cells' => ['Date', 'Category', 'Description', 'Reference No', 'Batch', 'Amount (PHP)']],
            ];
            foreach (Expense::with('batch')->orderByDesc('date')->get() as $e) {
                $rows[] = ['cells' => [
                    (string) $e->date, $e->category, $e->description, (string) ($e->reference_no ?? ''),
                    $e->batch?->batch_code ?? 'General', $this->cur($e->amount),
                ]];
            }
            $sheets[] = ['name' => 'Expenses', 'widths' => [120, 160, 280, 150, 110, 130], 'rows' => $rows];
        }

        if ($has('inventory')) {
            $sheets[] = [
                'name' => 'Inventory',
                'widths' => [180, 90],
                'rows' => [
                    ['style' => 'sHeader', 'cells' => ['Metric', 'Count']],
                    ['cells' => ['Total Pairs', $this->n($metrics['total_inventory'])]],
                    ['cells' => ['Available', $this->n($metrics['available_inventory'])]],
                    ['cells' => ['Reserved', $this->n($metrics['reserved_inventory'])]],
                    ['cells' => ['Sold', $this->n($metrics['sold_inventory'])]],
                ],
            ];
        }

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<?mso-application progid="Excel.Sheet"?>' . "\n";
        $xml .= '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet" xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet">';
        $xml .= $this->excelStyles();
        foreach ($sheets as $sheet) {
            $xml .= $this->renderWorksheet($sheet['name'], $sheet['widths'], $sheet['rows']);
        }
        $xml .= '</Workbook>';

        return $xml;
    }

    private function n($value): array
    {
        return ['v' => $value, 'num' => true];
    }

    private function cur($value): array
    {
        return ['v' => $value, 'num' => true, 'cur' => true];
    }

    private function excelStyles(): string
    {
        return '<Styles>'
            . '<Style ss:ID="Default" ss:Name="Normal"><Alignment ss:Vertical="Center"/><Font ss:FontName="Calibri" ss:Size="11"/></Style>'
            . '<Style ss:ID="sTitle"><Font ss:Bold="1" ss:Size="14" ss:Color="#1D1D1F"/><Alignment ss:Vertical="Center"/></Style>'
            . '<Style ss:ID="sHeader"><Font ss:Bold="1" ss:Color="#FFFFFF"/><Interior ss:Color="#1D1D1F" ss:Pattern="Solid"/><Alignment ss:Vertical="Center" ss:WrapText="1"/></Style>'
            . '<Style ss:ID="sNumber"><NumberFormat ss:Format="#,##0"/><Alignment ss:Horizontal="Right" ss:Vertical="Center"/></Style>'
            . '<Style ss:ID="sText"><Alignment ss:Vertical="Center" ss:WrapText="1"/></Style>'
            . '<Style ss:ID="sCurrency"><NumberFormat ss:Format="&quot;₱&quot;#,##0.00"/><Alignment ss:Horizontal="Right" ss:Vertical="Center"/></Style>'
            . '</Styles>';
    }

    /**
     * @param  array<int, int|float>  $widths
     * @param  array<int, array{cells?: array, style?: string}>  $rows
     */
    private function renderWorksheet(string $name, array $widths, array $rows): string
    {
        $out = '<Worksheet ss:Name="' . $this->xml($name) . '"><Table ss:DefaultRowHeight="16">';
        foreach ($widths as $width) {
            $out .= '<Column ss:AutoFitWidth="0" ss:Width="' . (int) $width . '"/>';
        }
        foreach ($rows as $row) {
            $rowStyle = $row['style'] ?? null;
            $out .= '<Row>';
            foreach (array_values($row['cells'] ?? []) as $cell) {
                $out .= $this->renderCell($cell, $rowStyle);
            }
            $out .= '</Row>';
        }
        $out .= '</Table></Worksheet>';

        return $out;
    }

    private function renderCell(mixed $cell, ?string $rowStyle): string
    {
        if (is_array($cell)) {
            $value = $cell['v'] ?? '';
            $isNumber = $cell['num'] ?? false;
            $isCurrency = $cell['cur'] ?? false;
        } else {
            $value = $cell;
            $isNumber = false;
            $isCurrency = false;
        }

        $value = $value === null ? '' : $value;

        if ($value === '') {
            return $rowStyle ? '<Cell ss:StyleID="' . $rowStyle . '"/>' : '<Cell/>';
        }

        if ($isNumber) {
            $style = $isCurrency ? 'sCurrency' : 'sNumber';
            return '<Cell ss:StyleID="' . $style . '"><Data ss:Type="Number">' . (0 + $value) . '</Data></Cell>';
        }

        $attr = $rowStyle ? ' ss:StyleID="' . $rowStyle . '"' : '';
        return '<Cell' . $attr . '><Data ss:Type="String">' . $this->xml((string) $value) . '</Data></Cell>';
    }

    private function xml(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_XML1, 'UTF-8');
    }
}
