<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Batch;
use App\Models\Delivery;
use App\Models\Expense;
use App\Models\Item;
use App\Models\Order;
use App\Models\Supplier;
use App\Services\ReportingService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(protected ReportingService $reportingService)
    {
    }

    public function index(Request $request): View
    {
        $user = $request->user();

        if ($user->isOwner()) {
            return $this->ownerDashboard();
        }

        return $this->staffDashboard();
    }

    protected function ownerDashboard(): View
    {
        $metrics = $this->reportingService->getOverallFinancialMetrics();
        $batchSummaries = $this->reportingService->getBatchProfitSummary();
        $recentAuditLogs = AuditLog::with('user')->latest()->take(10)->get();
        $recentTransactions = Order::with(['items.batch', 'customer', 'staff', 'payment'])
            ->whereIn('status', ['paid', 'fulfilled'])
            ->latest('date_awarded')
            ->take(8)
            ->get();

        $activeBatch = Batch::with('supplier')->latest()->first();
        $suppliersCount = Supplier::count();
        $pendingDeliveriesCount = Delivery::where('status', 'pending')->count();

        return view('dashboard.owner', compact(
            'metrics',
            'batchSummaries',
            'recentAuditLogs',
            'recentTransactions',
            'activeBatch',
            'suppliersCount',
            'pendingDeliveriesCount'
        ));
    }

    protected function staffDashboard(): View
    {
        $batches = Batch::with('supplier')->latest()->get();
        $activeBatch = $batches->first();

        $items = Item::with('batch')
            ->when($activeBatch, fn ($q) => $q->where('batch_id', $activeBatch->id))
            ->get();

        $activeClaims = Order::with(['items', 'customer', 'staff', 'payment'])
            ->whereIn('status', ['reserved', 'paid'])
            ->latest('date_awarded')
            ->get();

        $availableShoes = Item::with('batch')
            ->where('status', 'available')
            ->orderBy('brand')
            ->get();

        $expenses = Expense::with('batch')->latest('date')->take(10)->get();
        $pendingDeliveries = Delivery::with(['order.items', 'order.customer'])
            ->where('status', 'pending')
            ->latest()
            ->take(5)
            ->get();

        return view('dashboard.staff', compact(
            'batches',
            'activeBatch',
            'items',
            'activeClaims',
            'availableShoes',
            'expenses',
            'pendingDeliveries'
        ));
    }
}
