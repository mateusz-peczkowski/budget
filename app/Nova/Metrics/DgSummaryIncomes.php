<?php

namespace App\Nova\Metrics;

use App\Models\DgSummary;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Metrics\MetricTableRow;
use Laravel\Nova\Metrics\Table;

class DgSummaryIncomes extends Table
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
        $query = (new DgSummary)->newQuery();

        $query->tap(function ($query) use ($request) {
            return $this->applyFilterQuery($request, $query);
        });

        $sumGross = $query->clone()->sum('gross');
        $sumNet = $query->clone()->sum('net');

        $countMonths = $query->clone()->count();
        $sumGrossMinusExpenses = $sumGross - $query->clone()->sum('zus') - $query->clone()->sum('tax') - $query->clone()->sum('vat');

        $averagePerMonthGross = $countMonths ? $sumGrossMinusExpenses / $countMonths : 0;


        $gross = number_format($sumGross, 2, ',', ' ');
        $net = number_format($sumNet, 2, ',', ' ');
        $averagePerMonthGross = number_format($averagePerMonthGross, 2, ',', ' ');

        return [
            MetricTableRow::make()
                ->title($gross . ' ' . __(config('nova.currency')))
                ->subtitle(__('Gross')),

            MetricTableRow::make()
                ->title($net . ' ' . __(config('nova.currency')))
                ->subtitle(__('Net')),

            MetricTableRow::make()
                ->title($averagePerMonthGross . ' ' . __(config('nova.currency')))
                ->subtitle(__('Average per month income')),
        ];
    }

    /**
     * Get the displayable name of the metric.
     *
     * @return string
     */
    public function name()
    {
        return __('DG Summary Incomes');
    }
}
