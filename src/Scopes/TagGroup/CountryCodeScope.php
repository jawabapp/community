<?php


namespace Jawabapp\Community\Scopes\TagGroup;

use Jawabapp\Community\Models\TagGroup;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class CountryCodeScope implements Scope
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
        if (TagGroup::$enableGlobalScope && auth('api')->check()) {
            $builder->where(function ($query) {
                $query->whereNull('country_code');

                if (auth('api')->user()->phone_country ?? false) {
                    $query->orWhere('country_code', auth('api')->user()->phone_country);
                }
            });
        }
    }
}
