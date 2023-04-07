<?php

namespace App\Observers;

use App\Models\Income;
use Carbon\Carbon;

class IncomeObserver
{
    /**
     * Handle the Income "creating" event.
     */
    public function creating(Income $income): void
    {
        $incomePeriod = $this->getIncomePeriod($income->date);

        $income->status = $income->date->clone()->startOfDay()->isPast() ? 'paid' : 'pending';
        $income->period_id = $incomePeriod->id;

        $income->rate_local_currency = round($income->rate * $income->currency_rate, 2);

        $income->net = round($income->quantity * $income->rate_local_currency, 2);

        $income->vat = round($income->net * $income->vat_percent / 100, 2);

        $income->gross = round($income->net + $income->vat, 2);

        $income->tax = round($income->net * $income->tax_percent / 100, 2);

        $income->repeatable_key = $income->repeat ? $income->repeat : NULL;

        if ($income->repeatable_key === '2_weeks')
            $income->sub_name = $income->date->clone()->subDays(23)->format('d.m.Y') . ' - ' . $income->date->clone()->subDays(10)->format('d.m.Y');
        else
            $income->sub_name = __(Carbon::now()->setYear($incomePeriod->year)->setMonth($incomePeriod->month)->format('F')) . ' ' . Carbon::now()->setYear($incomePeriod->year)->setMonth($incomePeriod->month)->format('Y');

        unset($income->repeat);
    }

    /**
     * Handle the Income "created" event.
     */
    public function created(Income $income): void
    {
        if ($income->repeatable_key) {
            $type = $income->repeatable_key;

            $income->repeatable_key = $income->id;
            $income->save();

            $nextDate = $income->date->clone();

            if ($type == '2_weeks')
                $nextDate->addDays(14);
            else if ($type == 'last_of_the_month')
                $nextDate->firstOfMonth()->addMonth()->endOfMonth();
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

                $tempIncome = $income->replicate();
                $tempIncome->date = $nextDate;

                $tempIncome->status = $tempIncome->date->clone()->startOfDay()->isPast() ? 'paid' : 'pending';
                $tempIncome->period_id = $period->id;

                //Set sub name
                if ($type == '2_weeks')
                    $tempIncome->sub_name = $tempIncome->date->clone()->subDays(23)->format('d.m.Y') . ' - ' . $tempIncome->date->clone()->subDays(10)->format('d.m.Y');
                else
                    $tempIncome->sub_name = __(Carbon::now()->setYear($period->year)->setMonth($period->month)->format('F')) . ' ' . Carbon::now()->setYear($period->year)->setMonth($period->month)->format('Y');

                //Save temp income
                $tempIncome->saveQuietly();

                //Get next date
                if ($type == '2_weeks')
                    $nextDate->addDays(14);
                else if ($type == 'last_of_the_month')
                    $nextDate->firstOfMonth()->addMonth()->endOfMonth();
                else if ($type == 'each_month')
                    $nextDate->addMonth();

                $isNextPeriod = $nextDate->day > env('DAY_OF_THE_BUDGET_MONTH');

                $periodYear = $isNextPeriod ? $nextDate->clone()->startOfMonth()->addMonth()->year : $nextDate->year;
                $periodMonth = $isNextPeriod ? $nextDate->clone()->startOfMonth()->addMonth()->month : $nextDate->month;
            } while ($repeat === true);
        }
    }

    /**
     * Handle the Income "updating" event.
     */
    public function updating(Income $income): void
    {
        if ($income->isDirty(['name', 'income_type_id', 'rate', 'currency', 'currency_rate', 'tax_percent', 'vat_percent', 'quantity'])) {
            $income->rate_local_currency = round($income->rate * $income->currency_rate, 2);
            $income->net = round($income->quantity * $income->rate_local_currency, 2);
            $income->vat = round($income->net * $income->vat_percent / 100, 2);
            $income->gross = round($income->net + $income->vat, 2);
            $income->tax = round($income->net * $income->tax_percent / 100, 2);

            if ($income->update_future_incomes && $income->repeatable_key)
                \App\Models\Income::where('id', '>', $income->id)
                    ->where('repeatable_key', $income->repeatable_key)
                    ->where('status', 'pending')
                    ->update([
                        'name'                => $income->name,
                        'income_type_id'      => $income->income_type_id,
                        'rate'                => $income->rate,
                        'currency'            => $income->currency,
                        'currency_rate'       => $income->currency_rate,
                        'tax_percent'         => $income->tax_percent,
                        'vat_percent'         => $income->vat_percent,
                        'quantity'            => $income->quantity,
                        'rate_local_currency' => $income->rate_local_currency,
                        'net'                 => $income->net,
                        'vat'                 => $income->vat,
                        'gross'               => $income->gross,
                        'tax'                 => $income->tax,
                    ]);
        }

        unset($income->update_future_incomes);
    }

    /**
     * Handle the Income "updated" event.
     */
    public function updated(Income $income): void
    {

    }

    /**
     * Handle the Income "deleted" event.
     */
    public function deleted(Income $income): void
    {
        //
    }

    /**
     * Handle the Income "restored" event.
     */
    public function restored(Income $income): void
    {
        //
    }

    /**
     * Handle the Income "force deleted" event.
     */
    public function forceDeleted(Income $income): void
    {
        //
    }

    //

    private function getIncomePeriod($date)
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
