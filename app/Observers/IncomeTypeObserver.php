<?php

namespace App\Observers;

use App\Jobs\RecalculateZusForPeriods;
use App\Models\IncomeType;

class IncomeTypeObserver
{
    /**
     * Handle the IncomeType "created" event.
     */
    public function created(IncomeType $incomeType): void
    {
        dispatch(new RecalculateZusForPeriods);
    }

    /**
     * Handle the IncomeType "updated" event.
     */
    public function updated(IncomeType $incomeType): void
    {
        dispatch(new RecalculateZusForPeriods);
    }

    /**
     * Handle the IncomeType "deleted" event.
     */
    public function deleted(IncomeType $incomeType): void
    {
        dispatch(new RecalculateZusForPeriods);
    }

    /**
     * Handle the IncomeType "restored" event.
     */
    public function restored(IncomeType $incomeType): void
    {
        dispatch(new RecalculateZusForPeriods);
    }

    /**
     * Handle the IncomeType "force deleted" event.
     */
    public function forceDeleted(IncomeType $incomeType): void
    {
        dispatch(new RecalculateZusForPeriods);
    }
}
