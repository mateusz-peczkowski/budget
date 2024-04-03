<?php

namespace App\Nova\Metrics;

use App\Models\Expense;
use App\Models\TaxSettlement;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Laravel\Nova\FilterDecoder;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Metrics\MetricTableRow;
use Laravel\Nova\Metrics\Table;
use Peczis\PeriodFilter\PeriodFilter;

class ToPayVsExcessCalculations extends Table
{
    /**
     * Calculate the value of the metric.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return mixed
     */
    public function calculate(NovaRequest $request)
    {
        $model = TaxSettlement::class;

        $query = $model instanceof Builder ? $model : (new $model)->newQuery();

        $query->tap(function ($query) use ($request) {
            return $this->applyFilterQuery($request, $query);
        });

        $toPay = $query->sum('to_pay');
        $excess = $query->sum('excess');

        $toPayValue = number_format($toPay, 2, ',', ' ');
        $excessValue = number_format($excess, 2, ',', ' ');
        $profit = number_format($excess - $toPay, 2, ',', ' ');

        return [
            MetricTableRow::make()
                ->title($toPayValue . ' ' . __(config('nova.currency')))
                ->subtitle(__('To Pay')),

            MetricTableRow::make()
                ->title($excessValue . ' ' . __(config('nova.currency')))
                ->subtitle(__('Excess payment')),

            MetricTableRow::make()
                ->title(__('Balance') . ': ' . $profit . ' ' . __(config('nova.currency')))
        ];
    }

    /**
     * Get the displayable name of the metric.
     *
     * @return string
     */
    public function name()
    {
        return __('To Pay Vs Excess Calculations');
    }
}
