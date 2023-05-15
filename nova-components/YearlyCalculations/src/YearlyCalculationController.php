<?php

namespace Peczis\YearlyCalculations;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\App;

class YearlyCalculationController extends Controller
{
    public function config()
    {
        $first = \App\Models\Period::first();
        $last = \App\Models\Period::orderBy('id', 'desc')->first();
        $start = \Carbon\Carbon::now()->startOfYear()->setMonth(current_period()->month)->setYear(current_period()->year);

        return response()->json([
            'min_date'   => \Carbon\Carbon::now()->startOfYear()->setYear($first->year)->endOfDay(),
            'max_date'   => \Carbon\Carbon::now()->startOfYear()->setYear($last->year)->endOfYear(),
            'start_date' => $start->year,
            'currency'   => config('nova.currency'),
            'locale'     => config('app.locale'),
        ]);
    }

    public function data(Request $request)
    {
        $year = $request->get('year');
        $periods = \App\Models\Period::where('year', $year)->get();

        $expenses = [];

        foreach ($periods as $period) {
            $nameOfMonth = __(Carbon::now()->setMonth($period->month)->format('F'));

            $tempExpenses = \App\Models\Expense::where('period_id', $period->id)->get();

            $gross = \App\Models\Income::where('period_id', $period->id)->sum('gross');

            $toReturn = [
                'name'             => $nameOfMonth,
                'incomes'          => $gross,
                'balance'          => $gross - $tempExpenses->sum('value'),
                'expenses_by_type' => [],
            ];

            foreach (\App\Models\ExpenseType::all() as $expenseType)
                $toReturn['expenses_by_type'][$expenseType->id] = $tempExpenses->where('expense_type_id', $expenseType->id)->sum('value');

            $expenses[] = $toReturn;
        }

        $incomes = [];

        foreach (\App\Models\IncomeType::withTrashed()->whereNull('deleted_at')->orWhere('deleted_at', '>', Carbon::now()->setYear($year)->startOfYear())->get() as $incomeType) {
            $toReturn = [
                'name' => $incomeType->name . ($incomeType->user ? ' - ' . $incomeType->user->name : ''),
                'data' => [],
            ];

            foreach ($periods as $period) {
                $nameOfMonth = __(Carbon::now()->setMonth($period->month)->format('F'));

                $toReturn['data'][] = [
                    'name'    => $nameOfMonth,
                    'incomes' => \App\Models\Income::where('income_type_id', $incomeType->id)->where('period_id', $period->id)->sum('gross'),
                ];
            }

            $incomes[] = $toReturn;
        }

        return response()->json([
            'incomes'       => $incomes,
            'expenses'      => $expenses,
            'expensesTypes' => \App\Models\ExpenseType::pluck('name', 'id'),
        ]);
    }
}
