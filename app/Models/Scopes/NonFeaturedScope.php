<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * NonFeaturedScope is a custom Eloquent scope that filters out featured items.
 * It applies a condition to the query builder to only include items where 'featured' is false (0).
 */
class NonFeaturedScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param Builder $builder
     * @param Model $model
     * @return void
     */
    public function apply(Builder $builder, Model $model)
    {
        $builder->where('featured', 0);
    }
}