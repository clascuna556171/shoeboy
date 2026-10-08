<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\FiltersSearches;
use App\Http\Controllers\Concerns\SortsQueries;
use App\Http\Requests\SupplierRequest;
use App\Models\Supplier;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SupplierController extends Controller
{
    use FiltersSearches;
    use SortsQueries;

    public function index(Request $request): View
    {
        $search = $request->query('search');

        $query = Supplier::withCount('batches');

        if ($search) {
            $this->applySearch($query, $search, ['name', 'contact_number']);
        }

        $this->applySort($query, ['name', 'contact_number', 'batches_count', 'created_at'], 'created_at', 'desc');
        $suppliers = $query->get();

        return view('suppliers.index', compact('suppliers', 'search'));
    }

    public function store(SupplierRequest $request): RedirectResponse
    {
        $supplier = Supplier::create($request->validated());

        AuditService::log('supplier_created', $supplier, [
            'name' => $supplier->name,
        ]);

        return back()->with('success', "Supplier '{$supplier->name}' added successfully.");
    }

    public function update(SupplierRequest $request, Supplier $supplier): RedirectResponse
    {
        $supplier->update($request->validated());

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
