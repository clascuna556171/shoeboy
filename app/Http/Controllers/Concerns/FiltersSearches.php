<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Database\Eloquent\Builder;

/**
 * Reusable "search across columns (and one level of relations)" query helper,
 * so list controllers don't each re-implement the same like-closure.
 */
trait FiltersSearches
{
    /**
     * @param  array<int, string>  $columns  Direct columns to match.
     * @param  array<string, array<int, string>>  $relations  relation => columns.
     */
    protected function applySearch(Builder $query, ?string $term, array $columns, array $relations = []): void
    {
        $term = $term !== null ? trim($term) : null;

        if ($term === null || $term === '') {
            return;
        }

        $query->where(function (Builder $q) use ($term, $columns, $relations) {
            foreach ($columns as $column) {
                $q->orWhere($column, 'like', "%{$term}%");
            }

            foreach ($relations as $relation => $relColumns) {
                $q->orWhereHas($relation, function (Builder $rq) use ($relColumns, $term) {
                    foreach ($relColumns as $column) {
                        $rq->orWhere($column, 'like', "%{$term}%");
                    }
                });
            }
        });
    }
}
