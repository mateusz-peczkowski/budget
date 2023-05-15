<?php

namespace Peczis\PeriodFilter;

use Laravel\Nova\Filters\Filter;
use Laravel\Nova\Http\Requests\NovaRequest;

class PeriodFilter extends Filter
{
    /**
     * The filter's component.
     *
     * @var string
     */
    public $component = 'period-filter';

    /**
     * Apply the filter to the given query.
     *
     * @param \Laravel\Nova\Http\Requests\NovaRequest $request
     * @param \Illuminate\Database\Eloquent\Builder   $query
     * @param mixed                                   $value
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function apply(NovaRequest $request, $query, $value)
    {
        if (!$value)
            return $query;

        $fromMonth = $value[0]['month'] + 1;
        $fromYear = $value[0]['year'];

        $toMonth = $value[1]['month'] + 1;
        $toYear = $value[1]['year'];

        $startPeriod = \App\Models\Period::where('year', $fromYear)->where('month', $fromMonth)->first();
        $endPeriod = \App\Models\Period::where('year', $toYear)->where('month', $toMonth)->first();

        return $query->whereBetween('period_id', [$startPeriod->id, $endPeriod->id]);
    }

    /**
     * Get the filter's available options.
     *
     * @param \Laravel\Nova\Http\Requests\NovaRequest $request
     *
     * @return array
     */
    public function options(NovaRequest $request)
    {
        $first = \App\Models\Period::first();
        $last = \App\Models\Period::orderBy('id', 'desc')->first();
        $current = \Carbon\Carbon::now()->startOfMonth()->setMonth(current_period()->month)->setYear(current_period()->year);

        return [
            'min_date'   => \Carbon\Carbon::now()->startOfMonth()->setMonth($first->month)->setYear($first->year),
            'max_date'   => \Carbon\Carbon::now()->startOfMonth()->setMonth($last->month)->setYear($last->year)->endOfMonth(),
            'start_date' => $current,
            'date' => [
                [
                    'month' => $current->month - 1,
                    'year'  => $current->year,
                ],
                [
                    'month' => $current->month - 1,
                    'year'  => $current->year,
                ]
            ],
        ];
    }

    /**
     * Get the displayable name of the filter.
     *
     * @return string
     */
    public function name()
    {
        return __('Period Filter');
    }
}
