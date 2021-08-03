<?php


namespace Jawabapp\Community\Scopes\TagGroup;

use Jawabapp\Community\Models\TagGroup;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class PublishedScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return void
     */
    public function apply(Builder $builder, Model $model)
    {
        if (TagGroup::$enableGlobalScope) {
            $builder->where('is_published', true);
        }
    }
}
