<?php

namespace App\Nova\Metrics;

use App\Models\Income;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Metrics\MetricTableRow;
use Laravel\Nova\Metrics\Table;

class IncomeCalculatedTaxes extends Table
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

        return [
            MetricTableRow::make()
                ->title(number_format($query->sum('tax'), 2, ',', '') . ' ' . config('nova.currency'))
                ->subtitle(__('Tax')),

            MetricTableRow::make()
                ->title(number_format($query->sum('vat'), 2, ',', '') . ' ' . config('nova.currency'))
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
        return __('Income Calculated Taxes');
    }
}
