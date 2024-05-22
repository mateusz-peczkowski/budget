<?php

namespace Peczis\YearlyCalculations;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class YearlyCalculationController extends Controller
{
    public function config()
    {
        $first = \App\Models\Period::first();
        $last = \App\Models\Period::orderBy('id', 'desc')->first();
        $start = \Carbon\Carbon::now()->startOfYear()->setMonth(current_period()->month)->setYear(current_period()->year);

        $incomesSimulate = [];

        foreach (\App\Models\Income::pluck('name', 'repeatable_key')->toArray() as $key => $name) {
            $incomesSimulate[] = [
                'label' => $name,
                'value' => $key,
            ];
        }

        return response()->json([
            'min_date'            => \Carbon\Carbon::now()->startOfYear()->setYear($first->year)->endOfDay(),
            'max_date'            => \Carbon\Carbon::now()->startOfYear()->setYear($last->year)->endOfYear(),
            'start_date'          => $start->year,
            'start_date_month'    => $start->month - 1,
            'start_date_simulate' => $start,
            'incomes_simulate'    => $incomesSimulate,
            'currency'            => config('nova.currency'),
            'locale'              => config('app.locale'),
        ]);
    }

    public function data(Request $request)
    {
        $year = $request->get('year');
        $periods = \App\Models\Period::where('year', $year)->get();

        $simulateDate = $request->get('simulate_date');
        $simulateIncomes = $request->get('simulate_incomes');
        $simulatePeriod = null;

        if ($simulateDate && $simulateIncomes && is_array($simulateIncomes)) {
            $simulateDate = json_decode($simulateDate);

            if (isset($simulateDate->year) && isset($simulateDate->month))
                $simulatePeriod = \App\Models\Period::where('year', $simulateDate->year)->where('month', $simulateDate->month + 1)->first();
        } else {
            $simulateIncomes = null;
        }

        $expenses = [];

        foreach ($periods as $period) {
            $nameOfMonth = __(Carbon::now()->startOfMonth()->setMonth($period->month)->format('F'));

            $tempExpenses = \App\Models\Expense::where('period_id', $period->id)->get();

            $incomesToSum = \App\Models\Income::where('period_id', $period->id)
                ->where(function ($q) use ($simulatePeriod, $simulateIncomes) {
                    if ($simulatePeriod && $simulateIncomes) {
                        $q
                            ->where('period_id', '<', $simulatePeriod->id)
                            ->orWhere('status', 'paid')
                            ->orWhere(function ($q2) use ($simulatePeriod, $simulateIncomes) {
                                $q2
                                    ->where('period_id', '>=', $simulatePeriod->id)
                                    ->whereNotIn('repeatable_key', $simulateIncomes);
                            });
                    }
                });

            $gross = $incomesToSum->sum('gross');

            $toReturn = [
                'name'             => $nameOfMonth,
                'incomes'          => $gross,
                'expenses_by_type' => [],
            ];

            foreach (\App\Models\ExpenseType::all() as $expenseType)
                if (!$period->isClosed && $expenseType->id === 2)
                    $toReturn['expenses_by_type'][$expenseType->id] = round($incomesToSum->sum('tax')) + round($incomesToSum->sum('vat')) + $tempExpenses->where('expense_type_id', $expenseType->id)->whereNotIn('repeatable_key', ['tax', 'vat'])->sum('value');
                else
                    $toReturn['expenses_by_type'][$expenseType->id] = $tempExpenses->where('expense_type_id', $expenseType->id)->sum('value');

            $toReturn['expenses'] = array_sum($toReturn['expenses_by_type']);
            $toReturn['balance'] = $gross - $toReturn['expenses'];

            $toReturn['is_completed'] = $period->isClosed;

            $expenses[] = $toReturn;
        }

        $incomes = [];

        foreach (\App\Models\IncomeType::withTrashed()->whereNull('deleted_at')->orWhere('deleted_at', '>', Carbon::now()->setYear($year)->startOfYear())->get() as $incomeType) {
            $toReturn = [
                'name' => $incomeType->name . ($incomeType->user ? ' - ' . $incomeType->user->name : ''),
                'data' => [],
            ];

            foreach ($periods as $period) {
                $nameOfMonth = __(Carbon::now()->startOfMonth()->setMonth($period->month)->format('F'));

                $toReturn['data'][] = [
                    'name'         => $nameOfMonth,
                    'incomes'      => \App\Models\Income::where('income_type_id', $incomeType->id)
                        ->where('period_id', $period->id)
                        ->where(function ($q) use ($simulatePeriod, $simulateIncomes) {
                            if ($simulatePeriod && $simulateIncomes) {
                                $q
                                    ->where('period_id', '<', $simulatePeriod->id)
                                    ->orWhere('status', 'paid')
                                    ->orWhere(function ($q2) use ($simulatePeriod, $simulateIncomes) {
                                        $q2
                                            ->where('period_id', '>=', $simulatePeriod->id)
                                            ->whereNotIn('repeatable_key', $simulateIncomes);
                                    });
                            }
                        })
                        ->sum('gross'),
                    'is_completed' => $period->isClosed,
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
