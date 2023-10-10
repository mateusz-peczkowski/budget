<?php

namespace App\Observers;

use App\Jobs\UpdateLoanData;
use App\Models\Loan;

class LoanObserver
{
    /**
     * Handle the Loan "creating" event.
     */
    public function creating(Loan $loan): void
    {
        $user = \App\Models\User::where('name', $loan->who)->first();

        if ($user)
            $loan->user_id = $user->id;
        else
            $loan->user_id = NULL;

        if ($loan->status === 'automatic')
            $loan->status = 'current';
    }

    /**
     * Handle the Loan "created" event.
     */
    public function created(Loan $loan): void
    {
        dispatch(new UpdateLoanData($loan));
    }

    /**
     * Handle the Loan "updating" event.
     */
    public function updating(Loan $loan): void
    {
        $user = \App\Models\User::where('name', $loan->who)->first();

        if ($user)
            $loan->user_id = $user->id;
        else
            $loan->user_id = NULL;

        if ($loan->status === 'automatic')
            $loan->status = 'current';
    }

    /**
     * Handle the Loan "updated" event.
     */
    public function updated(Loan $loan): void
    {
        dispatch(new UpdateLoanData($loan));
    }

    /**
     * Handle the Loan "deleted" event.
     */
    public function deleted(Loan $loan): void
    {
        //
    }

    /**
     * Handle the Loan "restored" event.
     */
    public function restored(Loan $loan): void
    {
        //
    }
}
