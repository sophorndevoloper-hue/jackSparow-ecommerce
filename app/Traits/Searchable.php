<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait Searchable
{
    /**
     * Scope a query to search across multiple columns.
     *
     * @param  Builder<static>  $query
     * @param  array<int, string>  $columns
     * @return Builder<static>
     */
    public function scopeSearch(Builder $query, ?string $term, array $columns): Builder
    {
        if (empty($term)) {
            return $query;
        }

        $operator = $query->getConnection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';

        return $query->where(function (Builder $subQuery) use ($term, $columns, $operator) {
            foreach ($columns as $index => $column) {
                if ($index === 0) {
                    $subQuery->where($column, $operator, "%{$term}%");
                } else {
                    $subQuery->orWhere($column, $operator, "%{$term}%");
                }
            }
        });
    }
}
