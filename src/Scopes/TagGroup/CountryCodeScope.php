<?php


namespace JawabApp\Community\Scopes\TagGroup;

use App\Models\TagGroup;
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
        if (TagGroup::$enableGlobalScope && auth()->check()) {
            $builder->where(function ($query) {
                $query->whereNull('country_code');

                if (auth()->user()->phone_country ?? false) {
                    $query->orWhere('country_code', auth()->user()->phone_country);
                }
            });
        }
    }
}
