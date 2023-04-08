<?php

namespace App\Nova\Metrics;

use App\Models\Income;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Metrics\MetricTableRow;
use Laravel\Nova\Metrics\Table;

class IncomeExpensesCalculations extends Table
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

        $sumTax = $query->clone()->sum('tax');
        $sumVat = $query->clone()->sum('vat');
        $sumZus = $query
            ->clone()
            ->select('incomes.period_id', 'income_types.zus as zus')
            ->leftJoin('income_types', 'incomes.income_type_id', '=', 'income_types.id')
            ->where('zus', '>', 0)
            ->groupBy('incomes.period_id', 'income_types.zus')
            ->get()
            ->sum('zus'); //TO-DO PECZIS: Change to use expenses

        $zus = number_format($sumZus, 2, ',', ' ');
        $tax = number_format($sumTax, 0, ',', ' ');
        $vat = number_format($sumVat, 0, ',', ' ');
        $sum = number_format($sumTax + $sumVat + $sumZus, 2, ',', ' ');

        return [
            MetricTableRow::make()
                ->title($zus . ' ' . config('nova.currency'))
                ->subtitle(__('ZUS')),

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
        return __('Income Expenses Calculations');
    }
}
