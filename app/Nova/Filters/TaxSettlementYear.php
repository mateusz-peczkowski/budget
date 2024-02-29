<?php

namespace App\Nova\Filters;

use Carbon\Carbon;
use Laravel\Nova\Filters\Filter;
use Laravel\Nova\Http\Requests\NovaRequest;

class TaxSettlementYear extends Filter
{
    /**
     * The filter's component.
     *
     * @var string
     */
    public $component = 'select-filter';

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
        if (!$value)
            return $query;

        $start = Carbon::parse(($value + 1) . '-01-01')->startOfYear();
        $end = Carbon::parse(($value + 1) . '-12-31')->endOfYear();

        return $query->whereBetween('submit_date', [$start, $end]);
    }

    /**
     * Get the filter's available options.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function options(NovaRequest $request)
    {
        $first = \App\Models\TaxSettlement::orderBy('submit_date', 'asc')->first();
        $last = \App\Models\TaxSettlement::orderBy('submit_date', 'desc')->first();

        if (!$first || !$last)
            return [];

        $first = $first->submit_date->format('Y');
        $last = $last->submit_date->format('Y');

        $years = [];

        for ($i = $first; $i <= $last; $i++)
            $years[] = $i - 1;

        return $years;
    }

    public function name()
    {
        return __('Tax Settlement Year');
    }
}
