<?php

namespace App\Nova\Metrics;

use App\Models\Expense;
use App\Models\Income;
use Illuminate\Support\Collection;
use Laravel\Nova\Filters\FilterDecoder;
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

        $expenseLeftQuery = (new Expense)->newQuery();

        $expenseLeftQuery->tap(function ($query) use ($request) {
            return $this->applyFilterQuery($request, $query);
        })
            ->where('status', 'pending');

        $incomesGross = $incomeQuery->sum('gross');
        $expensesValue = $expenseQuery->sum('value');
        $expensesLeftValue = $expenseLeftQuery->sum('value');

        $incomeGross = number_format($incomesGross, 2, ',', ' ');
        $expenseValue = number_format($expensesValue, 2, ',', ' ');
        $expenseLeftValue = number_format($expensesLeftValue, 2, ',', ' ');
        $profit = number_format($incomesGross - $expensesValue, 2, ',', ' ');

        return [
            MetricTableRow::make()
                ->title($incomeGross . ' ' . __(config('nova.currency')))
                ->subtitle(__('Income')),

            MetricTableRow::make()
                ->title($expenseValue . ' ' . __(config('nova.currency')) . ($expensesLeftValue ? ' (' . __('left') . ': ' . $expenseLeftValue . ' ' . __(config('nova.currency') . ')') : ''))
                ->subtitle(__('Expenses')),

            MetricTableRow::make()
                ->title(__('Balance') . ': ' . $profit . ' ' . __(config('nova.currency')))
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
