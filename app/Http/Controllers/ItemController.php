<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\SortsQueries;
use App\Models\Batch;
use App\Models\Item;
use App\Services\AuditService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
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

        if ($triage = $request->query('triage')) {
            $query->where('triage_status', $triage);
        }

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('sku', 'like', "%{$search}%")
                    ->orWhere('brand', 'like', "%{$search}%")
                    ->orWhere('model', 'like', "%{$search}%");
            });
        }

        // Default view prioritises available stock, then triage order:
        // Available → Reserved → Sold, and within that Washing → Under repair → Ready.
        if ($request->filled('sort')) {
            $this->applySort($query, ['sku', 'brand', 'model', 'size', 'condition', 'listed_price', 'status', 'created_at'], 'created_at', 'desc');
        } else {
            $query->reorder()
                ->orderByRaw("CASE status WHEN 'available' THEN 0 WHEN 'reserved' THEN 1 WHEN 'sold' THEN 2 ELSE 3 END")
                ->orderByRaw("CASE triage_status WHEN 'washing' THEN 0 WHEN 'under_repair' THEN 1 WHEN 'available' THEN 2 ELSE 3 END")
                ->orderByDesc('created_at');
        }

        $items = $query->paginate(25)->withQueryString();

        // Status/triage breakdown for the header counters (respects the batch filter only).
        $base = fn () => Item::query()->when($selectedBatchId, fn ($q) => $q->where('batch_id', $selectedBatchId));

        $statusCounts = $base()->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');
        $triageCounts = $base()->selectRaw('triage_status, count(*) as total')->groupBy('triage_status')->pluck('total', 'triage_status');

        $availableCount = (int) ($statusCounts['available'] ?? 0);
        $reservedCount = (int) ($statusCounts['reserved'] ?? 0);
        $soldCount = (int) ($statusCounts['sold'] ?? 0);

        $washingCount = (int) ($triageCounts[Item::TRIAGE_WASHING] ?? 0);
        $repairCount = (int) ($triageCounts[Item::TRIAGE_UNDER_REPAIR] ?? 0);
        $readyCount = (int) ($triageCounts[Item::TRIAGE_READY] ?? 0);

        return view('items.index', compact(
            'items', 'batches', 'selectedBatchId',
            'availableCount', 'reservedCount', 'soldCount',
            'washingCount', 'repairCount', 'readyCount'
        ));
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
            'repair_cost' => ['nullable', 'numeric', 'min:0'],
            'category' => ['nullable', 'string', 'max:50'],
            'triage_status' => ['nullable', Rule::in(Item::TRIAGE_STAGES)],
        ]);

        $validated['status'] = 'available';
        $validated['triage_status'] = $validated['triage_status'] ?? Item::TRIAGE_WASHING;

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
            'repair_cost' => ['nullable', 'numeric', 'min:0'],
            'category' => ['nullable', 'string', 'max:50'],
            'triage_status' => ['nullable', Rule::in(Item::TRIAGE_STAGES)],
        ]);

        if (array_key_exists('triage_status', $validated) && $validated['triage_status'] === null) {
            unset($validated['triage_status']);
        }

        $item->update($validated);

        AuditService::log('item_updated', $item, array_merge([
            'sku' => $item->sku,
            'brand' => $item->brand,
            'model' => $item->model,
        ], $validated));

        return back()->with('success', "Item {$item->sku} updated.");
    }

    /** Advance/change a pair's wash-and-repair stage (used by the triage worklist). */
    public function triage(Request $request, Item $item): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'triage_status' => ['required', Rule::in(Item::TRIAGE_STAGES)],
        ]);

        $item->update(['triage_status' => $validated['triage_status']]);

        AuditService::log('item_triage_updated', $item, [
            'sku' => $item->sku,
            'triage_status' => $item->triage_status,
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'triage_status' => $item->triage_status,
                'label' => $item->triageLabel(),
            ]);
        }

        return back()->with('success', "Pair {$item->sku} triage updated to {$item->triageLabel()}.");
    }

    public function destroy(Item $item): RedirectResponse
    {
        if ($item->status !== 'available') {
            return back()->with('error', "Item {$item->sku} is {$item->status} and cannot be deleted.");
        }

        $sku = $item->sku;
        $id = $item->id;
        $item->delete();

        AuditService::log('item_deleted', null, ['sku' => $sku]);

        return back()->with('undo', [
            'message' => "Pair {$sku} deleted.",
            'url' => route('items.restore', $id),
        ]);
    }

    public function restore(int $id): RedirectResponse
    {
        $item = Item::withTrashed()->findOrFail($id);
        $item->restore();

        AuditService::log('item_restored', $item, ['sku' => $item->sku]);

        return back()->with('success', "Pair {$item->sku} restored to inventory.");
    }
}
