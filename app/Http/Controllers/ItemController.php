<?php

namespace App\Http\Controllers;

use App\Models\Batch;
use App\Models\Item;
use App\Services\AuditService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ItemController extends Controller
{
    public function index(Request $request): View
    {
        $batches = Batch::latest()->get();
        $selectedBatchId = $request->query('batch_id', $batches->first()?->id);

        $query = Item::with('batch');

        if ($selectedBatchId) {
            $query->where('batch_id', $selectedBatchId);
        }

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('sku', 'like', "%{$search}%")
                    ->orWhere('brand', 'like', "%{$search}%")
                    ->orWhere('model', 'like', "%{$search}%");
            });
        }

        $items = $query->latest()->paginate(25)->withQueryString();

        return view('items.index', compact('items', 'batches', 'selectedBatchId'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'batch_id' => ['required', 'exists:batches,id'],
            'sku' => ['nullable', 'string', 'max:50', 'unique:items,sku'],
            'brand' => ['required', 'string', 'max:100'],
            'model' => ['required', 'string', 'max:150'],
            'price_tier' => ['required', 'string'],
            'listed_price' => ['required', 'numeric', 'min:0'],
            'condition' => ['required', 'string'],
            'size' => ['required', 'string'],
            'status' => ['required', 'in:available,reserved,sold'],
            'repair_cost' => ['nullable', 'numeric', 'min:0'],
            'category' => ['nullable', 'string'],
        ]);

        $batch = Batch::findOrFail($validated['batch_id']);

        if (empty($validated['sku'])) {
            $nextIndex = $batch->items()->count() + 1;
            $validated['sku'] = sprintf('%s-%03d', $batch->batch_code, $nextIndex);
        }

        $item = Item::create($validated);

        AuditService::log('item_added', $item, [
            'sku' => $item->sku,
            'price' => $item->listed_price,
        ]);

        return back()->with('success', "Item {$item->sku} added to batch {$batch->batch_code}.");
    }

    public function update(Request $request, Item $item): RedirectResponse
    {
        $validated = $request->validate([
            'price_tier' => ['required', 'string'],
            'listed_price' => ['required', 'numeric', 'min:0'],
            'condition' => ['required', 'string'],
            'size' => ['required', 'string'],
            'status' => ['required', 'in:available,reserved,sold'],
            'repair_cost' => ['nullable', 'numeric', 'min:0'],
        ]);

        $item->update($validated);

        AuditService::log('item_updated', $item, $validated);

        return back()->with('success', "Item {$item->sku} updated.");
    }

    public function updateTriage(Request $request, Item $item): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['nullable', 'in:available,reserved,sold'],
            'condition' => ['nullable', 'string'],
            'repair_cost' => ['nullable', 'numeric', 'min:0'],
        ]);

        $item->update($validated);

        AuditService::log('item_triage_updated', $item, $validated);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'item' => $item]);
        }

        return back()->with('success', "Triage status updated for {$item->sku}.");
    }
}
