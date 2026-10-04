<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Request as RequestFacade;

trait SortsQueries
{
    /**
     * Apply a whitelisted, request-driven sort to a query, falling back to a default.
     *
     * @param  Builder  $query
     * @param  array<int, string>  $allowed
     */
    protected function applySort($query, array $allowed, string $defaultColumn, string $defaultDirection = 'desc'): void
    {
        $request = RequestFacade::instance();
        $sort = $request->query('sort');
        $direction = strtolower((string) $request->query('direction')) === 'asc' ? 'asc' : 'desc';

        $query->reorder();

        if ($sort && in_array($sort, $allowed, true)) {
            $query->orderBy($sort, $direction);
        } else {
            $query->orderBy($defaultColumn, $defaultDirection);
        }
    }
}
