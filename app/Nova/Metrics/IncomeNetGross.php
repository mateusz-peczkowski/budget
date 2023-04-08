<?php

namespace App\Nova\Metrics;

use App\Models\Income;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Metrics\MetricTableRow;
use Laravel\Nova\Metrics\Table;

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

        $net = number_format($query->sum('net'), 2, ',', ' ');
        $gross = number_format($query->sum('gross'), 2, ',', ' ');

        return [
            MetricTableRow::make()
                ->title($net . ' ' . config('nova.currency'))
                ->subtitle(__('Net')),

            MetricTableRow::make()
                ->title($gross . ' ' . config('nova.currency'))
                ->subtitle(__('Gross')),
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
