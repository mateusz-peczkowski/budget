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

        $sumGross = number_format($query->clone()->sum('gross'), 2, ',', ' ');
        $sumNet = number_format($query->clone()->sum('net'), 2, ',', ' ');
        $averagePerMonthNet = number_format($query->clone()->count() ? $query->clone()->sum('net') / $query->clone()->count() : 0, 2, ',', ' ');

        return [
            MetricTableRow::make()
                ->title($sumGross . ' ' . __(config('nova.currency')))
                ->subtitle(__('Gross')),

            MetricTableRow::make()
                ->title($sumNet . ' ' . __(config('nova.currency')))
                ->subtitle(__('Net')),

            MetricTableRow::make()
                ->title($averagePerMonthNet . ' ' . __(config('nova.currency')))
                ->subtitle(__('Average per month net')),
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
