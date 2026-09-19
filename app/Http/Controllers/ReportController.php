<?php

namespace App\Http\Controllers;

use App\Services\ReportingService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function __construct(protected ReportingService $reportingService)
    {
    }

    public function index(Request $request): View
    {
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        $metrics = $this->reportingService->getOverallFinancialMetrics();
        $batchReports = $this->reportingService->getBatchProfitSummary();
        $tierReports = $this->reportingService->getPriceTierSummary();
        $sessionReports = $this->reportingService->getSessionSummary($startDate, $endDate);

        return view('reports.index', compact(
            'metrics',
            'batchReports',
            'tierReports',
            'sessionReports',
            'startDate',
            'endDate'
        ));
    }

    public function exportCsv(): Response
    {
        $csvContent = $this->reportingService->generateCsvExport();
        $filename = 'The_Shoe_Boy_Profit_Report_' . date('Ymd_His') . '.csv';

        return response($csvContent, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }
}
