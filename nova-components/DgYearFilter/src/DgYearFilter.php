<?php

namespace Peczis\DgYearFilter;

use App\Models\DgSummary;
use Carbon\Carbon;
use Laravel\Nova\Filters\Filter;
use Laravel\Nova\Http\Requests\NovaRequest;

class DgYearFilter extends Filter
{
    /**
     * The filter's component.
     *
     * @var string
     */
    public $component = 'dg-year-filter';

    /**
     * Apply the filter to the given query.
     *
     * @param \Laravel\Nova\Http\Requests\NovaRequest $request
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param mixed $value
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function apply(NovaRequest $request, $query, $value)
    {
        if (!$value)
            return $query;

        return $query->whereBetween('date', [
                Carbon::now()->startOfYear()->setYear($value),
                Carbon::now()->endOfYear()->setYear($value)]
        );
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
        $current = \Carbon\Carbon::now()->endOfYear()->setYear(2019);

        if ($latest = DgSummary::latest('date')->first())
            $current = $latest->date->endOfYear();

        return [
            'min_date'   => \Carbon\Carbon::now()->startOfYear()->setYear(2019),
            'max_date'   => $current,
            'start_date' => $current,
            'year_range' => [2019, $current->year],
            'date'       => $current->year,
        ];
    }

    /**
     * Get the displayable name of the filter.
     *
     * @return string
     */
    public function name()
    {
        return __('Year Filter');
    }
}
