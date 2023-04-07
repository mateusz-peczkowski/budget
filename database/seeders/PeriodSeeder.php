<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PeriodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $startDate = \Carbon\Carbon::now()->setYear(2022)->startOfYear();
        $endDate = \Carbon\Carbon::now()->setYear(2050)->endOfYear();

        foreach (\Carbon\CarbonPeriod::create($startDate, '1 month', $endDate) as $day) {
            DB::table('periods')->insert([
                'year'       => $day->clone()->format('Y'),
                'month'      => $day->clone()->format('m'),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }
    }
}
