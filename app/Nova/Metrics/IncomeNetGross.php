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

class IncomeNetGross extends Table
{
    /**
     * Calculate the value of the metric.
     *
     * @param \Laravel\Nova\Http\Requests\NovaRequest $request
     *
     * @return mixed
     */
    public function calculate(NovaRequest $request)
    {
        $query = (new Income)->newQuery();

        $query->tap(function ($query) use ($request) {
            return $this->applyFilterQuery($request, $query);
        });

        $sumGross = $query->clone()->sum('gross');
        $sumNet = $query->clone()->sum('net');
        $sumTax = $query->clone()->sum('tax');

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

        $gross = number_format($sumGross, 2, ',', ' ');
        $net = number_format($sumNet, 2, ',', ' ');
        $income = number_format($sumNet - round($sumTax) - $sumZus, 2, ',', ' ');

        return [
            MetricTableRow::make()
                ->title($gross . ' ' . __(config('nova.currency')))
                ->subtitle(__('Gross')),

            MetricTableRow::make()
                ->title($net . ' ' . __(config('nova.currency')))
                ->subtitle(__('Net')),

            MetricTableRow::make()
                ->title($income . ' ' . __(config('nova.currency')))
                ->subtitle(__('Income')),
        ];
    }

    /**
     * Get the displayable name of the metric.
     *
     * @return string
     */
    public function name()
    {
        return __('Income Net/Gross');
    }
}
