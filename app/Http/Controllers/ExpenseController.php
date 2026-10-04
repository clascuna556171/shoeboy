<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\SortsQueries;
use App\Models\Batch;
use App\Models\Expense;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ExpenseController extends Controller
{
    use SortsQueries;

    public function index(Request $request): View
    {
        $search = $request->query('search');
        $category = $request->query('category');

        $query = Expense::with('batch');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                    ->orWhere('reference_no', 'like', "%{$search}%");
            });
        }

        if ($category) {
            $query->where('category', $category);
        }

        $this->applySort($query, ['date', 'category', 'description', 'reference_no', 'amount', 'created_at'], 'date', 'desc');
        $expenses = $query->paginate(30)->withQueryString();
        $batches = Batch::latest()->get();
        $categories = Expense::select('category')->distinct()->orderBy('category')->pluck('category');
        $totalExpenses = Expense::sum('amount');

        return view('expenses.index', compact('expenses', 'batches', 'categories', 'totalExpenses', 'search', 'category'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'category' => ['required', 'string', 'max:100'],
            'description' => ['required', 'string', 'max:255'],
            'reference_no' => ['nullable', 'string', 'max:100'],
            'amount' => ['required', 'numeric', 'min:0'],
            'date' => ['required', 'date'],
            'batch_id' => ['nullable', 'exists:batches,id'],
        ]);

        $expense = Expense::create($validated);

        AuditService::log('expense_recorded', $expense, [
            'category' => $expense->category,
            'amount' => $expense->amount,
            'description' => $expense->description,
            'reference_no' => $expense->reference_no,
        ]);

        return back()->with('success', "Expense of ₱" . number_format($expense->amount, 2) . " recorded.");
    }

    public function destroy(Expense $expense): RedirectResponse
    {
        $desc = $expense->description;
        $amt = $expense->amount;
        $id = $expense->id;
        $expense->delete();

        AuditService::log('expense_deleted', null, [
            'description' => $desc,
            'amount' => $amt,
        ]);

        return back()->with('undo', [
            'message' => "Expense \"{$desc}\" deleted.",
            'url' => route('expenses.restore', $id),
        ]);
    }

    public function restore(int $id): RedirectResponse
    {
        $expense = Expense::withTrashed()->findOrFail($id);
        $expense->restore();

        AuditService::log('expense_restored', $expense, [
            'description' => $expense->description,
            'amount' => $expense->amount,
        ]);

        return back()->with('success', 'Expense restored.');
    }
}
