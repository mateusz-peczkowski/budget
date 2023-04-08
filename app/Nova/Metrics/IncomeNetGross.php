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

        $sumGross = $query->clone()->sum('gross');
        $sumNet = $query->clone()->sum('net');
        $sumTax = $query->clone()->sum('tax');
        $sumZus = $query
            ->clone()
            ->select('incomes.period_id', 'income_types.zus as zus')
            ->leftJoin('income_types', 'incomes.income_type_id', '=', 'income_types.id')
            ->where('zus', '>', 0)
            ->groupBy('incomes.period_id', 'income_types.zus')
            ->get()
            ->sum('zus'); //TO-DO PECZIS: Change to use expenses

        $gross = number_format($sumGross, 2, ',', ' ');
        $net = number_format($sumNet, 2, ',', ' ');
        $income = number_format($sumNet - round($sumTax) - $sumZus, 2, ',', ' ');

        return [
            MetricTableRow::make()
                ->title($gross . ' ' . config('nova.currency'))
                ->subtitle(__('Gross')),

            MetricTableRow::make()
                ->title($net . ' ' . config('nova.currency'))
                ->subtitle(__('Net')),

            MetricTableRow::make()
                ->title($income . ' ' . config('nova.currency'))
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
