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

class IncomeExpensesCalculations extends Table
{
    /**
     * Calculate the value of the metric.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return mixed
     */
    public function calculate(NovaRequest $request)
    {
        $query = (new Income)->newQuery();

        $query->tap(function ($query) use ($request) {
            return $this->applyFilterQuery($request, $query);
        });

        $sumTax = $query->clone()->sum('tax');
        $sumVat = $query->clone()->sum('vat');

        $expenseQuery = (new Expense)->newQuery();

        if ($this->filters instanceof Collection) {
            $fakeRequest = $request;

            foreach((new FilterDecoder($fakeRequest->filter, $this->filters))
                        ->filters() as $filter) {
                if ($filter->filter instanceof PeriodFilter)
                    $expenseQuery = (new $filter->filter->class)->apply($fakeRequest, $expenseQuery, $filter->value);
            }
        }

        $sumZus = $expenseQuery
            ->where('repeatable_key', 'zus')
            ->sum('value');

        $zus = number_format($sumZus, 2, ',', ' ');
        $tax = number_format($sumTax, 0, ',', ' ');
        $vat = number_format($sumVat, 0, ',', ' ');
        $sum = number_format($sumTax + $sumVat + $sumZus, 2, ',', ' ');

        return [
            MetricTableRow::make()
                ->title($zus . ' ' . __(config('nova.currency')))
                ->subtitle(__('ZUS')),

            MetricTableRow::make()
                ->title($tax . ' ' . __(config('nova.currency')))
                ->subtitle(__('Tax')),

            MetricTableRow::make()
                ->title($vat . ' ' . __(config('nova.currency')))
                ->subtitle(__('VAT')),

            MetricTableRow::make()
                ->title(__('Sum') . ': ' . $sum . ' ' . __(config('nova.currency')))
        ];
    }

    /**
     * Get the displayable name of the metric.
     *
     * @return string
     */
    public function name()
    {
        return __('Income Expenses Calculations');
    }
}
