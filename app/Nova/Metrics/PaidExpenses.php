<?php

namespace App\Nova\Metrics;

use App\Models\Expense;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Metrics\Progress;

class PaidExpenses extends Progress
{
    /**
     * Calculate the value of the metric.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return mixed
     */
    public function calculate(NovaRequest $request)
    {
        $query = (new Expense)->newQuery();

        $query->tap(function ($query) use ($request) {
            return $this->applyFilterQuery($request, $query);
        });

        return $this
            ->sum($request, Expense::class, function ($query2) {
                return $query2->where('status', 'paid');
            }, 'value', target: $query->clone()->sum('value') ?: 1)
            ->format('0')
            ->suffix(' ' . __(config('nova.currency')) . ' / ' . number_format($query->clone()->sum('value'), 0, ',', ' ') . ' ' . __(config('nova.currency')))
            ->withoutSuffixInflection();
    }

    /**
     * Determine the amount of time the results of the metric should be cached.
     *
     * @return  \DateTimeInterface|\DateInterval|float|int
     */
    public function cacheFor()
    {
        // return now()->addMinutes(5);
    }

    /**
     * Get the URI key for the metric.
     *
     * @return string
     */
    public function uriKey()
    {
        return 'paid-expenses';
    }

    /**
     * Get the displayable name of the metric.
     *
     * @return string
     */
    public function name()
    {
        return __('Paid Expenses');
    }
}
