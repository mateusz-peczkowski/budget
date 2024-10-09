<?php

namespace App\Nova\Metrics;

use App\Models\DgSummary;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Metrics\MetricTableRow;
use Laravel\Nova\Metrics\Table;

class DgSummaryExpenses extends Table
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

        $sumZus = number_format($query->clone()->sum('zus'), 2, ',', ' ');
        $sumTax = number_format($query->clone()->sum('tax'), 2, ',', ' ');
        $sumVat = number_format($query->clone()->sum('vat'), 2, ',', ' ');

        return [
            MetricTableRow::make()
                ->title($sumZus . ' ' . __(config('nova.currency')))
                ->subtitle(__('ZUS')),

            MetricTableRow::make()
                ->title($sumTax . ' ' . __(config('nova.currency')))
                ->subtitle(__('Tax')),

            MetricTableRow::make()
                ->title($sumVat . ' ' . __(config('nova.currency')))
                ->subtitle(__('VAT')),
        ];
    }

    /**
     * Get the displayable name of the metric.
     *
     * @return string
     */
    public function name()
    {
        return __('DG Summary Expenses');
    }
}
