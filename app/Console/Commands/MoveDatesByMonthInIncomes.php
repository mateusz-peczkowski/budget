<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class MoveDatesByMonthInIncomes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:move-dates-by-month-in-incomes {repeatable_key} {num}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Move dates by month in incomes';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $incomes = \App\Models\Income::where('repeatable_key', $this->argument('repeatable_key'))
            ->orderBy('date')
            ->get();

        foreach($incomes as $num => $income) {
            $income->date = $income->date->addMonths($this->argument('num'));
            $income->pay_date = $income->pay_date ? $income->pay_date->addMonths($this->argument('num')) : null;
            $income->save();
        }
    }
}
