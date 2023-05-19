<?php

namespace Peczis\ClosestExpensesAndIncomes;

use Carbon\Carbon;
use Illuminate\Http\Request;
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
                $expense->resource_id = $expense->id;

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
                $income->resource_id = $income->id;

                return $income;
            });

        return response()->json([
            'expenses' => $expenses,
            'incomes'  => $incomes,
        ]);
    }

    public function changeStatusToPaid(Request $request)
    {
        $id = $request->get('id');
        $type = $request->get('type');

        if (!$id || !$type)
            return response()->json([], 404);

        $model = null;

        if ($type === 'expense')
            $model = \App\Models\Expense::find($id);

        if ($type === 'income')
            $model = \App\Models\Income::find($id);

        if (!$model)
            return response()->json([], 404);

        $model->status = 'paid';
        $model->pay_date = Carbon::now();
        $model->updateQuietly();

        return response()->json();
    }
}
