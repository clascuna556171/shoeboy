<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\SortsQueries;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    use SortsQueries;

    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search'));
        $category = $request->query('category') ?: null;

        $query = AuditLog::with('user');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('action', 'like', "%{$search}%")
                    ->orWhere('ip_address', 'like', "%{$search}%")
                    ->orWhereHas('user', fn ($uq) => $uq->where('name', 'like', "%{$search}%"));
            });
        }

        $prefixes = match ($category) {
            'security' => ['user_login', 'user_logout', 'login_'],
            'inventory' => ['item_', 'batch_'],
            'sales' => ['order_', 'payment_', 'delivery_'],
            'finance' => ['expense_'],
            'admin' => ['staff_', 'supplier_'],
            default => [],
        };

        if (! empty($prefixes)) {
            $query->where(function ($q) use ($prefixes) {
                foreach ($prefixes as $prefix) {
                    $q->orWhere('action', 'like', $prefix . '%');
                }
            });
        }

        $this->applySort($query, ['created_at', 'action'], 'created_at', 'desc');

        $logs = $query->paginate(30)->withQueryString();

        return view('audit.index', compact('logs', 'search', 'category'));
    }
}
