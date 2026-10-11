<?php

namespace App\Http\Controllers;

use App\Services\ReportingService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function __construct(protected ReportingService $reportingService) {}

    public function index(Request $request): View
    {
        $request->validate([
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],
        ]);

        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');
        $sort = $request->query('sort');
        $direction = $request->query('direction') === 'asc' ? 'asc' : 'desc';

        $metrics = $this->reportingService->getOverallFinancialMetrics();

        $batchReports = $this->sortRows(
            $this->reportingService->getBatchProfitSummary(),
            $sort,
            $direction,
            ['batch_code', 'supplier_name', 'date_acquired', 'total_pairs', 'sold_pairs', 'reserved_pairs', 'available_pairs', 'total_cost', 'realized_revenue', 'order_profit_sum', 'batch_expenses', 'net_proceeds'],
        );
        $tierReports = $this->sortRows(
            $this->reportingService->getPriceTierSummary(),
            $sort,
            $direction,
            ['tier', 'total_items', 'sold_count', 'total_revenue', 'total_cogs', 'net_profit'],
        );
        $sessionReports = $this->sortRows(
            $this->reportingService->getSessionSummary($startDate, $endDate),
            $sort,
            $direction,
            ['date', 'orders_count', 'gross_sales', 'net_profit', 'cash_collected', 'gcash_collected'],
        );
        $salesLedger = $this->sortRows(
            $this->reportingService->getSalesLedger($startDate, $endDate),
            $sort,
            $direction,
            ['order_number', 'date', 'sku', 'brand_model', 'size', 'awarded_price', 'unit_profit', 'customer', 'payment_method', 'staff'],
        );
        $expenseLedger = $this->sortRows(
            $this->reportingService->getExpenseLedger($startDate, $endDate),
            $sort,
            $direction,
            ['date', 'category', 'description', 'reference_no', 'batch', 'amount'],
        );

        return view('reports.index', compact(
            'metrics',
            'batchReports',
            'tierReports',
            'sessionReports',
            'salesLedger',
            'expenseLedger',
            'startDate',
            'endDate',
            'sort',
            'direction'
        ));
    }

    /**
     * Sort an array of associative rows by a whitelisted column (numeric-aware).
     *
     * @param  array<int, array<string, mixed>>  $rows
     * @param  array<int, string>  $allowed
     * @return array<int, array<string, mixed>>
     */
    protected function sortRows(array $rows, ?string $sort, string $direction, array $allowed): array
    {
        if (! $sort || ! in_array($sort, $allowed, true)) {
            return $rows;
        }

        $multiplier = $direction === 'asc' ? 1 : -1;

        usort($rows, function (array $a, array $b) use ($sort, $multiplier) {
            $left = $a[$sort] ?? null;
            $right = $b[$sort] ?? null;

            if (is_numeric($left) && is_numeric($right)) {
                return ($left <=> $right) * $multiplier;
            }

            return strcasecmp((string) $left, (string) $right) * $multiplier;
        });

        return $rows;
    }

    public function exportExcel(Request $request): Response
    {
        $options = $this->exportOptions($request);

        $content = $this->reportingService->generateExcelExport($options);
        $suffix = ($options['start_date'] || $options['end_date'])
            ? '_'.($options['start_date'] ?: 'start').'_to_'.($options['end_date'] ?: 'today')
            : '';
        $filename = 'The_Shoe_Boy_Profit_Report'.$suffix.'_'.date('Ymd_His').'.xlsx';

        return $this->excelResponse($content, $filename);
    }

    public function exportAllExcel(): Response
    {
        $content = $this->reportingService->generateExcelExport([
            'sections' => ['summary', 'sales', 'expenses', 'sessions', 'batches', 'tiers', 'inventory'],
            'order_status' => 'paid_fulfilled',
            'sales_granularity' => 'pair',
            'inventory_status' => 'all',
            'include_repair' => true,
            'include_payment_ref' => true,
            'include_customer' => true,
            'include_notes' => true,
        ]);
        $filename = 'The_Shoe_Boy_Profit_Report_All_'.date('Ymd_His').'.xlsx';

        return $this->excelResponse($content, $filename);
    }

    /**
     * Assemble validated export options from the request.
     *
     * @return array<string, mixed>
     */
    protected function exportOptions(Request $request): array
    {
        [$startDate, $endDate] = $this->resolveRange($request);

        $validated = $request->validate([
            'sections' => ['nullable', 'array'],
            'sections.*' => ['in:summary,sales,expenses,sessions,batches,tiers,inventory'],
            'channel' => ['nullable', 'in:all,live_stream,walkin_pos'],
            'payment_method' => ['nullable', 'in:all,cash,gcash'],
            'order_status' => ['nullable', 'in:paid_fulfilled,fulfilled'],
            'sales_granularity' => ['nullable', 'in:pair,order'],
            'inventory_status' => ['nullable', 'in:all,available,reserved,sold'],
            'batch_id' => ['nullable', 'integer', 'exists:batches,id'],
            'staff_id' => ['nullable', 'integer', 'exists:users,id'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],
            'preset' => ['nullable', 'in:all,today,week,month,last30,year,custom'],
        ]);

        return [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'sections' => (array) $request->query('sections', []),
            'channel' => $validated['channel'] ?? 'all',
            'payment_method' => $validated['payment_method'] ?? 'all',
            'order_status' => $validated['order_status'] ?? 'paid_fulfilled',
            'sales_granularity' => $validated['sales_granularity'] ?? 'pair',
            'inventory_status' => $validated['inventory_status'] ?? 'all',
            'batch_id' => $validated['batch_id'] ?? null,
            'staff_id' => $validated['staff_id'] ?? null,
            'include_repair' => $request->has('include_repair') ? $request->boolean('include_repair') : true,
            'include_payment_ref' => $request->has('include_payment_ref') ? $request->boolean('include_payment_ref') : true,
            'include_customer' => $request->has('include_customer') ? $request->boolean('include_customer') : true,
            'include_notes' => $request->has('include_notes') ? $request->boolean('include_notes') : false,
        ];
    }

    /**
     * Resolve the requested export range from a preset or custom dates.
     *
     * @return array{0: ?string, 1: ?string}
     */
    protected function resolveRange(Request $request): array
    {
        $preset = $request->query('preset');
        $now = Carbon::now();

        return match ($preset) {
            'today' => [$now->toDateString(), $now->toDateString()],
            'week' => [$now->copy()->startOfWeek()->toDateString(), $now->toDateString()],
            'month' => [$now->copy()->startOfMonth()->toDateString(), $now->toDateString()],
            'last30' => [$now->copy()->subDays(29)->toDateString(), $now->toDateString()],
            'year' => [$now->copy()->startOfYear()->toDateString(), $now->toDateString()],
            'custom' => [$request->query('start_date'), $request->query('end_date')],
            default => [null, null],
        };
    }

    protected function excelResponse(string $content, string $filename): Response
    {
        return response($content, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }
}
