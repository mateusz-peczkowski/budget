<?php

namespace App\Nova\Metrics;

use Carbon\Carbon;
use Laravel\Nova\Menu\MenuItem;
use Laravel\Nova\Metrics\MetricTableRow;
use Laravel\Nova\Metrics\Table;

class IncomingExpenses extends Table
{
    /**
     * Calculate the value of the metric.
     *
     * @return mixed
     */
    public function calculate()
    {
        $toReturn = [];

        $expenses = \App\Models\Expense::where('status', 'pending')
            ->orderBy('date', 'asc')
            ->limit(10)
            ->get();

        foreach ($expenses as $expense) {
            $icon = 'check-circle';
            $iconClass = 'text-green-500';

            if ($expense->date < Carbon::now()->addDays(10)->endOfDay()) {
                $icon = 'question-mark-circle';
                $iconClass = 'text-yellow-500';
            }

            if ($expense->date < Carbon::now()->subDay()->endOfDay()) {
                $icon = 'exclamation-circle';
                $iconClass = 'text-red-500';
            }

            array_push(
                $toReturn,
                MetricTableRow::make()
                    ->icon($icon)
                    ->iconClass($iconClass)
                    ->title($expense->name . ' (' . $expense->date->format('d.m') . ')')
                    ->subtitle(number_format($expense->value, 2, ',', ' ') . ' ' . config('nova.currency') . ($expense->sub_name ? ' (' . $expense->sub_name . ')' : ''))
                    ->actions(function () use ($expense) {
                        return [
                            MenuItem::link('Details', '/resources/expenses/' . $expense->id),
                        ];
                    })
            );
        }

        return $toReturn;
    }

    /**
     * Get the displayable name of the metric.
     *
     * @return string
     */
    public function name()
    {
        return __('Incoming Expenses');
    }
}
