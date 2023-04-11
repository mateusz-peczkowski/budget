<?php

namespace App\Nova\Metrics;

use App\Models\Expense;
use App\Models\Income;
use Illuminate\Support\Collection;
use Laravel\Nova\FilterDecoder;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Metrics\MetricTableRow;
use Laravel\Nova\Metrics\Table;
use Peczis\PeriodFilter\PeriodFilter;

class ExpensesVsIncomesCalculations extends Table
{
    /**
     * Calculate the value of the metric.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return mixed
     */
    public function calculate(NovaRequest $request)
    {
        $incomeQuery = (new Income)->newQuery();

        if ($this->filters instanceof Collection) {
            $fakeRequest = $request;

            foreach((new FilterDecoder($fakeRequest->filter, $this->filters))
                        ->filters() as $filter) {
                if ($filter->filter instanceof PeriodFilter)
                    $incomeQuery = (new $filter->filter->class)->apply($fakeRequest, $incomeQuery, $filter->value);
            }
        }

        $expenseQuery = (new Expense)->newQuery();

        $expenseQuery->tap(function ($query) use ($request) {
            return $this->applyFilterQuery($request, $query);
        });

        $incomesGross = $incomeQuery->clone()->sum('gross');
        $expensesValue = $expenseQuery->clone()->sum('value');

        $incomeGross = number_format($incomesGross, 2, ',', ' ');
        $expenseValue = number_format($expensesValue, 2, ',', ' ');
        $profit = number_format($incomesGross - $expensesValue, 2, ',', ' ');

        return [
            MetricTableRow::make()
                ->title($incomeGross . ' ' . config('nova.currency'))
                ->subtitle(__('Income')),

            MetricTableRow::make()
                ->title($expenseValue . ' ' . config('nova.currency'))
                ->subtitle(__('Expenses')),

            MetricTableRow::make()
                ->title(__('Balance') . ': ' . $profit . ' ' . config('nova.currency'))
        ];
    }

    /**
     * Get the displayable name of the metric.
     *
     * @return string
     */
    public function name()
    {
        return __('Income Vs Expenses Calculations');
    }
}
