<?php

namespace App\Nova\Metrics;

use App\Models\Loan;
use Illuminate\Support\Collection;
use Laravel\Nova\FilterDecoder;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Metrics\MetricTableRow;
use Laravel\Nova\Metrics\Table;
use Peczis\PeriodFilter\PeriodFilter;

class LoansData extends Table
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
        $query = (new Loan)->newQuery()->where('status', '!=', 'archive');

        $sumOverall = $query->clone()->sum('overall_value');
        $sumPaid = $query->clone()->sum('paid_value');
        $sumNextPay = $query->clone()->sum('next_payment_value');

        $overall = number_format($sumOverall, 2, ',', ' ');
        $paid = number_format($sumPaid, 2, ',', ' ');
        $left = $overall - $paid;
        $nextPay = number_format($sumNextPay, 2, ',', ' ');

        return [
            MetricTableRow::make()
                ->title($overall . ' ' . __(config('nova.currency')))
                ->subtitle(__('Overall Value')),

            MetricTableRow::make()
                ->title($paid . ' ' . __(config('nova.currency')))
                ->subtitle(__('Paid Value')),

            MetricTableRow::make()
                ->title($left . ' ' . __(config('nova.currency')))
                ->subtitle(__('Left Value')),

            MetricTableRow::make()
                ->title($nextPay . ' ' . __(config('nova.currency')))
                ->subtitle(__('Loan Payments Value')),
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
