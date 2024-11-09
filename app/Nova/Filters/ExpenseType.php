<?php

namespace App\Nova\Filters;

use Laravel\Nova\Http\Requests\NovaRequest;
use Outl1ne\NovaMultiselectFilter\MultiselectFilter;

class ExpenseType extends MultiselectFilter
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
        return $query->whereHas('expenseType', function ($query) use ($value) {
            $query->whereIn('expense_type_id', $value);
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
        return \App\Models\ExpenseType::orderBy('name')->get()->pluck('name', 'id')->toArray();
    }

    public function name()
    {
        return __('Expense Type');
    }
}
