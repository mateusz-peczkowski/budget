<?php

namespace App\Observers;

use App\Models\Expense;
use Carbon\Carbon;

class ExpenseObserver
{
    /**
     * Handle the Expense "creating" event.
     */
    public function creating(Expense $expense): void
    {
        $expensePeriod = $this->getExpensePeriod($expense->date->clone());

        $expense->status = $expense->date->clone()->endOfDay()->isPast() ? 'paid' : 'pending';
        $expense->period_id = $expensePeriod->id;

        if ($expense->status === 'paid')
            $expense->pay_date = $expense->date;

        $expense->repeatable_key = $expense->repeat ? $expense->repeat . ($expense->repeat_length ? '-' . $expense->repeat_length : '') : NULL;

        unset($expense->repeat);
        unset($expense->repeat_length);
    }

    /**
     * Handle the Expense "created" event.
     */
    public function created(Expense $expense): void
    {
        if ($expense->repeatable_key) {
            $type = explode('-', $expense->repeatable_key);
            $count = intval(isset($type[1]) && $type[1] ? $type[1] : null);
            $type = $type[0];

            $expense->repeatable_key = $expense->id;
            $subName = $expense->sub_name;
            $expense->sub_name = str_replace('[x]', '1', $subName);
            $expense->sub_name = str_replace('[sum]', $expense->value, $expense->sub_name);
            $expense->saveQuietly();

            $nextDate = $expense->date->clone();

            if ($type == 'each_year')
                $nextDate->addYearNoOverflow();
            else if ($type == 'each_3_months')
                $nextDate->addMonthsNoOverflow(3);
            else if ($type == 'each_month')
                $nextDate->addMonthNoOverflow();

            $isNextPeriod = $nextDate->day > env('DAY_OF_THE_BUDGET_MONTH');

            $startPeriodDate = $nextDate->clone()->subMonthNoOverflow();

            $periodYear = $isNextPeriod ? $startPeriodDate->clone()->addMonthNoOverflow()->year : $startPeriodDate->year;
            $periodMonth = $isNextPeriod ? $startPeriodDate->clone()->addMonthNoOverflow()->month : $startPeriodDate->month;

            $repeat = true;
            $counter = 2;

            do {
                $period = \App\Models\Period::where('year', $periodYear)
                    ->where('month', $periodMonth)
                    ->first();

                if (!$period || ($count && $count < $counter)) {
                    $repeat = false;
                    break;
                }

                $tempExpense = $expense->replicate();
                $tempExpense->date = $nextDate;

                $tempExpense->status = $tempExpense->date->clone()->startOfDay()->isPast() ? 'paid' : 'pending';
                $tempExpense->period_id = $period->id;
                $tempExpense->sub_name = str_replace('[x]', $counter, $subName);
                $tempExpense->sub_name = str_replace('[sum]', $tempExpense->value * $counter, $tempExpense->sub_name);

                if ($tempExpense->status === 'paid')
                    $tempExpense->pay_date = $nextDate;

                //Save temp expense
                $tempExpense->saveQuietly();

                $nextDate = $expense->date->clone();

                //Get next date
                if ($type == 'each_year')
                    $nextDate->addYearsNoOverflow($counter);
                else if ($type == 'each_3_months')
                    $nextDate->addMonthsNoOverflow(3 * $counter);
                else if ($type == 'each_month')
                    $nextDate->addMonthsNoOverflow($counter);

                $isNextPeriod = $nextDate->day > env('DAY_OF_THE_BUDGET_MONTH');

                $startPeriodDate = $nextDate->clone()->subMonthNoOverflow();

                $periodYear = $isNextPeriod ? $startPeriodDate->clone()->addMonthNoOverflow()->year : $startPeriodDate->year;
                $periodMonth = $isNextPeriod ? $startPeriodDate->clone()->addMonthNoOverflow()->month : $startPeriodDate->month;

                $counter++;
            } while (($count && $count < $counter) || $repeat === true);
        }
    }

    /**
     * Handle the Expense "updating" event.
     */
    public function updating(Expense $expense): void
    {
        $isTax = in_array($expense->repeatable_key, ['zus', 'tax', 'vat']);

        if ($expense->isDirty('date')) {
            $expense->period_id = $this->getExpensePeriod($expense->date->clone())->id;
        }

        if (!$isTax && $expense->repeatable_key && $expense->isDirty(['name', 'expense_type_id', 'value'])) {
            \App\Models\Expense::where('date', '>', $expense->date)
                ->where('repeatable_key', $expense->repeatable_key)
                ->update([
                    'name'            => $expense->name,
                    'expense_type_id' => $expense->expense_type_id,
                    'value'           => $expense->value,
                ]);
        }
    }

    /**
     * Handle the Expense "updated" event.
     */
    public function updated(Expense $expense): void
    {

    }

    /**
     * Handle the Expense "deleted" event.
     */
    public function deleted(Expense $expense): void
    {
        //
    }

    /**
     * Handle the Expense "restored" event.
     */
    public function restored(Expense $expense): void
    {
        //
    }

    /**
     * Handle the Expense "force deleted" event.
     */
    public function forceDeleted(Expense $expense): void
    {
        //
    }

    //

    private function getExpensePeriod($date)
    {
        $isNextPeriod = $date->day > env('DAY_OF_THE_BUDGET_MONTH');

        $startDate = $date->clone()->startOfMonth()->subMonth();

        $periodYear = $isNextPeriod ? $startDate->clone()->addMonthNoOverflow()->year : $startDate->year;
        $periodMonth = $isNextPeriod ? $startDate->clone()->addMonthNoOverflow()->month : $startDate->month;

        $period = \App\Models\Period::where('year', $periodYear)
            ->where('month', $periodMonth)
            ->first();

        if ($period)
            return $period;

        return \App\Models\Period::first();
    }
}
