<?php

namespace App\Nova\Metrics;

use App\Models\Income;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Metrics\MetricTableRow;
use Laravel\Nova\Metrics\Table;

class IncomeCalculationsTaxes extends Table
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

        $tax = number_format($query->sum('tax'), 2, ',', ' ');
        $vat = number_format($query->sum('vat'), 2, ',', ' ');
        $sum = number_format($query->sum('tax') + $query->sum('vat'), 2, ',', ' ');

        return [
            MetricTableRow::make()
                ->title($tax . ' ' . config('nova.currency'))
                ->subtitle(__('Tax')),

            MetricTableRow::make()
                ->title($vat . ' ' . config('nova.currency'))
                ->subtitle(__('VAT')),

            MetricTableRow::make()
                ->title(__('Sum') . ': ' . $sum . ' ' . config('nova.currency'))
        ];
    }

    /**
     * Get the displayable name of the metric.
     *
     * @return string
     */
    public function name()
    {
        return __('Income Calculations Taxes');
    }
}
