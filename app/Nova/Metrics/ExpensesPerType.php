<?php

namespace App\Nova\Metrics;

use App\Models\Expense;
use App\Models\ExpenseType;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Metrics\Partition;

class ExpensesPerType extends Partition
{
    /**
     * Calculate the value of the metric.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return mixed
     */
    public function calculate(NovaRequest $request)
    {
        return $this
            ->sum($request, Expense::class, 'value', 'expense_type_id')
            ->label(function($value) {
                return ExpenseType::find($value) ? ExpenseType::find($value)->name : __('Unknown');
            });
    }

    /**
     * Determine the amount of time the results of the metric should be cached.
     *
     * @return \DateTimeInterface|\DateInterval|float|int|null
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
        return 'expenses-per-type';
    }

    /**
     * Get the displayable name of the metric.
     *
     * @return string
     */
    public function name()
    {
        return __('Expenses Per Type');
    }
}
