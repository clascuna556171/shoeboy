<?php

namespace App\Http\Controllers;

use App\Models\Batch;
use App\Models\Supplier;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BatchController extends Controller
{
    public function index(): View
    {
        $batches = Batch::with(['supplier', 'items'])
            ->withCount([
                'items as available_count' => fn ($q) => $q->where('status', 'available'),
                'items as reserved_count' => fn ($q) => $q->where('status', 'reserved'),
                'items as sold_count' => fn ($q) => $q->where('status', 'sold'),
            ])
            ->latest()
            ->get();

        $suppliers = Supplier::orderBy('name')->get();

        return view('batches.index', compact('batches', 'suppliers'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'supplier_id' => ['required', 'exists:suppliers,id'],
            'batch_code' => ['required', 'string', 'max:30', 'unique:batches,batch_code'],
            'date_acquired' => ['required', 'date'],
            'total_sacks' => ['required', 'integer', 'min:1'],
            'total_pairs' => ['required', 'integer', 'min:1'],
            'total_cost' => ['required', 'numeric', 'min:0'],
        ]);

        $batch = Batch::create($validated);

        AuditService::log('batch_intake_created', $batch, [
            'batch_code' => $batch->batch_code,
            'total_pairs' => $batch->total_pairs,
            'total_cost' => $batch->total_cost,
            'avg_cost' => $batch->average_item_cost,
        ]);

        return back()->with('success', "Batch intake {$batch->batch_code} recorded. Average pair cost: ₱{$batch->average_item_cost}.");
    }

    public function show(Batch $batch): View
    {
        $batch->load(['supplier', 'items.orders.customer', 'expenses']);
        $items = $batch->items()->latest()->paginate(50);

        return view('batches.show', compact('batch', 'items'));
    }
}
