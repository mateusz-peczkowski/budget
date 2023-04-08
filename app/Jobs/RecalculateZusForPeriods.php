<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RecalculateZusForPeriods implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $newValue = \App\Models\IncomeType::where('zus', '>', 0)->sum('zus');

        $modelExpenses = \App\Models\Expense::where('name', __('ZUS'))
            ->where('repeatable_key', 'zus')
            ->where('expense_type_id', 2)
            ->where('status', 'pending')
            ->get();

        foreach($modelExpenses as $modelExpense) {
            $modelExpense->value = $newValue;
            $modelExpense->saveQuietly();
        }
    }
}
