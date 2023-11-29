<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class FixPeriodIdsInExpenses extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fix-period-ids-in-expenses {id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix period ids in expenses';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $mainExpense = \App\Models\Expense::find($this->argument('id'));

        foreach(\App\Models\Expense::where('repeatable_key', $mainExpense->repeatable_key)->where('id', '>', $mainExpense->id)->where('status', 'pending')->orderBy('date')->get() as $num => $expense) {
            $newPeriodId = $mainExpense->period_id + $num + 1;

            if (\App\Models\Period::find($newPeriodId)) {
                $expense->period_id = $newPeriodId;
                $expense->save();
            } else {
                $expense->delete();
            }
        }
    }
}
