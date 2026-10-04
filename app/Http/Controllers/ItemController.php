<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\SortsQueries;
use App\Models\Batch;
use App\Models\Item;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ItemController extends Controller
{
    use SortsQueries;

    public function index(Request $request): View
    {
        $batches = Batch::latest()->get();
        $selectedBatchId = $request->query('batch_id');

        $query = Item::with(['batch', 'orders.customer', 'orders.staff', 'orders.payment', 'orders.items', 'orders.delivery']);

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

        $this->applySort($query, ['sku', 'brand', 'model', 'size', 'condition', 'listed_price', 'status', 'created_at'], 'created_at', 'desc');
        $items = $query->paginate(25)->withQueryString();

        // Status breakdown for the header counters (respects the batch filter only).
        $statusCounts = Item::query()
            ->when($selectedBatchId, fn ($q) => $q->where('batch_id', $selectedBatchId))
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $availableCount = (int) ($statusCounts['available'] ?? 0);
        $reservedCount = (int) ($statusCounts['reserved'] ?? 0);
        $soldCount = (int) ($statusCounts['sold'] ?? 0);

        return view('items.index', compact('items', 'batches', 'selectedBatchId', 'availableCount', 'reservedCount', 'soldCount'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'batch_id' => ['required', 'exists:batches,id'],
            'sku' => ['nullable', 'string', 'max:50', 'unique:items,sku'],
            'brand' => ['required', 'string', 'max:100'],
            'model' => ['required', 'string', 'max:150'],
            'listed_price' => ['required', 'numeric', 'min:0'],
            'condition' => ['required', 'string'],
            'size' => ['required', 'string'],
            'status' => ['required', 'in:available,reserved,sold'],
            'repair_cost' => ['nullable', 'numeric', 'min:0'],
            'category' => ['nullable', 'string', 'max:50'],
        ]);

        $batch = Batch::findOrFail($validated['batch_id']);

        if (empty($validated['sku'])) {
            $existing = $batch->items()->pluck('sku');
            $index = $batch->items()->count() + 1;
            do {
                $candidate = sprintf('%s-%03d', $batch->batch_code, $index);
                $index++;
            } while ($existing->contains($candidate));

            $validated['sku'] = $candidate;
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
        if (! $item->isEditable()) {
            return back()->with('error', "Item {$item->sku} has already been {$item->status} and can no longer be edited.");
        }

        $validated = $request->validate([
            'brand' => ['required', 'string', 'max:100'],
            'model' => ['required', 'string', 'max:150'],
            'listed_price' => ['required', 'numeric', 'min:0'],
            'condition' => ['required', 'string'],
            'size' => ['required', 'string'],
            'status' => ['required', 'in:available,reserved,sold'],
            'repair_cost' => ['nullable', 'numeric', 'min:0'],
            'category' => ['nullable', 'string', 'max:50'],
        ]);

        $item->update($validated);

        AuditService::log('item_updated', $item, array_merge([
            'sku' => $item->sku,
            'brand' => $item->brand,
            'model' => $item->model,
        ], $validated));

        return back()->with('success', "Item {$item->sku} updated.");
    }
}
