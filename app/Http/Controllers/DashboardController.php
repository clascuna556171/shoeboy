<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Batch;
use App\Models\Delivery;
use App\Models\Expense;
use App\Models\Item;
use App\Models\Order;
use App\Models\Supplier;
use App\Services\OrderService;
use App\Services\ReportingService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        protected ReportingService $reportingService,
        protected OrderService $orderService
    ) {}

    public function index(Request $request): View
    {
        // Lazily sweep lapsed reservations whenever anyone opens the workspace.
        $this->orderService->releaseExpiredReservations();

        $user = $request->user();

        if ($user->isOwner()) {
            return $this->ownerDashboard();
        }

        return $this->staffDashboard($request);
    }

    /** Staff operations console — open to both staff and owners. */
    public function console(Request $request): View
    {
        $this->orderService->releaseExpiredReservations();

        return $this->staffDashboard($request);
    }

    protected function ownerDashboard(): View
    {
        $metrics = $this->reportingService->getOverallFinancialMetrics();
        $batchSummaries = $this->reportingService->getBatchProfitSummary();
        $recentAuditLogs = AuditLog::with('user')->latest()->take(25)->get();
        $recentTransactions = Order::with(['items.batch', 'customer', 'staff', 'payment'])
            ->whereIn('status', ['paid', 'fulfilled'])
            ->latest('date_awarded')
            ->take(8)
            ->get();

        $activeBatch = Batch::with('supplier')->latest()->first();
        $suppliersCount = Supplier::count();
        $pendingDeliveriesCount = Delivery::where('status', 'pending')
            ->whereHas('order', fn ($q) => $q->where('order_type', '!=', 'walkin_pos'))
            ->count();

        // "Needs attention" panel data (live orders only — walk-in POS is paid + fulfilled on the spot).
        $pendingDeliveries = Delivery::with(['order.customer'])
            ->where('status', 'pending')
            ->whereHas('order', fn ($q) => $q->where('order_type', '!=', 'walkin_pos'))
            ->latest()
            ->take(4)
            ->get();

        $expiringReservations = Order::with(['customer', 'items'])
            ->where('status', 'reserved')
            ->whereNotNull('expires_at')
            ->orderBy('expires_at')
            ->take(4)
            ->get();
        $expiringReservationsCount = Order::where('status', 'reserved')->whereNotNull('expires_at')->count();

        $washingCount = Item::where('triage_status', Item::TRIAGE_WASHING)->count();
        $repairCount = Item::where('triage_status', Item::TRIAGE_UNDER_REPAIR)->count();
        $readyStock = Item::where('status', 'available')->where('triage_status', Item::TRIAGE_READY)->count();

        return view('dashboard.owner', compact(
            'metrics',
            'batchSummaries',
            'recentAuditLogs',
            'recentTransactions',
            'activeBatch',
            'suppliersCount',
            'pendingDeliveriesCount',
            'pendingDeliveries',
            'expiringReservations',
            'expiringReservationsCount',
            'washingCount',
            'repairCount',
            'readyStock'
        ));
    }

    protected function staffDashboard(Request $request): View
    {
        $batches = Batch::with('supplier')->latest()->get();

        // Batch picker: defaults to ALL batches; a valid id scopes to that batch, invalid falls back to the newest.
        $requested = $request->query('batch');
        if ($requested === null || $requested === 'all') {
            $activeBatch = null;
        } else {
            $activeBatch = $batches->firstWhere('id', (int) $requested) ?? $batches->first();
        }

        $items = Item::with([
            'batch',
            'orders' => fn ($q) => $q->whereIn('status', ['reserved', 'paid', 'fulfilled'])
                ->with(['items', 'customer', 'staff', 'payment', 'delivery']),
        ])
            ->when($activeBatch, fn ($q) => $q->where('batch_id', $activeBatch->id))
            ->get();

        $activeClaims = Order::with(['items', 'customer', 'staff', 'payment'])
            ->whereIn('status', ['reserved', 'paid'])
            ->when($activeBatch, fn ($q) => $q->whereHas('items', fn ($iq) => $iq->where('batch_id', $activeBatch->id)))
            ->latest('date_awarded')
            ->get();

        $expenses = Expense::with('batch')
            ->when($activeBatch, fn ($q) => $q->where('batch_id', $activeBatch->id))
            ->latest('date')
            ->take(10)
            ->get();

        $pendingDeliveries = Delivery::with(['order.items', 'order.customer'])
            ->where('status', 'pending')
            ->when($activeBatch, fn ($q) => $q->whereHas('order.items', fn ($iq) => $iq->where('batch_id', $activeBatch->id)))
            ->latest()
            ->take(5)
            ->get();

        return view('dashboard.staff', compact(
            'batches',
            'activeBatch',
            'items',
            'activeClaims',
            'expenses',
            'pendingDeliveries'
        ));
    }
}
