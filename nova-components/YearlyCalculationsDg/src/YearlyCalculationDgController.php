<?php

namespace Peczis\YearlyCalculationsDg;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class YearlyCalculationDgController extends Controller
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

        $dgIncomeType = \App\Models\IncomeType::find(env('DG_ID'));

        $data = [];

        $taxFreeIncomes = [];
        $taxTotal = 0;

        foreach ($periods as $period) {
            $nameOfMonth = __(Carbon::now()->startOfMonth()->setMonth($period->month)->format('F'));

            //ZUS
            $plannedZus = $dgIncomeType->zus;
            $paidZus = \App\Models\Expense::where('period_id', $period->id)->where('repeatable_key', 'zus')->where('status', 'paid')->sum('value');

            if ($paidZus)
                $plannedZus = $paidZus;

            $paidPlannedZus = $plannedZus;

            //TAX
            $plannedTax = \App\Models\Income::where('period_id', $period->id)->where('income_type_id', $dgIncomeType->id)->sum('tax');

            $paidTaxItems = \App\Models\Expense::where('period_id', $period->id)->where('repeatable_key', 'tax')->where('status', 'paid')->pluck('value')->toArray();
            $paidTax = array_sum($paidTaxItems);

            $balanceTax = 0;

            if (count($paidTaxItems))
                $balanceTax = $plannedTax - $paidTax;

            $paidPlannedTax = !$paidTax && !count($paidTaxItems) ? $plannedTax : $paidTax;

            //VAT
            $plannedVat = \App\Models\Income::where('period_id', $period->id)->where('income_type_id', $dgIncomeType->id)->sum('vat');

            $paidVatItems = \App\Models\Expense::where('period_id', $period->id)->where('repeatable_key', 'vat')->where('status', 'paid')->pluck('value')->toArray();
            $paidVat = array_sum($paidVatItems);

            $balanceVat = 0;

            if (count($paidVatItems))
                $balanceVat = $plannedVat - $paidVat;

            $paidPlannedVat = !$paidVat && !count($paidVatItems) ? $plannedVat : $paidVat;

            //Gross
            $grossIncome = \App\Models\Income::where('period_id', $period->id)->where('income_type_id', $dgIncomeType->id)->sum('gross');

            //

            $data[] = [
                'name'         => $nameOfMonth,
                'is_completed' => $period->isClosedDg,
                'zus'          => [
                    'planned'      => $plannedZus,
                    'paid'         => $paidZus,
                    'paid_planned' => $paidPlannedZus,
                    'balance'      => 0,
                ],
                'tax'          => [
                    'planned'      => $plannedTax,
                    'paid'         => $paidTax,
                    'paid_planned' => $paidPlannedTax,
                    'balance'      => $balanceTax,
                ],
                'vat'          => [
                    'planned'      => $plannedVat,
                    'paid'         => $paidVat,
                    'paid_planned' => $paidPlannedVat,
                    'balance'      => $balanceVat,
                ],
                'total'        => [
                    'planned' => $plannedZus + $plannedTax + $plannedVat,
                    'paid'    => $paidZus + $paidTax + $paidVat,
                    'balance' => 0 + $balanceTax + $balanceVat,
                ],
                'gross_income' => $grossIncome,
                'net_income'   => $grossIncome - ($paidPlannedZus + $paidPlannedTax + $paidPlannedVat),
            ];

            $taxTotal += $paidZus + $paidTax + $paidVat;
            $taxFreeIncomes[$period->month] = $grossIncome;
        }

        $taxFreeMonth = '';

        foreach ($taxFreeIncomes as $month => $income) {
            $taxTotal -= $income;

            if ($taxTotal <= 0) {
                $taxFreeMonth = __(Carbon::now()->startOfMonth()->setMonth($month)->format('F'));
                break;
            }
        }

        return response()->json([
            'data'         => $data,
            'taxFreeMonth' => $taxFreeMonth,
        ]);
    }
}
