<?php

namespace App\Jobs;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RecalculateTaxesAndVatsForPeriods implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $recalculateTaxesPeriods;

    /**
     * Create a new job instance.
     */
    public function __construct($recalculateTaxesPeriods)
    {
        $this->recalculateTaxesPeriods = $recalculateTaxesPeriods;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        foreach($this->recalculateTaxesPeriods as $taxesPeriodId) {
            $period = \App\Models\Period::find($taxesPeriodId);
            $dataPeriod = \App\Models\Period::where('id', '<', $taxesPeriodId)->orderBy('id', 'desc')->first();

            if (!$period || !$dataPeriod)
                continue;

            $expenses = [
                'tax' => __('Tax'),
                'vat' => __('VAT'),
            ];

            foreach($expenses as $key => $name) {
                $modelExpense = \App\Models\Expense::where('period_id', $period->id)
                    ->where('name', $name)
                    ->where('expense_type_id', 2)
                    ->first();

                if ($modelExpense && ($modelExpense->repeatable_key !== $key || $modelExpense->status === 'paid'))
                    continue;

                if (!$modelExpense) {
                    $startMonthDate = Carbon::now()->startOfMonth()->setYear($dataPeriod->year)->setMonth($dataPeriod->month);
                    $startMonthDatePeriod = Carbon::now()->startOfMonth()->setYear($period->year)->setMonth($period->month);

                    $modelExpense = new \App\Models\Expense();
                    $modelExpense->name = $name;
                    $modelExpense->sub_name = __($startMonthDate->clone()->subMonth()->format('F')) . ' ' . $startMonthDate->clone()->subMonth()->format('Y');
                    $modelExpense->repeatable_key = $key;
                    $modelExpense->date = $startMonthDatePeriod->clone()->setDay(25);
                    $modelExpense->status = $startMonthDatePeriod->clone()->startOfDay()->isPast() ? 'paid' : 'pending';
                    $modelExpense->expense_type_id = 2;
                    $modelExpense->period_id = $period->id;
                }

                $modelExpense->value = round(\App\Models\Income::where('period_id', $dataPeriod->id)->sum($key));
                $modelExpense->saveQuietly();
            }
        }
    }
}
