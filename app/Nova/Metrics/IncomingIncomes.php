<?php

namespace App\Nova\Metrics;

use Carbon\Carbon;
use Laravel\Nova\Menu\MenuItem;
use Laravel\Nova\Metrics\MetricTableRow;
use Laravel\Nova\Metrics\Table;

class IncomingIncomes extends Table
{
    /**
     * Calculate the value of the metric.
     *
     * @return mixed
     */
    public function calculate()
    {
        $toReturn = [];

        $incomes = \App\Models\Income::orderBy('date', 'asc')
            ->where('date', '>', Carbon::now())
            ->where('status', 'pending')
            ->limit(10)
            ->get();

        foreach ($incomes as $income) {
            $icon = 'check-circle';
            $iconClass = 'text-gray-400 dark:text-gray-700';

            if ($income->date < Carbon::now()->subDay()->endOfDay()) {
                $icon = 'exclamation-circle';
                $iconClass = 'text-red-500';
            }

            array_push(
                $toReturn,
                MetricTableRow::make()
                    ->icon($icon)
                    ->iconClass($iconClass)
                    ->title($income->name . ' (' . $income->date->format('d.m') . ')')
                    ->subtitle(number_format($income->gross, 2, ',', ' ') . ' ' . config('nova.currency') . ($income->sub_name ? ' (' . $income->sub_name . ')' : ''))
                    ->actions(function () use ($income) {
                        return [
                            MenuItem::link('Details', '/resources/incomes/' . $income->id),
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
        return __('Incoming Incomes');
    }
}
