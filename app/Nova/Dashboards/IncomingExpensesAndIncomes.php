<?php

namespace App\Nova\Dashboards;

use App\Nova\Metrics\IncomingExpenses;
use App\Nova\Metrics\IncomingIncomes;
use Laravel\Nova\Dashboard;
class IncomingExpensesAndIncomes extends Dashboard
{
    /**
     * Get the cards for the dashboard.
     *
     * @return array
     */
    public function cards()
    {
        return [
            (new IncomingExpenses)
                ->width('1/2'),

            (new IncomingIncomes)
                ->width('1/2'),
        ];
    }

    /**
     * Get the URI key for the dashboard.
     *
     * @return string
     */
    public function uriKey()
    {
        return 'incoming-expenses-and-incomes';
    }
}
