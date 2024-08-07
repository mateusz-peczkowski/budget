<?php

namespace App\Nova\Filters;

use Laravel\Nova\Http\Requests\NovaRequest;
use Outl1ne\NovaMultiselectFilter\MultiselectFilter;

class TaxSettlementType extends MultiselectFilter
{
    /**
     * Apply the filter to the given query.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  mixed  $value
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function apply(NovaRequest $request, $query, $value)
    {
        $taxNames = \App\Models\TaxSettlementType::whereIn('id', $value)->get()->pluck('name')->toArray();

        return $query->whereHas('taxSettlementType', function ($query) use ($taxNames) {
            $query->whereIn('name', $taxNames);
        });
    }

    /**
     * Get the filter's available options.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function options(NovaRequest $request)
    {
        return \App\Models\TaxSettlementType::all()->pluck('name', 'id')->unique()->toArray();
    }

    public function name()
    {
        return __('Tax Settlement Type');
    }
}
