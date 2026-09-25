<?php

namespace App\Http\Controllers;

use App\Models\Batch;
use App\Models\Expense;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ExpenseController extends Controller
{
    public function index(): View
    {
        $expenses = Expense::with('batch')->latest('date')->paginate(30);
        $batches = Batch::latest()->get();
        $totalExpenses = Expense::sum('amount');

        return view('expenses.index', compact('expenses', 'batches', 'totalExpenses'));
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
        $expense->delete();

        AuditService::log('expense_deleted', null, [
            'description' => $desc,
            'amount' => $amt,
        ]);

        return back()->with('info', "Expense deleted.");
    }
}
