<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FixDateInExpensesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (DB::table('expenses')->whereNotIn('repeatable_key', ['zus', 'tax', 'vat'])->get() as $expense) {
            $date = Carbon::parse($expense->date)->addMonthNoOverflow();
            DB::table('expenses')
                ->where('id', $expense->id)
                ->update([
                    'date' => $date,
                ]);
        }
    }
}
