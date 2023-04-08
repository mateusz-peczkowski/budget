<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MainExpensesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $expenses = [
            'zus' => __('ZUS'),
            'tax' => __('Tax'),
            'vat' => __('VAT'),
        ];

        foreach (DB::table('periods')->get() as $num => $period) {
            if (!$num) {
                foreach ($expenses as $key => $name) {
                    if (!DB::table('expenses')
                            ->where('expense_type_id', 2)
                            ->where('period_id', $period->id)
                            ->where('name', $name)->count() > 0) {

                        $startMonthDate = Carbon::now()->startOfMonth()->setYear($period->year)->setMonth($period->month);

                        DB::table('expenses')->insert([
                            'name'            => $name,
                            'sub_name'        => __($startMonthDate->format('F')) . ' ' . $startMonthDate->format('Y'),
                            'repeatable_key'  => $key,
                            'date'            => $this->getDateByType($startMonthDate, $key),
                            'status'          => $startMonthDate->clone()->startOfDay()->isPast() ? 'paid' : 'pending',
                            'expense_type_id' => 2,
                            'period_id'       => $period->id,
                            'value'           => $this->getValueByType($period, $key),
                            'created_at'      => Carbon::now(),
                            'updated_at'      => Carbon::now(),
                        ]);
                    }
                }
            }

            foreach ($expenses as $key => $name) {
                $nextPeriod = DB::table('periods')->where('id', '>', $period->id)->first();

                if (!$nextPeriod)
                    continue;

                if (DB::table('expenses')
                        ->where('expense_type_id', 2)
                        ->where('period_id', $nextPeriod->id)
                        ->where('name', $name)->count() > 0) {
                    continue;
                }

                $startMonthDate = Carbon::now()->startOfMonth()->setYear($nextPeriod->year)->setMonth($nextPeriod->month);

                $expenseDate = $this->getDateByType($startMonthDate, $key);

                DB::table('expenses')->insert([
                    'name'            => $name,
                    'sub_name'        => __($startMonthDate->format('F')) . ' ' . $startMonthDate->format('Y'),
                    'repeatable_key'  => $key,
                    'date'            => $expenseDate,
                    'status'          => $expenseDate->clone()->startOfDay()->isPast() ? 'paid' : 'pending',
                    'expense_type_id' => 2,
                    'period_id'       => $nextPeriod->id,
                    'value'           => $this->getValueByType($period, $key),
                    'created_at'      => Carbon::now(),
                    'updated_at'      => Carbon::now(),
                ]);
            }
        }
    }

    //

    private function getDateByType($date, $type)
    {
        switch ($type) {
            case 'zus':
                return $date->clone()->setDay(20);
            case 'tax':
                return $date->clone()->setDay(20);
            case 'vat':
                return $date->clone()->setDay(25);
        }
    }

    private function getValueByType($period, $type)
    {
        switch ($type) {
            case 'zus':
                return DB::table('incomes')
                    ->select('incomes.period_id', 'income_types.zus as zus')
                    ->leftJoin('income_types', 'incomes.income_type_id', '=', 'income_types.id')
                    ->where('zus', '>', 0)
                    ->where('period_id', $period->id)
                    ->groupBy('incomes.period_id', 'income_types.zus')
                    ->get()
                    ->sum('zus');
            case 'tax':
                return round(DB::table('incomes')
                    ->where('period_id', $period->id)
                    ->sum('tax'));
            case 'vat':
                return round(DB::table('incomes')
                    ->where('period_id', $period->id)
                    ->sum('vat'));
        }
    }
}
