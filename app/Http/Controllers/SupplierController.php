<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\SortsQueries;
use App\Models\Supplier;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SupplierController extends Controller
{
    use SortsQueries;

    public function index(Request $request): View
    {
        $search = $request->query('search');

        $query = Supplier::withCount('batches');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('contact_number', 'like', "%{$search}%");
            });
        }

        $this->applySort($query, ['name', 'contact_number', 'batches_count', 'created_at'], 'created_at', 'desc');
        $suppliers = $query->get();

        return view('suppliers.index', compact('suppliers', 'search'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'contact_number' => ['nullable', 'numeric'],
            'notes' => ['nullable', 'string'],
        ]);

        $supplier = Supplier::create($validated);

        AuditService::log('supplier_created', $supplier, [
            'name' => $supplier->name,
        ]);

        return back()->with('success', "Supplier '{$supplier->name}' added successfully.");
    }

    public function update(Request $request, Supplier $supplier): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'contact_number' => ['nullable', 'numeric'],
            'notes' => ['nullable', 'string'],
        ]);

        $supplier->update($validated);

        AuditService::log('supplier_updated', $supplier, [
            'name' => $supplier->name,
            'contact_number' => $supplier->contact_number,
            'notes' => $supplier->notes,
        ]);

        return back()->with('success', "Supplier '{$supplier->name}' updated.");
    }

    public function destroy(Supplier $supplier): RedirectResponse
    {
        $name = $supplier->name;
        $id = $supplier->id;
        $supplier->delete();

        AuditService::log('supplier_deleted', null, ['name' => $name]);

        return back()->with('undo', [
            'message' => "Supplier \"{$name}\" deleted.",
            'url' => route('suppliers.restore', $id),
        ]);
    }

    public function restore(int $id): RedirectResponse
    {
        $supplier = Supplier::withTrashed()->findOrFail($id);
        $supplier->restore();

        AuditService::log('supplier_restored', $supplier, ['name' => $supplier->name]);

        return back()->with('success', "Supplier '{$supplier->name}' restored.");
    }
}
