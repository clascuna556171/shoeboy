<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SupplierController extends Controller
{
    public function index(): View
    {
        $suppliers = Supplier::withCount('batches')->latest()->get();
        return view('suppliers.index', compact('suppliers'));
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
        ]);

        return back()->with('success', "Supplier '{$supplier->name}' updated.");
    }

    public function destroy(Supplier $supplier): RedirectResponse
    {
        $name = $supplier->name;
        $supplier->delete();

        AuditService::log('supplier_deleted', null, ['name' => $name]);

        return back()->with('info', "Supplier '{$name}' deleted.");
    }
}
