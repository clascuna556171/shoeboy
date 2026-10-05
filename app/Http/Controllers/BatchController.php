<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\SortsQueries;
use App\Models\Batch;
use App\Models\Supplier;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BatchController extends Controller
{
    use SortsQueries;

    public function index(Request $request): View
    {
        $search = $request->query('search');

        $query = Batch::with(['supplier', 'items'])
            ->withCount([
                'items as available_count' => fn ($q) => $q->where('status', 'available'),
                'items as reserved_count' => fn ($q) => $q->where('status', 'reserved'),
                'items as sold_count' => fn ($q) => $q->where('status', 'sold'),
            ]);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('batch_code', 'like', "%{$search}%")
                    ->orWhereHas('supplier', fn ($sq) => $sq->where('name', 'like', "%{$search}%"));
            });
        }

        $this->applySort($query, ['batch_code', 'total_sacks', 'total_pairs', 'total_cost', 'date_acquired', 'created_at'], 'created_at', 'desc');
        $batches = $query->get();

        $suppliers = Supplier::orderBy('name')->get();

        return view('batches.index', compact('batches', 'suppliers', 'search'));
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
        $batch->load(['supplier', 'expenses']);

        $stats = [
            'total_pairs' => (int) $batch->total_pairs,
            'logged' => $batch->items()->count(),
            'available' => $batch->items()->where('status', 'available')->count(),
            'reserved' => $batch->items()->where('status', 'reserved')->count(),
            'sold' => $batch->items()->where('status', 'sold')->count(),
        ];

        return view('batches.show', compact('batch', 'stats'));
    }

    public function update(Request $request, Batch $batch): RedirectResponse
    {
        $validated = $request->validate([
            'supplier_id' => ['required', 'exists:suppliers,id'],
            'batch_code' => ['required', 'string', 'max:30', Rule::unique('batches', 'batch_code')->ignore($batch->id)],
            'date_acquired' => ['required', 'date'],
            'total_sacks' => ['required', 'integer', 'min:1'],
            'total_pairs' => ['required', 'integer', 'min:1'],
            'total_cost' => ['required', 'numeric', 'min:0'],
        ]);

        $batch->update($validated);

        AuditService::log('batch_updated', $batch, [
            'batch_code' => $batch->batch_code,
            'total_pairs' => $batch->total_pairs,
            'total_cost' => $batch->total_cost,
        ]);

        return back()->with('success', "Batch {$batch->batch_code} updated.");
    }

    public function destroy(Batch $batch): RedirectResponse
    {
        if ($batch->items()->exists() || $batch->expenses()->exists()) {
            return back()->with('error', "Batch {$batch->batch_code} still has pairs or expenses and cannot be deleted. Empty it first.");
        }

        $code = $batch->batch_code;
        $id = $batch->id;
        $batch->delete();

        AuditService::log('batch_deleted', null, ['batch_code' => $code]);

        return back()->with('undo', [
            'message' => "Batch {$code} deleted.",
            'url' => route('batches.restore', $id),
        ]);
    }

    public function restore(int $id): RedirectResponse
    {
        $batch = Batch::withTrashed()->findOrFail($id);
        $batch->restore();

        AuditService::log('batch_restored', $batch, ['batch_code' => $batch->batch_code]);

        return back()->with('success', "Batch {$batch->batch_code} restored.");
    }
}
