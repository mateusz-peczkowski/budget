<?php

namespace App\Nova\Metrics;

use App\Models\Loan;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Metrics\MetricTableRow;
use Laravel\Nova\Metrics\Table;

class LoansArchiveData extends Table
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
        $query = (new Loan)->newQuery()->where('status', 'archive');

        $sumOverall = $query->clone()->sum('overall_value');

        $overall = number_format($sumOverall, 2, ',', ' ');

        return [
            MetricTableRow::make()
                ->title($overall . ' ' . __(config('nova.currency')))
                ->subtitle(__('Overall Value')),
        ];
    }

    /**
     * Get the displayable name of the metric.
     *
     * @return string
     */
    public function name()
    {
        return __('Loans Calculations');
    }
}
