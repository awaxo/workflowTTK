<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

///
/// This scope is used to filter out featured users
///
class NonFeaturedScope implements Scope
{
    public function apply(Builder $builder, Model $model)
    {
        $builder->where('featured', 0);
    }
}