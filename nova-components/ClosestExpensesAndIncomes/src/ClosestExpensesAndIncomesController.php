<?php

namespace Peczis\ClosestExpensesAndIncomes;

use Carbon\Carbon;
use Illuminate\Routing\Controller;

class ClosestExpensesAndIncomesController extends Controller
{
    public function config()
    {

        return response()->json([
            'currency' => config('nova.currency'),
            'locale'   => config('app.locale'),
        ]);
    }

    public function data()
    {
        $expenses = \App\Models\Expense::where('status', 'pending')
            ->orderBy('date', 'asc')
            ->limit(10)
            ->get()
            ->transform(function ($expense) {
                $icon = 'check-circle';
                $iconClass = 'text-gray-400 dark:text-gray-700';

                if ($expense->date < Carbon::now()->addDays(10)->endOfDay()) {
                    $icon = 'question-mark-circle';
                    $iconClass = 'text-yellow-500';
                }

                if ($expense->date < Carbon::now()->subDay()->endOfDay()) {
                    $icon = 'exclamation-circle';
                    $iconClass = 'text-red-500';
                }

                $expense->icon = $icon;
                $expense->icon_class = $iconClass;
                $expense->date_formated = $expense->date->format('d.m.Y');

                return $expense;
            });

        $incomes = \App\Models\Income::where('status', 'pending')
            ->orderBy('date', 'asc')
            ->limit(10)
            ->get()
            ->transform(function ($income) {
                $icon = 'check-circle';
                $iconClass = 'text-gray-400 dark:text-gray-700';

                if ($income->date < Carbon::now()->endOfDay()) {
                    $icon = 'question-mark-circle';
                    $iconClass = 'text-yellow-500';
                }

                if ($income->date < Carbon::now()->subDay()->endOfDay()) {
                    $icon = 'exclamation-circle';
                    $iconClass = 'text-red-500';
                }

                $income->icon = $icon;
                $income->icon_class = $iconClass;
                $income->date_formated = $income->date->format('d.m.Y');

                return $income;
            });

        return response()->json([
            'expenses' => $expenses,
            'incomes'  => $incomes,
        ]);
    }
}
