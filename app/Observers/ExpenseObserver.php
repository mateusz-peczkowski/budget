<?php

namespace App\Observers;

use App\Models\Expense;

class ExpenseObserver
{
    /**
     * Handle the Expense "creating" event.
     */
    public function creating(Expense $expense): void
    {
        $expensePeriod = $this->getExpensePeriod($expense->date);

        $expense->status = $expense->date->clone()->startOfDay()->isPast() ? 'paid' : 'pending';
        $expense->period_id = $expensePeriod->id;

        $expense->repeatable_key = $expense->repeat ? $expense->repeat : NULL;

        unset($expense->repeat);
    }

    /**
     * Handle the Expense "created" event.
     */
    public function created(Expense $expense): void
    {
        if ($expense->repeatable_key) {
            $type = $expense->repeatable_key;

            $expense->repeatable_key = $expense->id;
            $expense->saveQuietly();

            $nextDate = $expense->date->clone();

            if ($type == 'each_year')
                $nextDate->addYear();
            else if ($type == 'each_3_months')
                $nextDate->addMonths(3);
            else if ($type == 'each_month')
                $nextDate->addMonth();

            $isNextPeriod = $nextDate->day > env('DAY_OF_THE_BUDGET_MONTH');

            $periodYear = $isNextPeriod ? $nextDate->clone()->startOfMonth()->addMonth()->year : $nextDate->year;
            $periodMonth = $isNextPeriod ? $nextDate->clone()->startOfMonth()->addMonth()->month : $nextDate->month;

            $repeat = true;

            do {
                $period = \App\Models\Period::where('year', $periodYear)
                    ->where('month', $periodMonth)
                    ->first();

                if (!$period) {
                    $repeat = false;
                    break;
                }

                $tempExpense = $expense->replicate();
                $tempExpense->date = $nextDate;

                $tempExpense->status = $tempExpense->date->clone()->startOfDay()->isPast() ? 'paid' : 'pending';
                $tempExpense->period_id = $period->id;

                //Save temp expense
                $tempExpense->saveQuietly();

                //Get next date
                if ($type == 'each_year')
                    $nextDate->addYear();
                else if ($type == 'each_3_months')
                    $nextDate->addMonths(3);
                else if ($type == 'each_month')
                    $nextDate->addMonth();

                $isNextPeriod = $nextDate->day > env('DAY_OF_THE_BUDGET_MONTH');

                $periodYear = $isNextPeriod ? $nextDate->clone()->startOfMonth()->addMonth()->year : $nextDate->year;
                $periodMonth = $isNextPeriod ? $nextDate->clone()->startOfMonth()->addMonth()->month : $nextDate->month;
            } while ($repeat === true);
        }
    }

    /**
     * Handle the Expense "updating" event.
     */
    public function updating(Expense $expense): void
    {
        $isTax = in_array($expense->repeatable_key, ['zus', 'tax', 'vat']);

        if ($isTax && $expense->isDirty('value'))
            $expense->repeatable_key = NULL;

        if (!$isTax && $expense->repeatable_key && $expense->isDirty(['name', 'expense_type_id', 'value']) && $expense->status == 'pending') {
            \App\Models\Expense::where('id', '>', $expense->id)
                ->where('repeatable_key', $expense->repeatable_key)
                ->where('status', 'pending')
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

        $periodYear = $isNextPeriod ? $date->clone()->startOfMonth()->addMonth()->year : $date->year;
        $periodMonth = $isNextPeriod ? $date->clone()->startOfMonth()->addMonth()->month : $date->month;

        $period = \App\Models\Period::where('year', $periodYear)
            ->where('month', $periodMonth)
            ->first();

        if ($period)
            return $period;

        return \App\Models\Period::first();
    }
}
